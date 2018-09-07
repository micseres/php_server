<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\ConnectionPool;

/**
 * Class ConnectionInterface
 * @package Micseres\PhpServer\ConnectionPool
 */
interface ConnectionInterface
{
    /**
     * @return string
     */
    public function getId(): string ;
}
