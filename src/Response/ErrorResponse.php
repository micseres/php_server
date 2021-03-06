<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 07.09.18
 */

namespace Micseres\PhpServer\Response;

use Micseres\PhpServer\Task\Task;

/**
 * Class ErrorResponse
 * @package Micseres\PhpServer\Response
 */
class ErrorResponse extends Response
{
    /** @var string  */
    private $taskId;

    /**
     * ErrorResponse constructor.
     *
     * @param Task   $task
     * @param string $message
     */
    public function __construct(Task $task, string $message)
    {
        $this->taskId = $task->getId();
        parent::__construct($message, self::STATUS__FAIL);
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
            'taskId' => $this->getTaskId()
        ];
    }

    /**
     * @return string
     */
    public function getTaskId(): string
    {
        return $this->taskId;
    }
}
