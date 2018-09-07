<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 07.09.18
 */

namespace Micseres\PhpServer\Response;

interface ResponseInterface
{
    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @return string
     */
    public function getMessage(): string;
}
