<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer;

/**
 * Class Router
 * @package Micseres\PhpServer
 */
class Router
{
    private $routes = [];

    public function addMicroservice($routeName, $clientId)
    {
        if(!isset($this->routes[$routeName])) {
            $this->routes[$routeName] = [];
        }
        $this->routes[$routeName][] = $clientId;
    }

    public function removeMicroservice($clientId)
    {
        foreach ($this->routes as &$route) {
            if (in_array($clientId, $route)) {
                $key = array_search($clientId, $route);
                unset($route[$key]);
            }
        }
    }

    public function getMicroservice($route)
    {
        if (!array_key_exists($route, $this->routes) || empty($this->routes[$route])) {
            throw new \RuntimeException("Route not found");
        }
        return reset($this->routes[$route]);
    }

    public function all()
    {
        return array_keys($this->routes);
    }
}
