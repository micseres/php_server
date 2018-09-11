<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

namespace Micseres\PhpServer\Front\RequestHandler;

use Micseres\PhpServer\Front\RequestHandlerInterface;
use Micseres\PhpServer\Request\RequestInterface;
use Micseres\PhpServer\Response\Response;

/**
 * Class Authorization
 * @package Micseres\PhpServer\Front\RequestHandler
 */
class Authorization implements RequestHandlerInterface
{
    /**
     * @param RequestInterface $request
     * @param \Closure         $next
     *
     * @return Response
     */
    public function handle($request, \Closure $next): Response
    {
        return $next($request);
    }
}
