<?php
/**
 * @author: Andrii yakovlev <yawa20@gmail.com>
 * @since : 10.09.18
 */

$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);

$client->on(
    "connect",
    function (swoole_client $cli) {
        $request = [
            'action' => 'test',
            'params' => [
                'key' => 1,
            ],
        ];
        $cli->send(json_encode($request));
    }
);

$client->on(
    "receive",
    function (swoole_client $cli, $data) {
        echo($data."\n");
        $datastr = json_decode($data, true);
        if( !empty($datastr["message"]) && $datastr["message"] === 'Request accepted') {
            return;
        }

        $request = [
            'action' => 'test',
            'params' => [
                'key' => 1,
            ],
        ];
        $cli->send(json_encode($request));
    }
);

$client->on(
    "error",
    function (swoole_client $cli) {
        var_dump($cli);
        echo "Connection Error\n";
    }
);
$client->on(
    "close",
    function (swoole_client $cli) {
        echo "Connection closed\n";
    }
);

$client->connect('127.0.0.1', 9501);
//$client->connect('10.5.0.101', 9501);
