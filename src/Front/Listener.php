<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Front;

use Micseres\PhpServer\Exception\InvalidRequestException;
use Micseres\PhpServer\Request\FrontRequest;
use Micseres\PhpServer\Response\Response;

/**
 * Class Controller
 * @package Micseres\PhpServer\Front
 */
class Listener
{
    private $socket = '/var/run/micseres/front.sock';

    /** @var \swoole_server_port */
    private $port;

    /** @var RequestHandler */
    private $requestHandler;

    /**
     * Listener constructor.
     *
     * @param \swoole_server $server
     * @param RequestHandler $requestHandler
     */
    public function __construct(\swoole_server $server, RequestHandler $requestHandler)
    {
        $this->requestHandler = $requestHandler;
        $this->port           = $server->addListener($this->socket, 0, SWOOLE_UNIX_STREAM);
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
        try {
            $request = new FrontRequest($clientId, $data);
        } catch (InvalidRequestException $exception) {
            $server->send($clientId, $exception->getMessage(), Response::STATUS__FAIL);

            return;
        }

        $response = $this->requestHandler->handle($request);

        $server->send($clientId, $response);
    }
}
