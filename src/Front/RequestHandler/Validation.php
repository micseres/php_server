<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

namespace Micseres\PhpServer\Front\RequestHandler;

use Micseres\PhpServer\Front\RequestHandlerInterface;
use Micseres\PhpServer\Response\Response;
use Micseres\PhpServer\Server;

/**
 * Class ValidationRequestHandler
 * @package Micseres\PhpServer\Front
 */
class Validation implements RequestHandlerInterface
{
    /**
     * @param mixed    $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $action = $request->route ?? null;

        if (empty($action)) {
            Server::getLogger()->warning('Action is mandatory', (array)$request);

            return new Response("Action is mandatory", Response::STATUS__FAIL);
        }

        return $next($request);
    }
}
