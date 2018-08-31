<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\System;

use Micseres\PhpServer\Router;

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
     * @param string $action
     * @param array  $params
     *
     * @return string
     */
    public function dispatch(string $action, array $params = []): string
    {
        $methodName = $action.'Action';
        if (!method_exists($this,$methodName)) {
            throw new \RuntimeException("action $action not found");
        }

        return ($this->$methodName($params))."\n";
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
}
