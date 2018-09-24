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
     * @return bool
     */
    public function isWaitTaskData(): bool;

    /**
     * @param TaskInterface $task
     */
    public function startTask(TaskInterface $task);

    /**
     * @param string $data
     */
    public function finishTask(string $data);
}
