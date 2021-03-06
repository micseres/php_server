<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Back;

use Micseres\MicroServiceDH\DiffieHellman;
use Micseres\PhpServer\ConnectionPool\BackConnection;
use Micseres\PhpServer\Exception\ConnectionAlreadyAddedException;
use Micseres\PhpServer\Router\Route;
use Micseres\PhpServer\Router\Router;

/**
 * Class Controller
 * @package Micseres\PhpServer\Back
 */
class Controller
{
    /** @var Router  */
    private $router;
    /**
     * @var DiffieHellman
     */
    private $dh;

    /**
     * Controller constructor.
     *
     * @param Router $router
     * @param DiffieHellman $dh
     */
    public function __construct(Router $router, DiffieHellman $dh)
    {
        $this->router = $router;
        $this->dh = $dh;
    }

    /**
     * @uses registerAction
     * @uses helpAction
     *
     * @param BackConnection $connection
     * @param string         $action
     * @param array          $params
     *
     * @return string
     */
    public function dispatch(BackConnection $connection, string $action, array $params): string
    {
        $methodName = $action.'Action';
        if (!method_exists($this, $methodName)) {
            throw new \RuntimeException("action $action not found");
        }

        return $this->$methodName($connection, $params);
    }

    /**
     * @used-by dispatch()
     * @param BackConnection $connection
     * @param array $params
     *
     * @return string
     * @throws \Micseres\MicroServiceDH\Exception\DiffieHellmanException
     */
    private function registerAction(BackConnection $connection, array $params): string
    {
        $routePath = $params['route'] ?? null;

        if (null === $routePath) {
            throw new \RuntimeException("route is required");
        }

        if (!$this->router->hasRoute($routePath)) {
            $route = new Route($routePath);
            $this->router->addRoute($route);
        }

        $route = $this->router->getRoute($routePath);

        try {
            $route->addConnection($connection);
        } catch (ConnectionAlreadyAddedException $exception) {
            return $exception->getMessage();
        }


        if (isset($params['public_key'])) {
            $this->dh->generatePrimaryAsSlave($params['p'], $params['g']);
            $connection->setSharedKey($this->dh->getSharedKey($params['public_key']));

            return json_encode([
                'status' => 'OK',
                'payload' => [
                    'public_key' =>  $this->dh->getPublicKey()
                ]
            ]);
        }

        return json_encode([
            'status' => 'OK',
            'payload' => []
        ]);
    }

    /**
     * @used-by dispatch()
     * @param BackConnection $connection
     * @param array          $params
     *
     * @return string
     */
    private function helpAction(BackConnection $connection, array $params): string
    {
        $message = "this ports accept only valid json formatted messages\n";
        $message .= "each message should contains mandatory \"action\" field with action \n";
        $message .= "and optional \"params\" field, what contains action parameters\n";
        $message .= "example { \"action\": \"register\", \"params\": {\"route\": \"name\"}}";

        return $message;
    }
}
