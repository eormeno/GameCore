<?php

namespace App\Models\Prefab;

use App\Models\GameObject\GameObject;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

abstract class Base extends Model
{
    protected $keyType = 'string';  // PK is string
    public $incrementing = false;   // PK is not autoincrement
    protected $primaryKey = 'name'; // PK is 'name'
    public $timestamps = false;

    protected $fillable = ['name', 'type'];

    public static function structure(): array
    {
        return [];
    }

    // public function afterInstantiate(GameObject $gameObject, array $attributes = []): void
    // {
    // }

    public static function findPrefab(string $name): ?Prefab
    {
        $prefab = self::where('name', $name)->first();
        if (!$prefab) {
            throw new InvalidArgumentException("Prefab $name not found");
        }
        $type = $prefab->type;
        return new $type($prefab->toArray());
    }

	public static function castPrefab(Prefab $prefab): Prefab{
		$type = $prefab->type;
		return new $type($prefab->toArray());
	}
}
