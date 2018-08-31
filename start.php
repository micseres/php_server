#!/usr/bin/env php
<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

use Micseres\PhpServer\Server;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/vendor/autoload.php';

if (!isset($_SERVER['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException('APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.');
    }
    (new Dotenv())->load(__DIR__.'/.env');
}

$server = new Server();
$server->run();
