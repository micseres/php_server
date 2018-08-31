<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 31.08.18
 */

namespace Micseres\PhpServer\Back;

use Micseres\PhpServer\Router;

/**
 * Class Controller
 * @package Micseres\PhpServer\Back
 */
class Controller
{
    /** @var Router */
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
     * @uses registerAction
     *
     * @param string $action
     * @param array  $params
     * @param int    $fd
     * @param int    $reactorId
     *
     * @return string
     */
    public function dispatch(string $action, array $params, int $fd, int $reactorId): string
    {
        $methodName = $action.'Action';
        if (!method_exists($this, $methodName)) {
            throw new \RuntimeException("action $action not found");
        }

        return ($this->$methodName($params, $fd, $reactorId))."\n";
    }

    private function registerAction(array $params, int $fd, int $reactorId)
    {
        if (!isset($params['route'])) {
            throw new \RuntimeException("route is required");
        }
        $this->router->addMicroservice($params['route'], $fd);
    }
}
