<?php
require_once 'functions.php';
require_once 'db-connect.php';

        ini_set('display_errors', '1');
		ini_set('display_startup_errors', '1');
		error_reporting(E_ALL);

function checkUidExistence($uid_input) {
    global $db_host, $db_user, $db_pass, $db_name;
    $q = db_do_query_return_obj('SELECT * FROM uid_db WHERE uid = "' . $uid_input . '";');
    $res = $q->num_rows;

    if ($res > 0) {
        return 1;
    }
    else {
        return 0;
    }
}

function db_update_value($colname, $colnamestatus, $uID, $success_msg) {
    $q = db_do_query_return_obj('UPDATE `uid_db` SET ' . $colname  . '= ' . $colnamestatus . ' WHERE `uid` = "' . $uID . '";');

        if ($q) {
            echo $success_msg;
        }
        else {
            echo 'Błąd łączenia z bazą danych';
        }
}

function db_execute_action($cmd, $uID) {
    global $toga_price, $biret_price;

        switch ($cmd) {
            case 'toga-declare':
                $success_msg = 'Pomyślnie dopisaliśmy Cię do listy: Toga (' . $toga_price . ' zł)!';
                db_update_value('registration_toga', 1, $uID, $success_msg);

                break;
            case 'toga-resign':
                $success_msg = 'Pomyślnie usunęliśmy Cię z listy: Toga (' . $toga_price . ' zł)!';
                db_update_value('registration_toga', 0, $uID, $success_msg);
                
                break;
            case 'biret-declare':
                $success_msg = 'Pomyślnie dopisaliśmy Cię do listy: Biret (' . $biret_price . ' zł)!';
                db_update_value('registration_biret', 1, $uID, $success_msg);
                
                break;
            case 'biret-resign':
                $success_msg = 'Pomyślnie usunęliśmy Cię z listy: Biret (' . $biret_price . ' zł)!';
                db_update_value('registration_biret', 0, $uID, $success_msg);
                
                break;
            default:
                echo 'Nieznana komenda';
        }
}

if (isset($_POST['cmd']) && $_POST['cmd'] != null && isset($_POST['uID']) && strlen($_POST['uID']) === $uid_length) {

    $cmd = $_POST['cmd'];
    $uID = test_input($_POST['uID']);

    $uid_exists = checkUidExistence($uID);
    
    if (!$uid_exists) {
        echo 'uID nie istnieje';
        die();
    }
    else {
        db_execute_action($cmd, $uID);
    }

}


?>