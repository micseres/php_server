<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Back;

use Micseres\PhpServer\ConnectionPool\BackConnection;
use Micseres\PhpServer\ConnectionPool\BackConnectionPool;
use Micseres\PhpServer\Response\ErrorResponse;
use Micseres\PhpServer\Router\Router;

/**
 * Class FrontController
 * @package Micseres\PhpServer\Back
 */
class Listener
{
    /** @var \swoole_server  */
    private $server;

    /** @var BackConnectionPool  */
    private $pool;

    /** @var \swoole_server_port */
    private $backListener;

    /** @var Router  */
    private $router;

    /** @var Controller  */
    private $controller;

    /**
     * Listener constructor.
     *
     * @param \swoole_server     $server
     * @param BackConnectionPool $pool
     * @param Router             $router
     * @param Controller         $controller
     *
     * @throws \Exception
     */
    public function __construct(
        \swoole_server $server,
        BackConnectionPool $pool,
        Router $router,
        Controller $controller
    ) {
        $this->controller = $controller;
        $this->server = $server;
        $this->pool = $pool;
        $this->router = $router;

        $this->init();
    }

    /**
     * @throws \Exception
     */
    private function init()
    {
        list($host, $port, $type) = $this->getConf();
        $this->backListener = $this->server->addListener($host, $port, $type);

        $this->backListener->on('connect', [$this, 'onConnect']);
        $this->backListener->on('receive', [$this, 'onReceive']);
        $this->backListener->on('close', [$this, 'onClose']);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getConf(): array
    {
        $type = getenv('SOCKET_BACK_TYPE');
        if (null === $type || 'unix' === $type) {
            $socket = getenv('SOCKET_BACK_FILE');
            if (null === $socket) {
                $socket = '/var/run/micseres/back.sock';
            }
            return  [$socket, 0, SWOOLE_UNIX_STREAM];
        }

        $host = getenv('SOCKET_BACK_HOST');
        if (null === $host) {
            throw new \Exception("SOCKET_SYSTEM_HOST should be defined on tcp socket type");
        }
        $port = getenv('SOCKET_BACK_PORT');
        if (null === $port) {
            throw new \Exception("SOCKET_SYSTEM_PORT should be defined on tcp socket type");
        }

        return [$host, $port, SWOOLE_TCP];
    }

    /**
     * @param \swoole_server $server
     * @param int            $connectionId
     * @param int            $fromId
     */
    public function onConnect(\swoole_server $server, int $connectionId, int $fromId)
    {
        $connection = new BackConnection($server, $connectionId);
        $this->pool->addConnection($connection);

        $helloMessage = "Hello\n";
        $this->server->send($connectionId, $helloMessage);
    }

    /**
     * @param \swoole_server $server
     * @param int            $connectionId
     * @param int            $reactorId
     */
    public function onClose(\swoole_server $server, int $connectionId, int $reactorId)
    {
        /** @var BackConnection $connection */
        $connection = $this->pool->getConnection($connectionId);

        //send fail message to current task
        if ($connection->hasOpenTask()) {
            $task = $connection->getCurrentTask();
            $response = new ErrorResponse($connection->getCurrentTask(), "microserver closed connection");
            $server->send($task->getClientId(), $response);
        }

        $route = $this->router->getRouteByConnection($connection);
        if (null !== $route) {
            $route->removeConnection($connection);

            //if we have no more microservers on this route, sent error to client and destroy route
            if ($route->isEmpty()) {
                $this->router->unsetRoute($route->getPath());
                foreach ($connection->getTasks() as $task) {
                    $response = new ErrorResponse(
                        $connection->getCurrentTask(),
                        "microserver closed connection"
                    );
                    $server->send($task->getClientId(), $response);
                }
                //else if we have microservices - put tasks in they
            } else {
                foreach ($connection->getTasks() as $task) {
                    $target = $route->getLeastLoadedConnection();
                    $target->addTask($task);
                }
            }
        }

        $this->pool->removeConnection($connectionId);
    }

    /**
     * @param \swoole_server $server
     * @param int            $connectionId
     * @param int            $reactor_id
     * @param string         $data
     */
    public function onReceive(\swoole_server $server, int $connectionId, int $reactor_id, string $data)
    {
        if (!$this->pool->hasConnection($connectionId)) {
            return;
        }
        /** @var BackConnection $connection */
        $connection = $this->pool->getConnection($connectionId);
        if ($connection->hasOpenTask()) {
            $task = $connection->getCurrentTask();
            $this->server->send($task->getClientId(), $data);
            $connection->startNext();
            return;
        }
        $response = $this->handleCommandMessage($connection, $data);
        $this->server->send($connectionId, $response);
    }

    /**
     * @param BackConnection $connection
     * @param string         $data
     *
     * @return      string
     */
    private function handleCommandMessage(BackConnection $connection, string $data)
    {
        $request = json_decode($data, true);
        if (null === $request) {
            $message = "Invalid JSON format message\n ";
            $message .= 'try { "action": "help"} to help'."\n";

            return $message;
        }
        $action = $request['action']??'';
        $params = $request['params']??[];

        if (empty($action)) {
            return "Action is mandatory\n";
        }

        try {
            $data = $this->controller->dispatch($connection, $action, $params);
        } catch (\RuntimeException $exception) {
            $data = $exception->getMessage();
        }

        return $data."\n";
    }
}
