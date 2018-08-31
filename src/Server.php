<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer;

/**
 * Class Server
 * @package Micseres\PhpServer
 */
class Server
{
    public function run() {
        echo getenv('APP_ENV');
        echo "\n";
    }
}
