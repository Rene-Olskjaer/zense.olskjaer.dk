<?php
header('Content-type:text/html; charset=utf-8');
require_once('setup/setup.php');
?>
<!DOCTYPE html>
<html lang=da>
<head>
 <title>ZenseHome</title>
 <script src="sw-toolbox/companion.js" data-service-worker="sw.js"></script>
  <link rel="manifest" href="manifest/manifest.json">
<meta name="description" content="ZenseHome">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="apple-mobile-web-app-title" content="ZenseHome">
<meta name="viewport" content="width=device-width, initial-scale=0.8">
<meta name="theme-color" content="#ffffff">
<link rel="apple-touch-icon" href="images/ios/ios-appicon-120-120.png"> 
<link rel="apple-touch-icon" sizes="180x180" href="images/ios/ios-appicon-180-180.png">  
<link rel="apple-touch-icon" sizes="152x152" href="images/ios/ios-appicon-152-152.png">  
<link rel="apple-touch-icon" sizes="167x167" href="images/ios/ios-appicon-152-152.png">  
<link rel="icon" type="image/png" href="images/chrome/chrome-favicon-16-16.png" sizes="16x16">  
<link rel="icon" type="image/png" href="images/firefox/firefox-general-32-32.png" sizes="32x32">  
<link rel="icon" type="image/png" href="images/android/android-launchericon-96-96.png" sizes="96x96">  
<style>
  <?php include 'CSS/main.css'; ?>
</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="zense.js"></script>
</head>
<body style="background-color:lightgrey;">

<div class=fadeMe id='progress' style='display:none'>
   <div class=container>
       <img class=img src='images/ajax-loader.png' alt='loader'> 
   </div>
</div>

<script>      $("#progress").show(); </script>
<?php
class unit
{
    public $id;
    public $state;
    public $type;
    public $name;
    public $room;
    public $floor;
    public $dim;
}

function updatestatus($ip, $port, $login)
{
    $u = array();
    $conn = new mysqli($GLOBALS['mysqlserver'], $GLOBALS['user'], $GLOBALS['password']);
    mysqli_set_charset($conn, "utf8");
    $query = 'SELECT * from zense.box_' . $login . ';';
    $result = mysqli_query($conn, $query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $x = new unit();
            $x->id = $row["id"];
            $x->state = $row["state"];
            $x->type = $row["type"];
            $x->name = $row["name"];
            $x->room = $row["room"];
            $x->floor = $row["floor"];
            $x->dim = $row["dim"];
            array_push($u, $x);
        }
    }
    $fp = pfsockopen($ip, $port);
    if ($fp) {
        fputs($fp, '>>Login ' . $login . '<<\r');
        fgets($fp);
        fputs($fp, ">>Get Status<<\r");
        $tmp = fgets($fp);
        $tmp = str_replace("Get Status", "", $tmp);
        $tmp = str_replace("<<", "", $tmp);
        $tmp = str_replace(">>", "", $tmp);
        $tmp = trim(str_replace("0x", "", $tmp));
        $state = hexdec($tmp);
        #file_put_contents('zense.log','State:'.$state."\r\n",FILE_APPEND);
        #file_put_contents('zense.log','Antal:'. count($u) . "\r\n",FILE_APPEND);
        setcookie('STATE', $state);
        $cnt = 0;
        if (count($u)>31) { 
            $hi=$state >> 32;
            $lo=$state & 0xFFFFFFFF;
            $state=$lo*0x100000000+$hi;
            #file_put_contents('zense.log','NewState:'.$state."\r\n",FILE_APPEND);
        }
        foreach ($u as $t) {
           $a = 1 << $cnt;
           $cnt++;
           $newstate = ($state & $a) ? 100 : 0;
           if ($t->dim  == true) {
              fputs($fp, ">>Get ".$t->id."<<\r");
              $tmp = fgets($fp);
       	      $tmp = str_replace("Get ", "", $tmp);
              $tmp = str_replace("<<", "", $tmp);
              $tmp = str_replace(">>", "", $tmp);
              $tmp = trim(str_replace("0x", "", $tmp));
              $newstate=$tmp;
           }
           $query = 'UPDATE zense.box_'.$login.' SET state = '.$newstate.' WHERE id = '.$t->id.';';
           mysqli_query($conn, $query);
           mysqli_commit($conn);
        }
        fputs($fp, ">>Logout<<\r");
        usleep(100000);
        fclose($fp);
        return true;
    } else return false;
    $conn->close();
  
}

function showstatus($ip, $port, $login)
{
    $oldroom='';
    echo '<form action="settings.php" method="post" class="zense" id="zense">';
    echo '<div class="top">';
    echo '<table style="width:100%; border: 1px solid #CCC; border-collapse: collapse;">';
    if ($GLOBALS['connected']) {
       echo '<tr style="background-color:#3EA5C4;">';
   } else {
      echo '<tr style="background-color:#EA6060;">';
    }
    echo '<th style="border:none;text-align: left; width:20%"><input type="image" src="images/settings.png" alt="Settings" name="settings" width="60" height="60"></th>';
    if ($GLOBALS['connected']) {
       echo '<th style="border:none;text-align: center; font-size:xx-large;">ZenseHome</th>';
    } else {
       echo '<th style="border:none;text-align: center; font-size:x-large;">Ingen forbindelse</th>';
    }
 
    echo '<th style="border:none;text-align: right"><input type="image" src="images/list.png" alt="list" name="list" width="50" height="50"></th>';
    echo '</tr>';
    echo '</table>';
    echo '</div>';
 
    echo '<div class="push">';
    echo '<table id="thetable" style="width:100%; border: 1px solid #CCC; border-collapse: collapse;">';
 
    $conn = new mysqli($GLOBALS['mysqlserver'], $GLOBALS['user'], $GLOBALS['password']);
    mysqli_set_charset($conn, "utf8");

    $query = "SELECT * from zense.box_".$login." where name NOT LIKE '%Bruges ikke%' AND name not like '%Timeout%' ORDER BY floor,room,type,name;";
    $result = mysqli_query($conn, $query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row["id"];
            $state = $row["state"];
            $type = $row["type"];
            $room = trim($row["room"], "'");
            $name = trim($row["name"], "'");
            if ($room<>$oldroom) {
                echo  '<tr style="background-color:lightblue;"><th colspan="3">'.$room.'</th></tr>';
                $oldroom = $room;
            }
            $dim=$row["dim"];
            echo '<tr>';
            switch ($type) {
           case 1:
             if ($state==0) {
                 echo '<th style="border:none;"><img id="'.$id.'"src="images/light_off-100.png" alt="off" style="width:60%;"></th>';
             } else {
                 echo '<th style="border:none;"><img id="'.$id.'"src="images/light_on-100.png" alt="on" style="width:60%;"></th>';
             }
             break;
           case 4:
             if ($state==0) {
                 echo '<th style="border:none;"><img id="'.$id.'"src="images/zensehome-stikkontakt-off-100.png" alt="off" style="width:60%;"></th>';
             } else {
                 echo '<th style="border:none;"><img id="'.$id.'"src="images/zensehome-stikkontakt-on-100.png" alt="on" style="width:60%;"></th>';
             }
             break;
           case 18829:
             if ($state==0) {
                 echo '<th style="border:none;"><img id="'.$id.'"src="images/zensehome-stikkontakt-off-100.png" alt="off" style="width:60%;"></th>';
             } else {
                 echo '<th style="border:none;"><img id="'.$id.'"src="images/zensehome-stikkontakt-on-100.png" alt="on" style="width:60%;"></th>';
             }
             break;
           case 108:
             if ($state==0) {
                 echo '<th style="border:none;"><img id="'.$id.'"src="images/uniudtag-med-skygge-off-100.png" alt="off" style="width:60%;"></th>';
             } else {
                 echo '<th style="border:none;"><img id="'.$id.'"src="images/uniudtag-med-skygge-on-100.png" alt="on" style="width:60%;"></th>';
             }
             break;
           default:
             echo '<th id="'.$id.'" style="border:none;">unknown</th>';
           }
            echo '<th style="border:none;text-align: left; font-size:x-large;">'. $name.'</th>';
            if ($dim==1) {
                 echo  '<th style="border:none;text-align: right; width:20%">';
 
                  echo '<input name="'.$id.'" type="range"  min="0" max="100" value="'.$state.'" onmouseout="SetVal('.$id.',this.value)" oninput="SetVal('.$id.',this.value)" onchange="SetVal('.$id.',this.value)">';
                  echo '</th>';
            } else {
              echo  '<th style="border:none;text-align: right; width:20%"> <label class="switch"> <input name="'.$id.'"';
              if ($state>0) {
                echo  ' type="checkbox" checked> <span class="slider round"></span></label>';
                echo '</th>';
              } else {
                  echo  ' type="checkbox" > <span class="slider round"></span></label></th>';
              }
            }

            echo '</tr>';
        }
    }
    $conn->close();
    echo '</table>';
    echo '</div>';
    echo '</form>';
    echo '<script>';
    echo 'attachCheckboxHandlers();';
    echo '</script>';
}
   $login= htmlspecialchars($_COOKIE['BOXID']);
   $ip= htmlspecialchars($_COOKIE['IP']);
   $port= htmlspecialchars($_COOKIE['PORT']);
   $oldstate = htmlspecialchars($_COOKIE['STATE']);
   $connected = updatestatus($ip, $port, $login);
   if ($connected == false) {echo '<script> setTimeout(function(){window.location = window.location}, 5000); </script>';};
   showstatus($ip, $port, $login);
?>
<script> $("#progress").hide(); </script>

</body>
<script src="longpress.js"></script>

</html>
