<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Back;

use Micseres\MicroServiceEncrypt\Exception\EncryptException;
use Micseres\MicroServiceEncrypt\OpenSSLEncrypt;
use Micseres\PhpServer\ConnectionPool\BackConnection;
use Micseres\PhpServer\ConnectionPool\BackConnectionPool;
use Micseres\PhpServer\Response\ErrorResponse;
use Micseres\PhpServer\Router\Router;
use Micseres\PhpServer\Server;

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
     * @param int $connectionId
     * @param int $fromId
     * @throws EncryptException
     */
    public function onConnect(\swoole_server $server, int $connectionId, int $fromId)
    {
        Server::getLogger()->info('BACK: connection open', $server->connection_info($connectionId, $fromId) ?? []);

        $connection = new BackConnection($server, $connectionId, new OpenSSLEncrypt(getenv('ENCRYPT_ALGO')));
        $this->pool->addConnection($connection);
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
        $currentTask = null;

        //send fail message to current task
        if ($connection->isWaitTaskData()) {
            $currentTask = $connection->getCurrentTask();
        }

        $route = $this->router->getRouteByConnection($connection);
        if (null !== $route) {
            $route->removeConnection($connection);
            if ($route->isEmpty()) {
                $this->router->unsetRoute($route->getPath());
                if (null !== $currentTask) {
                    $response = new ErrorResponse($connection->getCurrentTask(), "microserver closed connection");
                    $server->send($currentTask->getClientId(), $response);
                }
            } else {
                $route->queueTask($currentTask);
            }
        }

        $this->pool->removeConnection($connectionId);

        Server::getLogger()->info('BACK: connection close', $server->connection_info($connectionId, $reactorId) ?? []);
    }

    /**
     * @param \swoole_server $server
     * @param int $connectionId
     * @param int $reactorId
     * @param string $data
     * @throws EncryptException
     */
    public function onReceive(\swoole_server $server, int $connectionId, int $reactorId, string $data)
    {
        Server::getLogger()->info("BACK: request {$data}", $server->connection_info($connectionId, $reactorId) ?? []);

        if (!$this->pool->hasConnection($connectionId)) {
            return;
        }

        /** @var BackConnection $connection */
        $connection = $this->pool->getConnection($connectionId);

        $data = $connection->decodeData($data);

        if (null === $data) {
            $connection->rejectTask();
            $this->server->resume($connectionId);

            return;
        }

        if ($this->detectCommandMessage($data)) {
            $response = $this->handleCommandMessage($connection, $data);
            $this->server->send($connectionId, $response);

            Server::getLogger()->info(
                "BACK: command response {$response}",
                $server->connection_info($connectionId, $reactorId) ?? []
            );

            return;
        }

        if ($connection->isWaitTaskData()) {
            if (isset($data['payload']['apiKey']) && $data['payload']['apiKey'] === getenv('API_KEY')) {
                $connection->finishTask($data['payload']['data']);
                $this->router->getRouteByConnection($connection)->pushTheQueue();

                Server::$total++;

                return;
            }
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    private function detectCommandMessage(array $data): bool
    {
        $route = $data['route'] ?? '';

        if ($route === 'system') {
            return true;
        }

        return false;
    }

    /**
     * @param BackConnection $connection
     * @param array $data
     *
     * @return      string
     */
    private function handleCommandMessage(BackConnection $connection, array $data): string
    {
        $payload = $data['payload'] ?? [];

        if (!isset($payload['action'])) {
            return "Action is mandatory\n";
        }

        $action = $payload['action'];

        try {
            $data = $this->controller->dispatch($connection, $action, $payload);
        } catch (\RuntimeException $exception) {
            $data = $exception->getMessage();
        }

        return $data."\n";
    }
}
