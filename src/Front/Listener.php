<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Front;

use Micseres\PhpServer\Exception\RouteNotExistsException;
use Micseres\PhpServer\Response\Response;
use Micseres\PhpServer\Response\TaskResponse;
use Micseres\PhpServer\Router\Router;
use Micseres\PhpServer\Task\Task;

/**
 * Class Controller
 * @package Micseres\PhpServer\Front
 */
class Listener
{
    private $socket = '/var/run/micseres/front.sock';

    /** @var Router  */
    private $router;

    /** @var \swoole_server_port  */
    private $port;

    /**
     * Listener constructor.
     *
     * @param \swoole_server $server
     * @param Router         $router
     */
    public function __construct(\swoole_server $server, Router $router)
    {
        $this->router = $router;
        $this->port = $server->addListener($this->socket, 0, SWOOLE_UNIX_STREAM);
        $this->port->on('connect', [$this, 'onConnect']);
        $this->port->on('receive', [$this, 'onReceive']);
    }

    public function onConnect(\swoole_server $server, int $clientId, int $reactorId)
    {
        $helloMessage = "Welcome to front socket\n";
        $server->send($clientId, $helloMessage);
    }

    public function onReceive(\swoole_server $server, int $clientId, int $reactorId, string $data)
    {
        $request = json_decode(trim($data), true);

        if (null === $request) {
            $response = new Response("Invalid JSON format", Response::STATUS__FAIL);
            $server->send($clientId, $response);
            return;
        }
        $params = $request['params']??[];
        $action = $request['action']??null;

        if (empty($action)) {
            $response = new Response("action is mandatory", Response::STATUS__FAIL);
            $server->send($clientId, $response);

            return;
        }

        try {
            $route = $this->router->getRoute($action);
        } catch (RouteNotExistsException $exception) {
            $server->send($clientId, $exception->getMessage()."\n");

            return;
        }

        $task = new Task($clientId, $params);
        $response = new TaskResponse($task, 'Request accepted');
        $server->send($clientId, $response);
        $connection = $route->getLeastLoadedConnection();
        $connection->addTask($task);
        if (!$connection->hasOpenTask()) {
            $connection->startNext();
        }
    }
}
