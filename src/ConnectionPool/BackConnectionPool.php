<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\ConnectionPool;

use Micseres\PhpServer\Exception\ConnectionNotExistsException;

/**
 * Class BackConnectionPoolInterface
 * @package Micseres\PhpServer\ConnectionPool
 */
class BackConnectionPool implements ConnectionPoolInterface
{
    /** @var \swoole_server */
    private $server;

    /**
     * BackConnectionPool constructor.
     *
     * @param \swoole_server $server
     */
    public function __construct(\swoole_server $server)
    {
        $this->server = $server;
    }

    /**
     * @var BackConnectionInterface[]
     */
    private $connections = [];

    /**
     * @param ConnectionInterface $connection
     */
    public function addConnection(ConnectionInterface $connection)
    {
        if (! $connection instanceof BackConnectionInterface) {
            throw new \InvalidArgumentException();
        }

        $this->connections[$connection->getId()] = $connection;
    }

    /**
     * @param $key
     */
    public function removeConnection($key)
    {
        $connection = $this->connections[$key];
        unset($this->connections[$key]);
        unset($connection);
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasConnection($key): bool
    {
        return array_key_exists($key, $this->connections);
    }

    /**
     * @param $key
     *
     * @return BackConnectionInterface|ConnectionInterface
     */
    public function getConnection($key): ConnectionInterface
    {
        if (!$this->hasConnection($key)) {
            throw new ConnectionNotExistsException();
        }

        return $this->connections[$key];
    }
}
