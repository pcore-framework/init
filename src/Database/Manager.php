<?php

declare(strict_types=1);

namespace PCore\Init\Database;

use Closure;
use Exception;
use PCore\Database\Contracts\ConnectorInterface;
use PCore\Database\Query;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

/**
 * Class Manager
 * @package PCore\Init\Database
 * @github https://github.com/pcore-framework/init
 */
class Manager
{

    /**
     * @var string
     */
    protected string $default = 'mysql';

    /**
     * @var array
     */
    protected array $connectors = [];

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var EventDispatcherInterface|null
     */
    protected static ?EventDispatcherInterface $eventDispatcher = null;

    /**
     * @param string $name
     */
    public function setDefault(string $name): void
    {
        $this->default = $name;
    }

    /**
     * @param string $name
     * @param ConnectorInterface $connector
     */
    public function addConnector(string $name, ConnectorInterface $connector): void
    {
        $this->connectors[$name] = $connector;
    }

    /**
     * @param string $name
     * @return Query
     */
    public function query(string $name = ''): Query
    {
        $name = $name ?: $this->default;
        if (!isset($this->connectors[$name])) {
            throw new RuntimeException('Нет связанного подключения к базе данных.');
        }
        return new Query($this->connectors[$name], static::$eventDispatcher);
    }

    /**
     * @throws Exception
     */
    public function extend(string $name, Closure $resolver): void
    {
        $connector = ($resolver)($this);
        if (!$connector instanceof ConnectorInterface) {
            throw new Exception('Преобразователь должен возвращать экземпляр ConnectorInterface.');
        }
        $this->addConnector($name, $connector);
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        static::$eventDispatcher = $eventDispatcher;
    }

    /**
     *
     */
    public function boot(): void
    {
        Model::setManager($this);
    }

}
