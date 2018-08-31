<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Back;

use Micseres\PhpServer\Router;

/**
 * Class FrontController
 * @package Micseres\PhpServer\Back
 */
class Listener
{
    /** @var Router  */
    private $router;

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

    public function onConnect(\swoole_server $server, int $fd, int $reactorId)
    {
        $message = "Welcome to the back socket, your id is {$fd}\n";
        $message .= "Please, send me message, what route you want to listen\n";
        $message .= "write {action: 'register', params:{route: 'route'}} to register new route\n";
        $server->send($fd, $message);
    }

    public function onReceive(\swoole_server $server, int $fd, int $reactorId, string $data)
    {
        $request = json_decode($data, true);
        var_dump($request);
        $request['params'] = $request['params']??[];

        if (empty($request['action'])) {
            $server->send($fd, "action is mandatory\n");

            return;
        }
        try {
            $data = $this->controller->dispatch($request['action'], $request['params'], $fd, $reactorId);
        } catch (\RuntimeException $exception) {
            $data = $exception->getMessage()."\n";
        }

        $server->send($fd, $data);
    }
}
