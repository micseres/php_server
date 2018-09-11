<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\System;

use Micseres\PhpServer\Router\Router;
use Micseres\PhpServer\Server;

/**
 * Class Controller
 * @package Micseres\PhpServer\System
 */
class Controller
{
    /** @var Router  */
    private $router;

    /**
     * Controller constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @uses routesAction
     * @uses pingAction
     * @uses cowAction
     * @uses warAction
     * @uses helpAction
     * @uses totalAction
     * @param string $action
     *
     * @return string
     */
    public function dispatch(string $action): string
    {
        $methodName = $action.'Action';
        if (!method_exists($this, $methodName)) {
            throw new \RuntimeException("'$action' command not found, try help for help");
        }

        return ($this->$methodName())."\n";
    }

    /**
     * @used-by dispatch
     */
    private function routesAction(): string
    {
        return json_encode($this->router->all());
    }

    /**
     * @used-by dispatch
     */
    private function pingAction(): string
    {
        return "pong";
    }

    /**
     * @used-by dispatch
     */
    private function cowAction(): string
    {
        return "Muuu";
    }

    /**
     * @used-by dispatch
     */
    private function warAction(): string
    {
        return "Don`t war, make love";
    }

    /**
     * @used-by dispatch
     */
    private function helpAction(): string
    {
        $result = "Available commands are: \n";
        $result .= "routes, ping, help";

        return $result;
    }

    /**
     * @used-by dispatch
     */
    private function totalAction(): string
    {
        $total = Server::$total;
        $duration = microtime(true) - Server::$start;

        $result = sprintf("processed %d in %f \n", $total, $duration);

        return $result;
    }
}
