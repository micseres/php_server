<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 07.09.18
 */

namespace Micseres\PhpServer\Task;

/**
 * Class Task
 * @package Micseres\PhpServer\Task
 */
class Task implements TaskInterface, \JsonSerializable
{
    /** @var string  */
    private $taskId;

    /** @var string  */
    private $clientId;

    /** @var array  */
    private $params;

    /** @var string|null */
    private $connectionId;

    public function __construct(string $clientId, array $params)
    {
        $this->clientId = $clientId;
        $this->params = $params;
        $this->taskId = uniqid('task_');
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return $this->taskId;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $connectionId
     */
    public function setConnectionId(string $connectionId)
    {
        $this->connectionId = $connectionId;
    }

    /**
     * @return null|string
     */
    public function getConnectionId(): ?string
    {
        return $this->connectionId;
    }

    /**
     * @return string
     */
    public function getStringParams(): string
    {
        return json_encode($this->params);
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
            'taskId' => $this->taskId,
            'params' => $this->params
        ];
    }
}
