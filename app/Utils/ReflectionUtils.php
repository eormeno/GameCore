<?php

namespace App\Utils;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use App\Models\GameService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Models\Components\ComponentBase;
use Illuminate\Database\Eloquent\Collection;

class ReflectionUtils
{

	public static function short($class): string
	{
		$reflection = new ReflectionClass($class);
		return $reflection->getShortName();
	}

	public static function getKebabClassName($gen_instance, string $suffix_to_remove = ""): string
	{
		if (!$gen_instance instanceof ReflectionClass) {
			$gen_instance = new ReflectionClass($gen_instance);
		}
		$short_class_name = $gen_instance->getShortName();
		if ($suffix_to_remove) {
			$short_class_name = substr($short_class_name, 0, -strlen($suffix_to_remove));
		}
		return CaseConverters::pascalToKebab($short_class_name);
	}

	// return true if the class implements an interface
	public static function implementsInterface($class, $interface): bool
	{
		$reflection = new ReflectionClass($class);
		return $reflection->implementsInterface($interface);
	}

	// return true if the class is a subclass of another class
	public static function isSubclassOf($class, $parent): mixed
	{
		$reflection = new ReflectionClass($class);
		if (!$reflection->isSubclassOf($parent)) {
			return null;
		}
		return $class;
	}

	public static function getMethods($class)
	{
		$reflection = new ReflectionClass($class);
		return $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
	}

	public static function getMethodNames($class)
	{
		$methods = self::getMethods($class);
		$methodNames = [];
		foreach ($methods as $method) {
			$methodNames[] = $method->name;
		}
		return $methodNames;
	}

	public static function getMethodNamesStartingWith($class, $prefix)
	{
		$methods = self::getMethods($class);
		$methodNames = [];
		foreach ($methods as $method) {
			if (strpos($method->name, $prefix) === 0) {
				$methodNames[] = $method->name;
			}
		}
		return $methodNames;
	}

	public static function getMethodParameters($class, $method)
	{
		$reflection = new ReflectionClass($class);
		$method = $reflection->getMethod($method);
		$parameters = $method->getParameters();
		$parameterNames = [];
		foreach ($parameters as $parameter) {
			$parameterNames[] = $parameter->name;
		}
		return $parameterNames;
	}

	public static function getMethodParametersValues($class, $method, $data)
	{
		$parameters = self::getMethodParameters($class, $method);
		$parametersValues = [];
		foreach ($parameters as $parameter) {
			$parametersValues[$parameter] = self::searchKeyRecursive($data, $parameter); //$data[$parameter] ?? null;
		}
		return $parametersValues;
	}

	private static function searchKeyRecursive($array, $key)
	{
		foreach ($array as $k => $v) {
			if ($k === $key) {
				return $v;
			}
			if (is_array($v)) {
				$result = self::searchKeyRecursive($v, $key);
				if ($result !== null) {
					return $result;
				}
			}
		}
		return null;
	}

	public static function invokeEventMethod(ComponentBase|GameService $instance, array $event)
	{
		$method = 'on' . CaseConverters::snakeToPascal($event['event']) . 'Event';
		if (!method_exists($instance, $method)) {
			return;
		}
		return self::invokeMethod($instance, $method, $event);
	}

	public static function invokeMethod($instance, $method, $data)
	{
		$reflection = new ReflectionClass($instance);
		$parametersValues = self::getMethodParametersValues($instance, $method, $data);
		return $reflection->getMethod($method)->invokeArgs($instance, $parametersValues);
	}

	public static function getModelAttributeNames(Model $object)
	{
		$attributeNames = [];
		// add the accessor attributes
		$accessors = $object->getMutatedAttributes();
		foreach ($accessors as $accessor) {
			$attributeNames[] = $accessor;
		}
		// add the appended attributes
		$appends = $object->getAppends();
		foreach ($appends as $append) {
			$attributeNames[] = $append;
		}
		// add the fillable attributes
		$fillable = $object->getFillable();
		foreach ($fillable as $fill) {
			$attributeNames[] = $fill;
		}
		return $attributeNames;
	}

	public static function getShortClassName($object)
	{
		$reflection = new ReflectionClass($object);
		return $reflection->getShortName();
	}

	/**
	 * Given a Model and an object, copy the values of the model's attributes attributes with the same name
	 * in the object.
	 */
	public static function copyModelAttributes(Model $model, $object)
	{
		// copy the attributes
		$attributeNames = self::getModelAttributeNames($model);
		foreach ($attributeNames as $attribute) {
			if (property_exists($object, $attribute)) {
				$object->$attribute = $model->$attribute;
			}
		}
		// copy the relations
		$relations = self::getModelRelations($model);
		foreach ($relations as $relation) {
			if (property_exists($object, $relation)) {
				$value = $model->$relation;
				if ($value instanceof Model) {
					$object->$relation = $value->getAlias();
				} else if ($value instanceof Collection) {
					$object->$relation = $value->map(function ($item) {
						return $item->getAlias();
					})->toArray();
				}
			}
		}
	}

	public static function getProtectedProperties($object)
	{
		// get all the protected variables
		$reflection = new ReflectionClass($object);
		$properties = $reflection->getProperties(ReflectionProperty::IS_PROTECTED);
		$protectedProperties = [];
		foreach ($properties as $property) {
			$protectedProperties[] = $property->name;
		}
		return $protectedProperties;
	}

	public static function getModelRelations(Model $model)
	{
		$relations = [];
		$reflection = new ReflectionClass($model);
		$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method) {
			if ($method->getNumberOfParameters() > 0) {
				continue; // No queremos métodos con parámetros.
			}
			$returnType = $method->getReturnType();
			if ($returnType === null) {
				continue; // No queremos métodos sin tipo de retorno.
			}
			// Si el tipo de retorno es una subclase de Illuminate\Database\Eloquent\Relations\Relation
			if (is_subclass_of($returnType->getName(), 'Illuminate\Database\Eloquent\Relations\Relation')) {
				$relations[] = $method->name;
			}
		}
		return $relations;
	}

	public static function retrieveEventMethods($class)
	{
		$methods = self::getMethods($class);
		$eventMethods = [];
		$eventNamePattern = '/^on.*Event$/';
		foreach ($methods as $method) {
			if (preg_match($eventNamePattern, $method->name)) {
				$eventMethods[] = Str::snake(Str::before(Str::after($method->name, 'on'), 'Event'));
			}
		}
		return $eventMethods;
	}

	public static function componentClassFromSlug(string $componentSlugName): string
	{
		$cacheKey = "component_class_{$componentSlugName}";
		if (Cache::has($cacheKey)) {
			return Cache::get($cacheKey);
		}

		$componentClass = self::resolveComponentClassFromSlug($componentSlugName);

		Cache::put($cacheKey, $componentClass, now()->addDay());

		return $componentClass;
	}

	protected static function resolveComponentClassFromSlug(string $componentSlugName): string
	{
		$onlyType = $componentSlugName;
		$path = 'Common\\'; // Default path for common components
		if (Str::contains($componentSlugName, '.')) {
			$onlyType = Str::afterLast($componentSlugName, '.');
			$path = self::dotsToPath(Str::beforeLast($componentSlugName, '.')) . '\\';
		}
		$studly = Str::studly($onlyType);
		$componentModel = "App\\GameApps\\{$path}Components\\{$studly}Component";
		return $componentModel;
	}

	private static function dotsToPath($dots)
	{
		return str_replace('.', '\\', $dots);
	}

	public static function findClassesInPath($directory, callable $callback, $inheritsFrom = null, $implements = null)
	{
		collect(File::allFiles(app_path($directory)))->each(
			function ($file) use ($callback, $inheritsFrom, $implements) {
				$class_name = Str::before($file->getPathname(), '.php');
				$class_name = 'App' . Str::after($class_name, app_path());
				$class_name = str_replace('/', '\\', $class_name);
				$class = new ReflectionClass($class_name);
				if (
					($inheritsFrom === null || $class->isSubclassOf($inheritsFrom)) &&
					($implements === null || $class->implementsInterface($implements))
				) {
					$callback($class);
				}
			}
		);
	}
}
