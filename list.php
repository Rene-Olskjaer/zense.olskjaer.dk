<?php
$DEBUG = true;
require_once('setup/setup.php');
function save($login, $u)
{
    $conn = new mysqli($GLOBALS['mysqlserver'], $GLOBALS['user'], $GLOBALS['password']);
    mysqli_set_charset($conn, "utf8");
    $query = 'SELECT * from zense.box_' . $login;
    $result = mysqli_query($conn, $query);
    if (empty($result)) {
        $query = 'CREATE TABLE zense.box_' . $login . ' (id int DEFAULT 0,state varchar(20),type varchar(30),name varchar(30),room varchar(30),floor varchar(30))';
        mysqli_query($conn, $query);
    }
    $query = 'DELETE FROM zense.box_' . $login . ';';
    mysqli_query($conn, $query);
    foreach ($u as $value) {
        $query = 'INSERT INTO zense.box_' . $login . '(id,state,type,name,room,floor) VALUES (' . $value->id . ',"' . $value->state . '","' . $value->type . '","' . $value->name . '","' . $value->room . '","' . $value->floor . '");';
        mysqli_query($conn, $query);
    }
    $conn->close();
}

class unit
{
    public $id;
    public $state;
    public $type;
    public $name;
    public $room;
    public $floor;
}

function getall($ip, $port, $login)
{
    file_put_contents('zense.log', "Scanning:".$ip."\r\n", FILE_APPEND);
    $u = array();
    $units = array();
    $fp = pfsockopen($ip, $port);
    if ($fp) {
        fputs($fp, '>>Login ' . $login . '<<\r');
        $i=fgets($fp);
        file_put_contents('zense.log', $i."\r\n", FILE_APPEND);
        $u = array();
        fputs($fp, ">>Get Devices<<\r");
        $devs = fgets($fp);
        file_put_contents('zense.log', $devs."\r\n", FILE_APPEND);
        $devs = str_replace("Get Devices", "", $devs);
        $devs = str_replace("<<", "", $devs);
        $devs = str_replace(">>", "", $devs);
        $devs = str_replace(" ", "", $devs);
        $units = explode(',', $devs);
        foreach ($units as & $value) {
            file_put_contents('zense.log', $value."\r\n", FILE_APPEND);
            $value=trim($value);
            $x = new unit();
            $x->id = $value;
            fputs($fp, '>>Get Type ' . $value . '<<\r');
            $un = fgets($fp);
            file_put_contents('zense.log', $un."\r\n", FILE_APPEND);
            $un = str_replace(">>Get Type ", "", $un);
            $un = str_replace("<<", "", $un);
            if (trim($un)==18829) {
                $un=4;
            }
            $x->type = trim($un);
            fputs($fp, '>>Get Name ' . $value . '<<\r');
            $un = fgets($fp);
            file_put_contents('zense.log', $un."\r\n", FILE_APPEND);
            $un = str_replace(">>Get Name ", "", $un);
            $un = str_replace("<<", "", $un);
            $x->name = trim($un);
            fputs($fp, '>>Get ' . $value . '<<\r');
            $un = fgets($fp);
            $un = str_replace(">>Get ", "", $un);
            $un = str_replace("<<", "", $un);
            $x->state = trim($un);
            fputs($fp, '>>Get Room ' . $value . '<<\r');
            fputs($fp, '>>Get Room ' . $value . '<<\r');
            $un = fgets($fp);
            $un = str_replace(">>Get Room ", "", $un);
            $un = str_replace("<<", "", $un);
            $x->room = trim($un);
            fputs($fp, '>>Get Floor ' . $value . '<<\r');
            $un = fgets($fp);
            $un = str_replace(">>Get Floor ", "", $un);
            $un = str_replace("<<", "", $un);
            $x->floor = trim($un);
            array_push($u, $x);
        }
        fputs($fp, ">>Get Status<<\r");
        fgets($fp);
        fputs($fp, ">>Logout<<\r");
        fgets($fp);
        usleep(100000);
        fclose($fp);
        file_put_contents('zense.log', "Scanning complete:".$ip."\r\n", FILE_APPEND);
        save($login, $u);
    }
}

function zenseon($login, $id, $ip, $port)
{
    if ($id == 'homesim') {
        $fp = pfsockopen($ip, $port);
        if ($fp) {
            fputs($fp, ">>Login " . $login . "<<\r");
            fgets($fp);
            fputs($fp, ">>Sim On<<\r");
            $tmp = fgets($fp);
            fputs($fp, ">>Logout<<\r");
            usleep(100000);
            fclose($fp);
        }
    }
    if ($id == 'alloff') {
        $fp = pfsockopen($ip, $port);
        if ($fp) {
            fputs($fp, ">>Login " . $login . "<<\r");
            fgets($fp);
            fputs($fp, ">>Sluk Alt<<\r");
            $tmp = fgets($fp);
            fputs($fp, ">>Logout<<\r");
            usleep(100000);
            fclose($fp);
        }
    }
    if ($id == 'rescan') {
        getall($ip, $port, $login);
    }
}

function zenseoff($login, $id, $ip, $port)
{
    if ($id == 'homesim') {
        $fp = pfsockopen($ip, $port);
        if ($fp) {
            fputs($fp, ">>Login " . $login . "<<\r");
            fgets($fp);
            fputs($fp, ">>Sim Off<<\r");
            $tmp = fgets($fp);
            fputs($fp, ">>Logout<<\r");
            usleep(100000);
            fclose($fp);
        }
    }
}

$login= htmlspecialchars($_COOKIE['BOXID']);
$ip= htmlspecialchars($_COOKIE['IP']);
$port= htmlspecialchars($_COOKIE['PORT']);

if (isset($_POST['function2call']) && !empty($_POST['function2call'])) {
    $function2call = htmlspecialchars($_POST['function2call']);
    switch ($function2call) {
        case 'zenseon': zenseon($login, $_POST['id'], $ip, $port);break;
        case 'zenseoff': zenseoff($login, $_POST['id'], $ip, $port);break;
    }
}
