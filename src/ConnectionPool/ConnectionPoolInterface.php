<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\ConnectionPool;

/**
 * Class ConnectionPoolInterface
 * @package Micseres\PhpServer\ConnectionPool
 */
interface ConnectionPoolInterface
{
    /**
     * @param ConnectionInterface $connection
     */
    public function addConnection(ConnectionInterface $connection);

    /**
     * @param $key
     */
    public function removeConnection($key);


    /**
     * @param $key
     *
     * @return bool
     */
    public function hasConnection($key): bool;

    /**
     * @param $key
     *
     * @return ConnectionInterface
     */
    public function getConnection($key): ConnectionInterface;
}
