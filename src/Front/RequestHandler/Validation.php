<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

namespace Micseres\PhpServer\Front\RequestHandler;

use Micseres\PhpServer\Front\RequestHandlerInterface;
use Micseres\PhpServer\Response\Response;

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
        $action = $request->action ?? null;

        if (empty($action)) {
            return new Response("action is mandatory", Response::STATUS__FAIL);
        }

        return $next($request);
    }
}
