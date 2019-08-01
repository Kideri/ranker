<?php
    require_once 'configs/core.php';

    if (!isset ($_REQUEST)) {
        return;
	}

	$data = json_decode (file_get_contents ('php://input'));

	if (!isset ($data->type)) {
        echo 'Forbidden request.';
        return;
	}
    switch ($data->type) {
        case 'confirmation':
            echo $confirmationToken;
            break;
    }
?>
