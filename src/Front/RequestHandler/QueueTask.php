<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

namespace Micseres\PhpServer\Front\RequestHandler;

use Micseres\PhpServer\Exception\RouteNotExistsException;
use Micseres\PhpServer\Front\RequestHandlerInterface;
use Micseres\PhpServer\Request\RequestInterface;
use Micseres\PhpServer\Response\Response;
use Micseres\PhpServer\Response\TaskResponse;
use Micseres\PhpServer\Router\Router;
use Micseres\PhpServer\Task\Task;
use Swoole\Server;

/**
 * Class QueueTask
 * @package Micseres\PhpServer\Front\RequestHandler
 */
class QueueTask implements RequestHandlerInterface
{
    /** @var Router  */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param RequestInterface $request
     * @param \Closure         $next
     *
     * @return Response
     */
    public function handle($request, ?\Closure $next = null): Response
    {
        $action = $request->getAction();

        try {
            $route = $this->router->getRoute($action);
        } catch (RouteNotExistsException $exception) {
            $response = new Response($exception->getMessage(), Response::STATUS__FAIL);

            return $response;
        }

        $task     = new Task($request->getClientId(), $request->getParams());
        $connection = $route->getLeastLoadedConnection();
        $connection->addTask($task);
        if (!$connection->hasOpenTask()) {
            $connection->startNext();
        }

        $response = new TaskResponse($task, 'Request accepted');

        return $response;
    }
}
