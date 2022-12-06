<?php

declare(strict_types=1);

namespace PCore\Init\Database;

use PCore\Database\Builder as QueryBuilder;
use PCore\Init\Database\Exceptions\ModelNotFoundException;
use PCore\Utils\Collection;
use PDO;
use Throwable;

/**
 * Class Builder
 * @package PCore\Init\Database
 * @github https://github.com/pcore-framework/init
 */
class Builder extends QueryBuilder
{

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var string
     */
    protected string $class;

    /**
     * @param Model $model
     * @return $this
     */
    public function setModel(Model $model): static
    {
        $this->model = $model;
        $this->class = $model::class;
        $this->from = [$model->getTable(), ''];
        return $this;
    }

//    /**
//     * @param array $columns
//     * @return Collection
//     */
//    public function get(array $columns = ['*']): Collection
//    {
//        return Collection::make(
//            $this->query->statement($this->toSql($columns), $this->bindings)->fetchAll(PDO::FETCH_CLASS, $this->class)
//        );
//    }

    /**
     * @param array $columns
     * @return Model|null
     */
//    public function first(array $columns = ['*']): ?Model
//    {
//        try {
//            return $this->firstOrFail($columns);
//        } catch (Throwable) {
//            return null;
//        }
//    }

    /**
     * @param array $columns
     * @return Model
     * @throws ModelNotFoundException
     */
    public function firstOrFail(array $columns = ['*']): Model
    {
        return $this->query->statement(
            $this->limit(1)->toSql($columns),
            $this->bindings
        )->fetchObject($this->class)
            ?: throw new ModelNotFoundException('Данные не найдены.');
    }

//    /**
//     * @param $id
//     * @param array $columns
//     * @param string|null $identifier
//     * @return Model|null
//     */
//    public function find($id, array $columns = ['*'], ?string $identifier = null): ?Model
//    {
//        return $this->where($identifier ?? $this->model->getPrimaryKey(), $id)->first($columns);
//    }

    /**
     * @param $id
     * @param array $columns
     * @param string $identifier
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOrFail($id, array $columns = ['*'], string $identifier = 'id'): Model
    {
        return $this->where($this->model->getPrimaryKey(), $id)->firstOrFail($columns);
    }

}
