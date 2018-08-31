<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\System;

use Micseres\PhpServer\Router;

/**
 * Class Controller
 * @package Micseres\PhpServer\System
 */
class Listener
{
    /** @var Router  */
    private $router;

    /** @var Controller  */
    private $controller;

    /**
     * Listener constructor.
     *
     * @param Router     $router
     * @param Controller $controller
     */
    public function __construct(Router $router, Controller $controller)
    {
        $this->router = $router;
        $this->controller = $controller;
    }

    public function onReceive(\swoole_server $server, int $fd, int $reactorId, string $data)
    {
        $action = trim($data);
        try {
            $data = $this->controller->dispatch($action);
        } catch (\RuntimeException $exception) {
            $data = $exception->getMessage()."\n";
        }

        $server->send($fd, $data);
    }

    public function onConnect(\swoole_server $server, int $fd, int $reactorId)
    {
        $helloMessage = "Microservice resolver\n";
        $helloMessage .= "version 0.0.0.alpha\n";
        $server->send($fd, $helloMessage);
    }
}
