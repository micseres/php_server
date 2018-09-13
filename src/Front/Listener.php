<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Front;

use Micseres\PhpServer\Exception\InvalidRequestException;
use Micseres\PhpServer\Request\FrontRequest;
use Micseres\PhpServer\Response\ErrorResponse;
use Micseres\PhpServer\Response\Response;
use Micseres\PhpServer\Server;

/**
 * Class Controller
 * @package Micseres\PhpServer\Front
 */
class Listener
{
    /** @var \swoole_server_port */
    private $port;

    /** @var RequestHandler */
    private $requestHandler;

    /**
     * Listener constructor.
     *
     * @param \swoole_server $server
     * @param RequestHandler $requestHandler
     *
     * @throws \Exception
     */
    public function __construct(\swoole_server $server, RequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
        list($host, $port, $type) = $this->getConf();
        $this->port           = $server->addListener($host, $port, $type);
        $this->port->on('connect', [$this, 'onConnect']);
        $this->port->on('receive', [$this, 'onReceive']);
        $this->port->on('close', [$this, 'onClose']);

    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getConf(): array
    {
        $type = getenv('SOCKET_FRONT_TYPE');
        if (null === $type || 'unix' === $type) {
            $socket = getenv('SOCKET_FRONT_FILE');
            if (null === $socket) {
                $socket = '/var/run/micseres/front.sock';
            }
            return  [$socket, 0, SWOOLE_UNIX_STREAM];
        }

        $host = getenv('SOCKET_FRONT_HOST');
        if (null === $host) {
            throw new \Exception("SOCKET_FRONT_HOST should be defined on tcp socket type");
        }
        $port = getenv('SOCKET_FRONT_PORT');
        if (null === $port) {
            throw new \Exception("SOCKET_FRONT_PORT should be defined on tcp socket type");
        }

        return [$host, $port, SWOOLE_TCP];
    }


    public function onConnect(\swoole_server $server, int $clientId, int $reactorId)
    {
        Server::getLogger()->info('FRONT: connection open', $server->connection_info($clientId, $reactorId) ?? []);

        $helloMessage = "Welcome to front socket\n";
        $server->send($clientId, $helloMessage);
    }

    /**
     * @param \swoole_server $server
     * @param int $clientId
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive(\swoole_server $server, int $clientId, int $reactorId, string $data)
    {
        Server::getLogger()->info("FRONT: request {$data}", $server->connection_info($clientId, $reactorId) ?? []);

        try {
            $request = new FrontRequest($clientId, $data);
        } catch (InvalidRequestException $exception) {
            $response = new Response($exception->getMessage(), Response::STATUS__FAIL);
            $server->send($clientId, $response);

            return;
        }

        $response = $this->requestHandler->handle($request);

        $server->send($clientId, $response);

        Server::getLogger()->info("FRONT: response {$response}", $server->connection_info($clientId, $reactorId) ?? []);
    }

    /**
     * @param \swoole_server $server
     * @param int $clientId
     * @param int $reactorId
     */
    public function onClose(\swoole_server $server, int $clientId, int $reactorId)
    {
        Server::getLogger()->info('FRONT: connection close', $server->connection_info($clientId, $reactorId) ?? []);
    }
}
