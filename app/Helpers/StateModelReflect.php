<?php

namespace App\Helpers;

use ReflectionClass;
use ReflectionMethod;
use App\FSM\IStateModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class StateModelReflect
{
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

    public static function treeOfChildren(IStateModel $model, array &$tree = [])
    {
        if (in_array($model->getAlias(), $tree)) {
            return;
        }
        $relations = self::getModelRelations($model);
        foreach ($relations as $relation) {
            $value = $model->$relation;
            if ($value instanceof IStateModel) {
                $tree[] = $value->getAlias();
                self::treeOfChildren($value, $tree);
            } else if ($value instanceof Collection) {
                $value->each(function ($item) use (&$tree) {
                    $tree[] = $item->getAlias();
                    self::treeOfChildren($item, $tree);
                });
            }
        }
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

    private static function getModelRelations(Model $model)
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
            // Si el tipo de retorno es una relación BelongsTo, lo ignoramos.
            if ($returnType->getName() === 'Illuminate\Database\Eloquent\Relations\BelongsTo') {
                continue;
            }
            // Si el tipo de retorno es una subclase de Illuminate\Database\Eloquent\Relations\Relation
            if (is_subclass_of($returnType->getName(), 'Illuminate\Database\Eloquent\Relations\Relation')) {
                $relations[] = $method->name;
            }
        }
        return $relations;
    }

}
