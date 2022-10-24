<?php
require_once __DIR__ . '/classes/Chat.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funcs.php';

$chat = new Chat();
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, 0, PORT);
socket_listen($socket);

$clientSocketArray = array($socket);

while (true) {
    $nullA = [];
    $newSocketArray = $clientSocketArray;
    socket_select($newSocketArray, $nullA, $nullA, 0, 10);

    if (in_array($socket, $newSocketArray)) {
        $newSocket = socket_accept($socket);
        $clientSocketArray[] = $newSocket;

        $header = socket_read($newSocket, 1024);
        $chat->sendHeaders($header, $newSocket, "messenger", PORT);

        $newSocketArrayIndex = array_search($socket, $newSocketArray);
        unset($newSocketArray[$newSocketArrayIndex]);
    }

    foreach ($newSocketArray as $newSocketArrayResource) {

        while (socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1) {
            $socketMessage = $chat->unseal($socketData);
            $messageObj = json_decode($socketMessage);

            if (isset($messageObj->is_group) and $messageObj->is_group and isset($messageObj->from_id) and isset($messageObj->to_id)) {
                send_message_group($messageObj->message, $messageObj->from_id, $messageObj->to_id);
                $to_id = $messageObj->get_group_id;
            } elseif (isset($messageObj->from_id) and isset($messageObj->to_id)) {
                send_message($messageObj->message, $messageObj->from_id, $messageObj->to_id);
                $to_id = $messageObj->get_id;
            }

            if (isset($messageObj->user) and isset($messageObj->avatar) and isset($messageObj->date)) {
                $chatMessage = $chat->createChatMessage($messageObj->user, $messageObj->message, $messageObj->avatar, $messageObj->date, $to_id, $messageObj->from_id, $messageObj->is_group);
                $chat->send($chatMessage, $clientSocketArray);
            }
            break 2;
        }

        $socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
        if ($socketData === false) {
            $newSocketArrayIndex = array_search($newSocketArrayResource, $clientSocketArray);
            unset($clientSocketArray[$newSocketArrayIndex]);
        }
    }
}

socket_close($socket);
