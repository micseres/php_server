<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

$isRegistered = false;

$name = getenv("NAME");

$client->on(
    "connect",
    function (swoole_client $cli) {
        echo("connect\n");
        $request = [
            'action' => 'register',
            'params' => [
                'route' => 'test',
            ],
        ];
        echo "send ".json_encode($request),"\n";
        $cli->send(json_encode($request));
    }
);
$client->on(
    "receive",
    function (swoole_client $cli, $data) use (&$isRegistered, $name) {
        $data = trim($data);
        if (empty($data)) {
            return;
        }
        if ('Hello' === $data) {
            return;
        }
        if ('OK' === $data) {
            $isRegistered = true;

            return;
        }
        $cli->send(json_encode("Response: {$name}"));
    }
);
$client->on(
    "error",
    function (swoole_client $cli) {
        echo "Connection Error\n";
    }
);
$client->on(
    "close",
    function (swoole_client $cli) {
        echo "Connection Closed\n";
    }
);
//$client->connect('127.0.0.1', 9502);
$client->connect('10.5.0.101', 9502);
