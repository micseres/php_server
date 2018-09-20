<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\ConnectionPool;

use Micseres\PhpServer\Server;
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

    /** @var int */
    private $tasksProcessed = 0;

    /** @var float  */
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
     * @return float
     */
    public function getLoading(): float
    {
        return (count($this->tasks) + ($this->hasOpenTask()?1:0)) * $this->averageDuration;
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
        if ($this->hasOpenTask()) {
            $this->postDispatch($this->getCurrentTask());
        }
        $this->currentTask = array_shift($this->tasks);

        if (null === $this->currentTask) {
            return;
        }

        //hitrozhopiy workaround
        //this case needs to prevent queuing tasks to new connection,
        //when we do not know it`s power
        if (0 === $this->tasksProcessed) {
            $this->averageDuration = 999999999999;
        }

        $this->currentTask->start();

        $isSend = $this->server->send($this->getId(), $this->currentTask->getStringParams());

        Server::getLogger()->info("BACK: request sent to service {$this->currentTask->getStringParams()}", ['microservice'=>  $this->getId(), 'data' => $this->currentTask->getStringParams()]);
    }

    protected function postDispatch(Task $task)
    {
        $duration = microtime(true) - $task->getStartTime();
        //hitrozhopiy workaround
        //this case needs to allow queuing tasks to new connection,
        //when we already got first duration
        //@see startNext()
        if (0 === $this->tasksProcessed) {
            $this->averageDuration = 0;
        }

        $this->averageDuration = ($this->averageDuration * $this->tasksProcessed + $duration)
                                 / ($this->tasksProcessed + 1);

        $this->tasksProcessed++;
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

    /**
     * @return \swoole_server
     */
    public function getServer(): \swoole_server
    {
        return $this->server;
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
