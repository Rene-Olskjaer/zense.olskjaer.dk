<?php
header('Content-type:text/html; charset=utf-8');
require_once('setup/setup.php');
?>
<!DOCTYPE html>
<html lang=da>
<head>
 <title>ZenseHome</title>
<meta name="viewport" content="width=device-width, initial-scale=0.6">

<style>


<?php include 'CSS/main.css'; ?>
</style>
<script src="/jquery.min.js"></script>

</head>
<body style="background-color:lightgrey;">
<div class=fadeMe id='progress' style='display:none'>
   <div class=container>
       <img class=img src='images/ajax-loader.png' alt='loader'>
   </div>
</div>

<?php

function edit($ip, $port, $login, $id)
{
    $u = array();
    $conn = new mysqli($GLOBALS['mysqlserver'], $GLOBALS['user'], $GLOBALS['password']);
    mysqli_set_charset($conn, "utf8");
    $query = 'SELECT * from zense.box_' . $login . ' where id='.$id.';';
    $result = mysqli_query($conn, $query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $name = $row["name"];
            $room = $row["room"];
            $floor = $row["floor"];
            $dim = $row["dim"];
        }
    }
    $conn->close();
    echo '<form action="edit.php?id='.$id.'" method="post">';
    echo '<table style="width:100%">';
    echo '<tr style="background-color:#3EA5C4;">';
    echo '<td style="border:none;text-align: left"><a href="/"><img src="images/undo.png" alt="undo" id="undo" width="60" height="60"></a></td>';
    echo '<td style="border:none;text-align: right"><input type="image" src="images/save.png" alt="Submit" name="save" width="60" height="60"></td>';
    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">Floor</th>';
    echo  '<th style="text-align: right; width:20%"> <input type="text" name="floor" value='.$floor.' id="inputForm1"></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">Room</th>';
    echo  '<th style="text-align: right; width:20%"> <input type="text" name="room" value='.$room.' id="inputForm1"></th>';
    echo '</tr>';
    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">Name</th>';
    echo  '<th style="text-align: right; width:20%"> <input type="text" name="name" value='.$name.' id="inputForm1"></th>';
    echo '</tr>';

    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">Dimmable</th>';
    echo  '<th style="text-align: right; width:20%"> <label class="switch"> <input name="dim"';
    if ($dim>0) {
        echo  ' type="checkbox" checked> <span class="slider round"></span></label></th>';
    } else {
        echo  ' type="checkbox" > <span class="slider round"></span></label></th>';
    }
    echo '</tr>';

     echo '</table>';
    echo '</form>';


}


$login = $_COOKIE['BOXID'] ?? '';
$ip    = $_COOKIE['IP'] ?? '';
$port  = (int)($_COOKIE['PORT'] ?? 0);
$oldstate = (int)($_COOKIE['STATE'] ?? 0);


if (isset($_POST['save_x'])) {
    $conn = new mysqli($GLOBALS['mysqlserver'], $GLOBALS['user'], $GLOBALS['password']);
    mysqli_set_charset($conn, "utf8");
    $dim=0;
    if($_POST["dim"]=="on") $dim = 1;
    $name='\''.$_POST["name"].'\'';
    $room='\''.$_POST["room"].'\'';
    $floor='\''.$_POST["floor"].'\'';
    $query = 'UPDATE zense.box_' . $login . ' SET name="'.$name.'",room="'.$room.'", floor="'.$floor.'", dim="'.$dim.'" where id='.$_GET["id"].';';
    mysqli_query($conn, $query);
    mysqli_commit($conn);
    $conn->close();
    header("Location: /");
} else {
   edit($ip, $port, $login, $_GET['id']);
}


?>

</body>

</html>
