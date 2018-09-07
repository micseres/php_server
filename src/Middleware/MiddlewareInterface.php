<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\Middleware;

use Micseres\PhpServer\Request\RequestInterface;

interface MiddlewareInterface
{
    /**
     * @param mixed                    $request
     * @param MiddlewareInterface|null $middleware
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function handle(RequestInterface $request, ?MiddlewareInterface $middleware);
}
