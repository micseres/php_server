<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

namespace Micseres\PhpServer\Front;

use Micseres\PhpServer\Middleware\ClosureBuilder;
use Micseres\PhpServer\Request\RequestInterface;
use Micseres\PhpServer\Response\Response;

/**
 * Class RequestHandler
 * @package Micseres\PhpServer\Front
 */
class RequestHandler
{
    /** @var array RequestHandlerInterface[] */
    private $middlewares = [];

    /** @var \Closure */
    private $process;

    /** @var ClosureBuilder  */
    private $closureBuilder;

    public function __construct(ClosureBuilder $closureBuilder)
    {
        $this->closureBuilder = $closureBuilder;
    }

    /**
     * @param RequestInterface $request
     *
     * @return Response
     */
    public function handle(RequestInterface $request): Response
    {
        if ($this->process === null) {
            $this->process = $this->closureBuilder->build($this->middlewares);
        }
        $method = $this->process;

        return $method($request);
    }


    public function addMiddleware(RequestHandlerInterface $handler)
    {
        $this->middlewares[] = $handler;
    }
}
