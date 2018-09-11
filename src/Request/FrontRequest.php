<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\Request;

use Micseres\PhpServer\Exception\InvalidRequestException;

/**
 * Class FrontRequest
 * @package Micseres\PhpServer\Request
 * @property string $action
 * @property array $params
 */
class FrontRequest implements RequestInterface
{
    /** @var string  */
    private $clientId;

    /**
     * FrontRequest constructor.
     *
     * @param string $clientId
     * @param string $data
     */
    public function __construct(string $clientId, string $data)
    {
        $this->clientId = $clientId;

        $data = json_decode(trim($data), true);

        if (null === $data) {
            throw  new InvalidRequestException("Invalid request format");
        }

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }
}
