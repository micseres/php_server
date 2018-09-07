<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\Router;

use Micseres\PhpServer\ConnectionPool\ConnectionInterface;
use Micseres\PhpServer\Exception\RouteAlreadyExistsException;
use Micseres\PhpServer\Exception\RouteNotExistsException;

/**
 * Class Router
 * @package Micseres\PhpServer\Router
 */
class Router
{
    /** @var Route[] */
    private $routes = [];

    public function addRoute(Route $route)
    {
        $key = $route->getPath();
        if (array_key_exists($route->getPath(), $this->routes)) {
            throw new RouteAlreadyExistsException();
        };

        $this->routes[$key] = $route;
    }

    /**
     * @param string $path
     *
     * @return Route
     */
    public function getRoute(string $path)
    {
        if (!$this->hasRoute($path)) {
            throw new RouteNotExistsException("Route {$path} not registered");
        }
        return $this->routes[$path];
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function hasRoute(string $path):bool
    {
        return array_key_exists($path, $this->routes);
    }

    /**
     * @param string $path
     */
    public function unsetRoute(string $path)
    {
        if (array_key_exists($path, $this->routes)) {
            $route = $this->routes[$path];
            unset($this->routes[$path]);
            unset($route);
        }
    }

    /**
     * @param ConnectionInterface $connection
     *
     * @return Route|null
     */
    public function getRouteByConnection(ConnectionInterface $connection): ?Route
    {
        foreach ($this->routes as $route) {
            if ($route->hasConnection($connection->getId())) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @return Route[]
     */
    public function all()
    {
        return $this->routes;
    }
}
