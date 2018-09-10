<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\Middleware;

interface MiddlewareInterface
{
    /**
     * @param mixed    $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, \Closure $next);
}
