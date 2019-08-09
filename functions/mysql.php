<?php
    function get_param_from_db($peer_id, $user_id, $param_name) {
        $query = "SELECT * FROM id$peer_id WHERE user_id=$user_id;";
        $result = mysqli_query($GLOBALS['db'], $query);
        return $result[$param_name];
    }

    function set_param_in_db($peer_id, $user_id, $param_name, $param_value) {
        $query = "UPDATE id$peer_id SET $param_name=$param_value WHERE user_id=$user_id;";
        mysqli_query($GLOBALS['db'], $query);
    }
?>