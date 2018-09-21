<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\ConnectionPool;

use Micseres\PhpServer\Response\TaskResultResponse;
use Micseres\PhpServer\Server;
use Micseres\PhpServer\Task\Task;
use Micseres\PhpServer\Task\TaskInterface;

/**
 * Class ConnectionPool
 * @package Micseres\PhpServer\ConnectionPool
 */
class BackConnection implements ConnectionInterface, BackConnectionInterface, \JsonSerializable
{
    /** @var string */
    private $identifier;

    /** @var TaskInterface|null */
    private $currentTask;

    /** @var \swoole_server */
    private $server;

    /** @var int */
    private $tasksProcessed = 0;

    /** @var float */
    private $averageDuration = 0;

    /** @var string|null */
    private $sharedKey;

    /**
     * ConnectionPool constructor.
     *
     * @param \swoole_server $server
     * @param int            $identity
     */
    public function __construct(\swoole_server $server, int $identity)
    {
        $this->server     = $server;
        $this->identifier = (string)$identity;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->identifier;
    }

    /**
     * @return bool
     */
    public function isBusy(): bool
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
            'identifier'      => $this->identifier,
            'tasksProcessed'  => $this->tasksProcessed,
            'averageDuration' => $this->averageDuration,
            'currentTask'     => $this->currentTask,
        ];
    }

    /**
     * @param TaskInterface $task
     */
    public function startTask(TaskInterface $task)
    {
        if ($this->isBusy()) {
            throw new \RuntimeException("Connection already has a task in process");
        }

        $this->currentTask = $task;
        $this->currentTask->start();

        $this->server->send($this->getId(), $this->currentTask->getStringParams());

        Server::getLogger()->info(
            "BACK: request sent to service {$this->currentTask->getStringParams()}",
            [
                'microservice' => $this->getId(),
                'data'         => $this->currentTask->getStringParams(),
            ]
        );
    }

    /**
     * @param string $data
     */
    public function finishTask(string $data)
    {
        $task = $this->getCurrentTask();
        $this->postDispatch($task);

        $response          = new TaskResultResponse($task, $data);
        $this->currentTask = null;

        $this->server->send($task->getClientId(), $response);
    }

    protected function postDispatch(Task $task)
    {
        $duration = microtime(true) - $task->getStartTime();

        $this->averageDuration = ($this->averageDuration * $this->tasksProcessed + $duration)
                                 / ($this->tasksProcessed + 1);

        $this->tasksProcessed++;
    }

    /**
     * @return null|string
     */
    public function getSharedKey(): ?string
    {
        return $this->sharedKey;
    }

    /**
     * @param null|string $sharedKey
     */
    public function setSharedKey(?string $sharedKey): void
    {
        $this->sharedKey = $sharedKey;
    }
}
