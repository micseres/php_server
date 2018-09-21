<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\Router;

use Micseres\PhpServer\ConnectionPool\BackConnection;
use Micseres\PhpServer\ConnectionPool\BackConnectionInterface;
use Micseres\PhpServer\Exception\ConnectionAlreadyAddedException;
use Micseres\PhpServer\Exception\PoolNotExistsException;
use Micseres\PhpServer\Task\TaskInterface;

/**
 * Class Route
 * @package Micseres\PhpServer\Router
 */
class Route implements \JsonSerializable
{
    /** @var string */
    private $path;

    /** @var BackConnectionInterface[] */
    private $connections = [];

    /** @var TaskInterface[]  */
    private $taskQueue = [];

    /**
     * Route constructor.
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param BackConnection $connection
     */
    public function addConnection(BackConnection $connection)
    {
        $key = $connection->getId();
        if ($this->hasConnection($key)) {
            throw new ConnectionAlreadyAddedException("connection already added to this route");
        }

        $this->connections[$key] = $connection;
    }

    /**
     * @param BackConnection $connection
     */
    public function removeConnection(BackConnection $connection)
    {
        unset($this->connections[$connection->getId()]);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->connections);
    }

    /**
     * @param string $key
     *
     * @return BackConnectionInterface
     */
    public function getConnection(string $key)
    {
        if (!$this->hasConnection($key)) {
            throw new PoolNotExistsException();
        }

        return $this->connections[$key];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasConnection(string $key):bool
    {
        return array_key_exists($key, $this->connections);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->connections;
    }

    public function queueTask(TaskInterface $task)
    {
        $this->taskQueue[]=$task;
        $this->pushTheQueue();
    }

    public function pushTheQueue()
    {
        //check tasks queue
        if (empty($this->taskQueue)) {
            return;
        }
        //detect free connection to process next task
        $freeConnection = null;
        foreach ($this->connections as $connection) {
            if (!$connection->isBusy()) {
                $freeConnection = $connection;
                break;
            }
        }

        if ($freeConnection === null) {
            return;
        }
        //extract task from queue and put it in connection
        $task = array_shift($this->taskQueue);
        $freeConnection->startTask($task);
    }
}
