<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\ConnectionPool;

use Micseres\PhpServer\Task\TaskInterface;

/**
 * Class ConnectionPoolInterface
 * @package Micseres\PhpServer\ConnectionPool
 */
interface BackConnectionInterface
{
    /**
     * @param TaskInterface $task
     */
    public function addTask(TaskInterface $task);

    /**
     * @return int
     */
    public function getLoading(): int;

    /**
     *
     */
    public function startNext();

    /**
     * @return bool
     */
    public function hasOpenTask(): bool;

    public function getTasks(): array;
}
