<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\System;

/**
 * Class Controller
 * @package Micseres\PhpServer\System
 */
class Listener
{
    /**
     * @var \swoole_server
     */
    private $server;

    /** @var Controller */
    private $controller;

    public function __construct(\swoole_server $server, Controller $controller)
    {
        $this->server     = $server;
        $this->controller = $controller;
        $server->on('connect', [$this, 'onConnect']);
        $server->on('receive', [$this, 'onReceive']);
//        $server->on('task', [$this, 'onTask']);
//        $server->on('finish', [$this, 'onFinish']);
    }

    public function onReceive(\swoole_server $server, int $identifier, int $reactorId, string $data)
    {
        try {
            $data     = trim($data);
            $response = $this->controller->dispatch($data)."\n";
        } catch (\RuntimeException $exception) {
            $response = $exception->getMessage()."\n";
        }

        $this->server->send($identifier, $response);
    }

    public function onConnect(\swoole_server $server, int $identifier, int $reactorId)
    {
        $helloMessage = "Microservice resolver\n";
        $helloMessage .= "version 0.0.0.alpha\n";
        $server->send($identifier, $helloMessage);
    }
}
