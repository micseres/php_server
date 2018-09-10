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

/**
 * Class Server
 * @package Micseres\PhpServer
 */
class Server
{

    private $systemSocket = '/var/run/micseres/sys.sock';

    private $frontListener;
    private $backListener;

    /** @var System\Listener */
    private $systemListener;

    public function run()
    {

        //todo: resolve this params via dotenv
        $server = new \swoole_server($this->systemSocket, 0, SWOOLE_BASE, SWOOLE_UNIX_STREAM);
//        $server->set(['task_worker_num'=>1]);

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

        $server->start();
    }
}
