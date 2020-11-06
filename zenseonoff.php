<?php
require_once('setup/setup.php');
function zenseon($login, $id, $ip, $port)
{
    $conn = new mysqli($GLOBALS['mysqlserver'], $GLOBALS['user'], $GLOBALS['password']);
    mysqli_set_charset($conn, "utf8");
    $query = 'UPDATE zense.box_'.$login.' SET state = 100 WHERE id = '.$id.';';
    mysqli_query($conn, $query);
    mysqli_commit($conn);

    $conn->close();
    $fp = pfsockopen($ip, $port);
    if ($fp) {
        fputs($fp, ">>Login " . $login . "<<\r");
        fgets($fp);
        fputs($fp, ">>Set ".$id." 1<<\r");
        $tmp = fgets($fp);
        fputs($fp, ">>Logout<<\r");
        usleep(100000);
        fclose($fp);
    }
}

function zenseoff($login, $id, $ip, $port)
{
    $conn = new mysqli($GLOBALS['mysqlserver'], $GLOBALS['user'], $GLOBALS['password']);
    mysqli_set_charset($conn, "utf8");
    $query = 'UPDATE zense.box_'.$login.' SET state = 0 WHERE id = '.$id.';';
    mysqli_query($conn, $query);
    mysqli_commit($conn);

    $conn->close();
    $fp = pfsockopen($ip, $port);
    if ($fp) {
        fputs($fp, ">>Login ". $login . "<<\r");
        fgets($fp);
        fputs($fp, ">>Set ".$id." 0<<\r");
        $tmp = fgets($fp);
        fputs($fp, ">>Logout<<\r");
        usleep(100000);
        fclose($fp);
    }
}

function fade($percent,$login, $id, $ip, $port)
{
    $conn = new mysqli($GLOBALS['mysqlserver'], $GLOBALS['user'], $GLOBALS['password']);
    mysqli_set_charset($conn, "utf8");
    $query = 'UPDATE zense.box_'.$login.' SET state = $percent WHERE id = '.$id.';';
    mysqli_query($conn, $query);
    mysqli_commit($conn);
    $conn->close();
    $fp = pfsockopen($ip, $port);
    if ($fp) {
        fputs($fp, ">>Login ". $login . "<<\r");
        fgets($fp);
        fputs($fp, ">>Fade ".$id." ".$percent."<<\r");
        $tmp = fgets($fp);
        fputs($fp, ">>Logout<<\r");
        usleep(100000);
        fclose($fp);
    }
}

$login= htmlspecialchars($_COOKIE['BOXID']);
$ip= htmlspecialchars($_COOKIE['IP']);
$port= htmlspecialchars($_COOKIE['PORT']);

if (isset($_POST['function2call']) && !empty($_POST['function2call'])) {
    $function2call = htmlspecialchars($_POST['function2call']);
    switch ($function2call) {
        case 'zenseon': zenseon($login, htmlspecialchars($_POST['id']), $ip, $port);break;
        case 'zenseoff': zenseoff($login, htmlspecialchars($_POST['id']), $ip, $port);break;
        case 'fade': fade(htmlspecialchars($_POST['val']),$login, htmlspecialchars($_POST['id']), $ip, $port);break;
    }
}
