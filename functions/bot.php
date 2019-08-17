<?php
    function help() {
        return 
        "Формат исползования команд:
        Найт команда (обязательные параметры) [побочные параметры]\n
        Доступные команды:
        1. повысить (Имя) [крестьянин, солдат, рыцарь, командир, генерал] - повышение статуса
        2. казнить (Имя) - кик
        3. выпустить (Имя) - разбанить
        4. голосование (Имя) - кик голосованием
        5. поставить цель (значение) [срок] - количество символов которое нужно написать чтобы не вылететь из чата (если выполняешь поставленную задачу получаешь печеньки
        6. магазин - открыть магазин
        6.1 магазин купить (вещь):
        (за печеньки купить:
        звание, см. п.2
        выйти из темницы п.4
        иммунитет к кику)
        7. суесыд - выйти из беседы
        8. помощь админ - Показ админских команд
        9. Онлайн - проверка онлайна в беседе";
    }

    function help_admin($request_id) {
        if (!have_access($request_id, 2)) {
            return "Простите, но Ваших прав недостаточно :с";
        }
        return
        "Команды разработчиков:\n".
        "1. Добавить админа (ID) - добавить администратора бота";
    }

    function get_users($request_id, $peer_id) {
        if (!have_access($request_id, 2, $peer_id)) {
            return "Простите, но Ваших прав недостаточно :с";
        }
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
        $members_id = "Пользователи: \n";
        foreach ($json->response->profiles as &$user) {
            $members_id .= $user->first_name. ' '. $user->last_name. ' '. $user->screen_name;
            if ($user->screen_name != 'id'.$user->id) {
                $members_id .= ' (id'. $user->id. ')';
            }
            $members_id .= "\n";
        }
        return $members_id;
    }

    function add_admin($request_id, $command, $chat_id) {
        if (!have_access($request_id, 2)) {
            return "Простите, но Ваших прав недостаточно :с";
        }
        $user_id = explode(' ', $command);
        if (count($user_id) < 3) {
            return "Не указан ID пользователя :с";
        }
        $user_id = $user_id[2];
        $temp = check_id($user_id, $chat_id);
        if ($temp === False) {
            return "Указан неверный ID пользователя :с";
        }
        if (!($temp === True)) {
            $user_id = $temp;
        }
        $query = "INSERT INTO bot_admins (user_id) VALUE ($user_id)";
        mysqli_query ($GLOBALS['db'], $query);
        return "Админ успешно добавлен";
    }

    function remove($request_id, $command, $chat_id) {
        if (!have_access($request_id, 1, $chat_id)) {
            return "Ваших прав не достаточно";
        }
        $id = explode(' ', $command);
        if (count($id) < 2) {
            return "Необходимо указать id добавляемого пользователя.";
        }
        $id = intval($id[1]);
        $temp = check_id($id, $chat_id);
        if ($temp === False) {
            return "Указанный пользователь не найден в беседе :с";
        }
        if ($temp != True) {
            $id = check_id($id, $chat_id);
        }
        $request_params = array (
            'user_id' => "$id",
            'chat_id' => $chat_id-2000000000,
            'access_token' => $GLOBALS['token'],
            'v' => '5.101'
        );
    
        $get_params = http_build_query ($request_params);
    
        $url = "https://api.vk.com/method/messages.removeChatUser?". $get_params;
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($json);
        if ($json->response === 1) {
            return "Пользователь успешно казнен";
        } else {
            return "Не удалось казнить пользователя";
        }
    }

    function online($peer_id) {
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
        $online = '';

        $current = '';
        foreach ($json->response->profiles as &$user) {
            if ($user->online === '0' || !have_access($user->id, 2, $peer_id)) {
                continue;
            }
            $current .= '@id'.$user->id.'('.$user->first_name.' '.$user->last_name.")\n";
        }

        if ($current === '') {
            $online .= "Админов бота нет в сети :с\n\n";
        } else {
            $online .= "Админы бота:\n".$current."\n\n";
        }

        $current = '';
        foreach ($json->response->profiles as &$user) {
            if ($user->online === '0' || !have_access($user->id, 1, $peer_id)) {
                continue;
            }
            $current .= '@id'.$user->id.'('.$user->first_name.' '.$user->last_name.")\n";
        }
        if ($current === '') {
            $online .= "Админов чата нет в сети :с\n\n";
        } else {
            $online .= "Админы чата:\n".$current."\n\n";
        }

        $current = '';
        foreach ($json->response->profiles as &$user) {
            if ($user->online === '0' || have_access($user->id, 2, $peer_id) || have_access($user->id, 1, $peer_id)) {
                continue;
            }
            $current .= '@id'.$user->id.'('.$user->first_name.' '.$user->last_name.")\n";
        }
        if ($current === '') {
            $online .= "Простых пользователей нет в сети :с\n\n";
        } else {
            $online .= "Простые пользователи:\n".$current;
        }

        return $online;
    }
?>