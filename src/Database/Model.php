<?php

declare(strict_types=1);

namespace PCore\Init\Database;

use ArrayAccess;
use JsonSerializable;
use PCore\Init\Database\Exceptions\ModelNotFoundException;
use PCore\Utils\{Arr, Collection, Str};
use PCore\Utils\Contracts\Arrayable;
use RuntimeException;
use Throwable;
use function PCore\Utils\classBasename;

/**
 * Class Model
 * @package PCore\Init\Database
 * @github https://github.com/pcore-framework/init
 */
abstract class Model implements ArrayAccess, Arrayable, JsonSerializable
{

    /**
     * @var string
     */
    protected static string $table;

    /**
     * @var string
     */
    protected static string $connection = '';

    /**
     * @var string
     */
    protected static string $primaryKey = 'id';

    /**
     * @var array
     */
    protected static array $cast = [];

    /**
     * @var array
     */
    protected static array $fillable = [];

    /**
     * @var array
     */
    protected static array $hidden = [];

    /**
     * @var Manager
     */
    protected static Manager $manager;

    /**
     * @var array
     */
    protected array $original = [];

    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var array
     */
    protected array $appends = [];

    public function __construct(array $attributes = [])
    {
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
        $this->table ??= Str::camel(classBasename(static::class));
    }

    /**
     * @param $key
     * @return null|mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return static::query()->{$name}(...$arguments);
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function fill(array $attributes): static
    {
        $this->original = $attributes;
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * @param $id
     * @param array $columns
     * @return null|Model
     */
    public static function find($id, array $columns = ['*']): ?static
    {
        try {
            return static::findOrFail($id, $columns);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param $id
     * @param array $columns
     * @return Model
     * @throws ModelNotFoundException
     */
    public static function findOrFail($id, array $columns = ['*']): static
    {
        return static::query()->findOrFail($id, $columns);
    }

    /**
     * @param array $columns
     * @return null|Model
     */
    public static function first(array $columns = ['*']): ?static
    {
        try {
            return static::firstOrFail($columns);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array $columns
     * @return Model
     * @throws ModelNotFoundException
     */
    public static function firstOrFail(array $columns = ['*']): static
    {
        try {
            return static::query()->firstOrFail($columns) ?? throw new ModelNotFoundException('Данные не найдены.');
        } catch (Throwable $throwable) {
            throw new ModelNotFoundException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @param array|string[] $columns
     * @return Collection
     */
    public static function all(array $columns = ['*']): Collection
    {
        return static::query()->get($columns);
    }

    /**
     * @return int
     */
    public function save(): int
    {
        $attributes = [];
        foreach ($this->getAttributes() as $key => $value) {
            if ($this->hasCast($key)) {
                $value = $this->cast($value, $this->getCast($key), true);
            }
            $attributes[$key] = $value;
        }
        return static::query()->insert($attributes);
    }

    /**
     * @param array $attributes
     * @return static|null
     */
    public static function create(array $attributes): ?static
    {
        $lastInsertId = (new static($attributes))->save();
        return $lastInsertId ? static::find($lastInsertId) : null;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return static::$table;
    }

    /**
     * @return Builder
     */
    public static function query(): Builder
    {
        return (new static())->newQuery();
    }

    /**
     * @param Manager $manager
     */
    public static function setManager(Manager $manager)
    {
        static::$manager = $manager;
    }

    /**
     * @return Builder
     */
    public function newQuery(): Builder
    {
        try {
            $builder = new Builder(static::$manager->query(static::$connection));
            return $builder->from($this->getTable())->setModel($this);
        } catch (Throwable $throwable) {
            throw new RuntimeException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @return array
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getCast($key): mixed
    {
        return static::$cast[$key];
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value): void
    {
        $this->original[$key] = $value;
        if (in_array($key, $this->getFillable())) {
            if ($this->hasCast($key)) {
                $value = $this->cast($value, $this->getCast($key));
            }
            $this->attributes[$key] = $value;
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return Arr::except($this->getAttributes(), $this->getHidden());
    }

    /**
     * @return array
     */
    public function getHidden(): array
    {
        return static::$hidden;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasAttribute($key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAttribute($key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param mixed $attributes
     */
    public function setAttributes(mixed $attributes): void
    {
        if ($attributes instanceof Arrayable) {
            $attributes = $attributes->toArray();
        }
        if (!is_array($attributes)) {
            throw new \InvalidArgumentException('Невозможно присвоить сущности ни один атрибут массива.');
        }
        $this->attributes = $attributes;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->hasAttribute($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        if ($this->hasAttribute($offset)) {
            unset($this->original[$offset], $this->attributes[$offset]);
        }
    }

    /**
     * @return array
     */
    public function getFillable(): array
    {
        return static::$fillable;
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function fillableFromArray(array $attributes): array
    {
        $fillable = $this->getFillable();
        if (count($fillable) > 0) {
            return array_intersect_key($attributes, array_flip($fillable));
        }
        return $attributes;
    }

    /**
     * @param $key
     * @return bool
     */
    protected function hasCast($key): bool
    {
        return isset(static::$cast[$key]);
    }

    /**
     * @param $value
     * @param $cast
     * @return mixed
     */
    protected function cast($value, $cast, bool $isWrite = false): mixed
    {
        return match ($cast) {
            'boolean', 'bool' => (bool)$value,
            'integer', 'int' => (int)$value,
            'string' => (string)$value,
            'double' => (float)$value,
            'float' => (float)$value,
            'json' => $isWrite ? json_encode($value, JSON_UNESCAPED_UNICODE) : json_decode($value, true),
            'serialize' => $isWrite ? serialize($value) : unserialize($value),
            default => $value,
        };
    }
}
