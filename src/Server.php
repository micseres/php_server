<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer;

use Micseres\PhpServer\Front;
use Micseres\PhpServer\Back;
use Micseres\PhpServer\System;

/**
 * Class Server
 * @package Micseres\PhpServer
 */
class Server
{
    private $backSocket = '/var/run/micseres/back.sock';
    private $frontSocket = '/var/run/micseres/front.sock';
    private $systemSocket = '/var/run/micseres/sys.sock';

    private  $frontPool;
    private  $backPool;

    public function run() {

        //todo: resolve this params via dotenv
        $server = new \swoole_server($this->systemSocket, 0, SWOOLE_BASE, SWOOLE_UNIX_STREAM);
        $this->frontPool = $server->addListener($this->frontSocket, 0, SWOOLE_UNIX_STREAM);
        $this->backPool = $server->addListener($this->backSocket, 0, SWOOLE_UNIX_STREAM);

        $router = new Router();

        $systemController = new System\Controller($router);
        $systemListener = new System\Listener($router, $systemController);
        $server->on('connect', [$systemListener, 'onConnect']);
        $server->on('receive', [$systemListener, 'onReceive']);

        $backController = new Back\Controller($router);
        $backListener = new Back\Listener($router, $backController);
        $this->backPool->on('connect', [$backListener, 'onConnect']);
        $this->backPool->on('receive', [$backListener, 'onReceive']);

        $frontController = new Front\Controller();
        $frontListener = new Front\Listener($router, $frontController);
        $this->frontPool->on('connect', [$frontListener, 'onConnect']);
        $this->frontPool->on('receive', [$frontListener, 'onReceive']);

        $server->start();
    }
}
