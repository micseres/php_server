<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

namespace Micseres\PhpServer\Response;

use Micseres\PhpServer\Task\Task;

/**
 * Class TaskResultResponse
 * @package Micseres\PhpServer\Response
 */
class TaskResultResponse extends Response
{
    /** @var string  */
    private $taskId;

    /** @var string  */
    private $result;

    public function __construct(Task $task, string $result)
    {
        $this->taskId = $task->getId();
        $this->result = $result;
        parent::__construct("task completed", self::STATUS__OK);
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
            'status' => $this->getStatus(),
            'message' => $this->getMessage(),
            'taskId' => $this->getTaskId(),
            'result' => $this->getResult(),
        ];
    }

    /**
     * @return string
     */
    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
