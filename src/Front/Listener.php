<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Front;

use Micseres\PhpServer\Router;

/**
 * Class Controller
 * @package Micseres\PhpServer\Front
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
        $this->controller = $controller;
        $this->router = $router;
    }

    public function onConnect(\swoole_server $server, int $fd, int $reactorId)
    {
        $helloMessage = "Welcome to front socket\n";
        $server->send($fd, $helloMessage);
    }

    public function onReceive(\swoole_server $server, int $fd, int $reactorId, string $data)
    {
    }
}
