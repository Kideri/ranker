<?php
    require_once 'mysql.php';
    require_once 'bot.php';

    function decoder($object) {
        if (isset($object->action) && $object->action->type == "chat_invite_user" && $object->action->member_id == "-185060641") {
            register($object);
            return;
        }
        $message = $object->text;
        $peer_id = $object->peer_id;
        $words = explode(' ', $message);

        $words[0] = mb_convert_case($words[0], MB_CASE_LOWER, "UTF-8");

        if (!($words[0] === 'найт')) {
            return;
        }
        $command = '';
        for ($i = 1; $i < count($words); $i++) {
            $command .= $words[$i]. ' ';
        }
        $command = mb_convert_case($command, MB_CASE_LOWER, "UTF-8");
        if (!is_command($command, $object->from_id, $peer_id)) {
            send_message($peer_id, "Вы использовали неверную команду :c\n".
                                   "Попробуйте написать Найт помощь");
        }
    }

    function register($object) {
        $peer_id = $object->peer_id;
        $query = "CREATE TABLE IF NOT EXISTS id".$peer_id. 
        " (user_id INT, is_admin INT, messages_count INT, words_count INT, dayly_words INT, dayly_messages INT);";
        mysqli_query($GLOBALS['db'], $query);
        send_message ($peer_id, "$result");
    }

    function have_access($user_id, $access_level, $peer_id) {
        if ($access_level === 2) {
            $query = "SELECT * FROM bot_admins WHERE user_id=$user_id";
            $result = mysqli_query ($GLOBALS['db'], $query);
            return (mysqli_num_rows($result) === 1);
        } else {
            $request_params = array (
                'access_token' => $GLOBALS['token'],
                'peer_id' => $peer_id,
                'v' => '5.101'
            );
        
            $get_params = http_build_query ($request_params);

            $url = "https://api.vk.com/method/messages.getConversationMembers?". $get_params;
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($curl);
            curl_close($curl);
            $json = json_decode($json);

            foreach ($json->response->items as &$user) {
                $temp .= $user->is_admin. ' ';
                if (''.$user_id === ''.$user->member_id && ''.$user->is_admin === '1') {
                    return True;
                }
            }
            return False;
        }
    }

    function check_id($user_id, $peer_id) {
        $request_params = array (
            'peer_id' => $peer_id,
            'access_token' => $GLOBALS['token'],
            'v' => '5.101'
        );
    
        $get_params = http_build_query ($request_params);
    
        $url = "https://api.vk.com/method/messages.getConversationMembers?". $get_params;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($json);
        foreach ($json->response->profiles as &$user) {
            if (''.$user_id === ''.$user->id) {
                return True;
            }
            if (''.$user_id === ''.$user->first_name.$user->last_name) {
                return $user->id;
            }
        }
        return False;
    }

    function is_command($command, $request_id, $peer_id) {
        if (strpos($command, 'помощь') === 0) {
            $command = explode(' ', $command);
            if (count($command) >= 2 && $command[1] === 'админ') {
                send_message($peer_id, help_admin($request_id));
            } else {
                send_message($peer_id, help());
            }
            return True;
        }
        if (strpos($command, 'казнить') === 0) {
            send_message($peer_id, remove($request_id, $command, $peer_id));
            return True;
        }
        if (strpos($command, 'пользователи') === 0) {
            send_message($peer_id, get_users($request_id, $peer_id));
            return True;
        }
        if (strpos($command, 'добавить админа') === 0) {
            send_message($peer_id, add_admin($request_id, $command, $peer_id));
            return True;
        }
        if (strpos($command, 'онлайн') === 0) {
            send_message($peer_id, online($peer_id));
            return True;
        }
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
        $ch = curl_init( 'https://api.vk.com/method/messages.send');

        curl_setopt_array( $ch, array(
            CURLOPT_POST            => TRUE,
            CURLOPT_POSTFIELDS      => $request_params,

            CURLOPT_RETURNTRANSFER  => TRUE,
            CURLOPT_SSL_VERIFYPEER  => FALSE,
            CURLOPT_SSL_VERIFYHOST  => FALSE,
            CURLOPT_CONNECTTIMEOUT  => 10,
            CURLOPT_TIMEOUT         => 10,
        ));

        $result = curl_exec( $ch);

        curl_close($curl);
    }
?>