<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\Request;

/**
 * Class BackRequest
 * @package Micseres\PhpServer\Request
 */
class BackRequest implements RequestInterface
{
    public function getRoute(): string
    {
        return '';
    }

    public function getPayload(): array
    {
        return [];
    }

    public function getClientId(): string
    {
        return '';
    }
}
