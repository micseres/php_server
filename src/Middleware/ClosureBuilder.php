<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

namespace Micseres\PhpServer\Middleware;

/**
 * Class ClosureBuilder
 * @package Micseres\PhpServer\Middleware
 */
class ClosureBuilder
{
    public function build(array $middlewares)
    {
        $stack = array_reverse($middlewares);

        $closure = array_reduce(
            $stack,
            function (?\Closure $next, MiddlewareInterface $current) {
                return function ($request) use ($current, $next) {
                    return $current->handle($request, $next);
                };
            },
            null
        );

        return $closure;
    }
}
