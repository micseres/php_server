<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer;

use Micseres\PhpServer\Back;
use Micseres\PhpServer\Front;
use Micseres\PhpServer\ConnectionPool\BackConnectionPool;
use Micseres\PhpServer\Middleware\ClosureBuilder;
use Micseres\PhpServer\Router\Router;
use Micseres\PhpServer\System;
use Monolog\Logger;

/**
 * Class Server
 * @package Micseres\PhpServer
 */
class Server
{
    public static $total = 0;
    public static $start = 0;
    private $frontListener;
    private $backListener;

    /** @var System\Listener */

    private $systemListener;
    /**
     * @var Logger
     */
    public static $logger;

    /**
     * @throws \Exception
     */
    public function run()
    {
        self::$start = microtime(true);

        $server = $this->buildServer();
        $server->set([
            'debug_mode' => 0,
            'buffer_output_size' => 32 * 1024 *1024,
            'open_eof_check' => true,
            'package_eof' => "\r\n",
        ]);
        $router = new Router();
        $systemController = new System\Controller($router);
        $this->systemListener = new System\Listener($server, $systemController);
        $backPool = new BackConnectionPool($server);
        $backController = new Back\Controller($router);
        $this->backListener = new Back\Listener($server, $backPool, $router, $backController);


        $requestHandler = new Front\RequestHandler(new ClosureBuilder());
        $requestHandler->addMiddleware(new Front\RequestHandler\Validation());
        $requestHandler->addMiddleware(new Front\RequestHandler\Authorization());
        $requestHandler->addMiddleware(new Front\RequestHandler\QueueTask($router));
        $this->frontListener = new Front\Listener($server, $requestHandler);

        self::getLogger()->info('SERVER IS STARTED');

        $server->start();
    }

    /**
     * @return \swoole_server
     * @throws \Exception
     */
    private function buildServer()
    {
        $type = getenv('SOCKET_SYSTEM_TYPE');

        if (null === $type || 'unix' === $type) {
            $socket = getenv('SOCKET_SYSTEM_FILE');

            if (null === $socket) {
                $socket = '/var/run/micseres/sys.sock';
            }

            return new \swoole_server($socket, 0, SWOOLE_PROCESS, SWOOLE_UNIX_STREAM);
        }

        $host = getenv('SOCKET_SYSTEM_HOST');

        if (null === $host) {
            throw new \Exception("SOCKET_SYSTEM_HOST should be defined on tcp socket type");
        }

        $port = getenv('SOCKET_SYSTEM_PORT');

        if (null === $port) {
            throw new \Exception("SOCKET_SYSTEM_PORT should be defined on tcp socket type");
        }

        return  new \swoole_server($host, $port, SWOOLE_BASE, SWOOLE_TCP);
    }

    /**
     * @param Logger $logger
     */
    public static function setLogger(Logger $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * @return Logger
     */
    public static function getLogger(): Logger
    {
        return self::$logger;
    }
}
