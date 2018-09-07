<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\Task;

/**
 * Class TaskInterface
 * @package Micseres\PhpServer\Task
 */
interface TaskInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    public function process();
}
