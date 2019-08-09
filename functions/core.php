<?php
    require_once 'mysql.php';

    function decoder($object) {
        if ($object->action->type == "chat_invite_user" && $object->action->member_id == "-185060641") {
            register($object);
            return;
        }
        $message = $object->text;
        $peer_id = $object->peer_id;
        $words = explode(' ', $message);

        if (!($words[0] == 'ranker')) {
            return;
        }
        $command = $words[1];
        if (!is_command($command, $peer_id)) {
            send_message($peer_id, "Unknown command :c");
        }
    }

    function register($object) {
        $peer_id = $object->peer_id;
        $query = "CREATE TABLE IF NOT EXISTS id".$peer_id. 
        " (user_id INT, is_admin INT, messages_count INT, words_count INT, dayly_words INT, dayly_messages INT);";
        mysqli_query($GLOBALS['db'], $query);
        send_message ($peer_id, "$result");
    }

    function is_command($command, $peer_id) {
        return False;
    }

    function send_message($peer_id, $message_text) {
        $request_params = array (
            'message' => "$message_text",
            'random_id' => mt_rand (),
            'peer_id' => $peer_id,
            'access_token' => $GLOBALS['token'],
            'v' => '5.101'
        );
    
        $get_params = http_build_query ($request_params);
    
        $url = "https://api.vk.com/method/messages.send?". $get_params;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($curl);
        curl_close($curl);
    }
?>