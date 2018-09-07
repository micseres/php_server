<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\ConnectionPool;

use Micseres\PhpServer\Task\Task;
use Micseres\PhpServer\Task\TaskInterface;

/**
 * Class ConnectionPool
 * @package Micseres\PhpServer\ConnectionPool
 */
class BackConnection implements ConnectionInterface, BackConnectionInterface, \JsonSerializable
{

    /** @var string  */
    private $identifier;

    /** @var TaskInterface[] */
    private $tasks = [];

    /** @var TaskInterface|null */
    private $currentTask;

    /** @var \swoole_server  */
    private $server;

    /**
     * ConnectionPool constructor.
     *
     * @param \swoole_server $server
     * @param int            $identity
     */
    public function __construct(\swoole_server $server, int $identity)
    {
        $this->server = $server;
        $this->identifier = (string) $identity;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->identifier;
    }

    /**
     * @param TaskInterface|Task $task
     */
    public function addTask(TaskInterface $task)
    {
        $task->setConnectionId($this->getId());
        array_push($this->tasks, $task);
        if (!$this->hasOpenTask()) {
            $this->startNext();
        }
    }

    /**
     * @return int
     */
    public function getLoading(): int
    {
        return count($this->tasks) + ($this->hasOpenTask()?1:0);
    }

    /**
     * @return bool
     */
    public function hasOpenTask(): bool
    {
        return (null !== $this->currentTask);
    }

    /**
     * @return TaskInterface|Task
     */
    public function getCurrentTask(): TaskInterface
    {
        return $this->currentTask;
    }

    public function startNext()
    {
        $this->currentTask = array_shift($this->tasks);

        if (null === $this->currentTask) {
            return;
        }
        $this->server->send($this->getId(), $this->currentTask->getStringParams());
    }

    /**
     * @return TaskInterface[]|Task[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
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
        return [
            'identifier' => $this->identifier,
            'loading' => $this->getLoading(),
            'currentTask' => $this->currentTask,
            'tasksQueue' => $this->tasks
        ];
    }
}
