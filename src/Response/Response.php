<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 07.09.18
 */

namespace Micseres\PhpServer\Response;

/**
 * Class Response
 * @package Micseres\PhpServer\Response
 */
class Response implements ResponseInterface, \JsonSerializable
{
    const STATUS__OK = 'OK';
    const STATUS__FAIL = 'FAIL';

    /** @var string  */
    private $status;

    /** @var string  */
    private $message;

    public function __construct(string $message, string $status = self::STATUS__OK)
    {
        $this->message = $message;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
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
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
