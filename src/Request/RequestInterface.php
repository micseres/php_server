<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 05.09.18
 */

namespace Micseres\PhpServer\Request;

interface RequestInterface
{
    public function getAction(): string;

    public function getParams(): array;

    public function getClientId(): string;
}
