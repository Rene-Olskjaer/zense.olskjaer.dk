<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang=da>
<head>
 <title>ZenseHome</title>
<meta name="viewport" content="width=device-width, initial-scale=0.8">

<style>
<?php include 'CSS/main.css'; ?>
</style>
<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
<script>
function attachCheckboxHandlers() {
    var el = document.getElementById('zense');
    var tops = el.getElementsByTagName('input');
    for (var i=0, len=tops.length; i<len; i++) {
        if ( tops[i].type === 'checkbox' ) {
            tops[i].onclick = updateZense;
        }
    }
}

function updateZense(e) {
    $("#progress").hide();
    var form = this.form;
    var val = this.name;
    var options = {};
    options.url = 'https://zense.olskjaer.dk/list.php';
    options.type = 'post';
    options.beforeSend = function () {
      $("#progress").show();
    };
    options.success = function(val) {
      window.location.reload(true);
    };
    if ( this.checked ) {
       options.data = {function2call: 'zenseon',id:val};
    } else
    {
       options.data = {function2call: 'zenseoff',id:val};
    }
    $.ajax(options);
}

</script>

</head>
<body style="background-color:lightgrey;">
<div class=fadeMe id='progress' style='display:none'>
   <div class=container>
       <img class=img src='images/ajax-loader.png' alt='loader'>
   </div>
</div>

<?php

if (isset($_POST['list_x'])) {
    $ip= htmlspecialchars($_COOKIE['IP']);
    $port= htmlspecialchars($_COOKIE['PORT']);
    $login= htmlspecialchars($_COOKIE['BOXID']);
  
    $fp = pfsockopen($ip, $port);
    if ($fp) {
        fputs($fp, '>>Login ' . $login . '<<\r');
        fgets($fp);
        fputs($fp, ">>Sim ?<<\r");
        $sim = fgets($fp);
        $sim = str_replace("Sim ?", "", $sim);
        $sim = str_replace("<<", "", $sim);
        $sim = str_replace(">>", "", $sim);
        $sim = str_replace(" ", "", $sim);
        fputs($fp, ">>Logout<<\r");
        fgets($fp);
        usleep(100000);
        fclose($fp);
    }
    $state = (trim($sim) == 1);
    echo '<form action="settings.php" method="post" class="zense" id="zense">';
    echo '<table style="width:100%">';
    echo '<tr style="background-color:#3EA5C4;">';
    echo '<th colspan=2; style="text-align: left"><a href="/"><img src="images/undo.png" alt="undo" id="undo" width="60" height="60"></a></th>';
    echo '</tr>';
    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">Hjemmesimulering</th>';
    echo  '<th style="text-align: right; width:20%"> <label class="switch"> <input name="homesim"';
    if ($state>0) {
        echo  ' type="checkbox" checked> <span class="slider round"></span></label></th>';
    } else {
        echo  ' type="checkbox" > <span class="slider round"></span></label></th>';
    }

    echo '</tr>';
    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">Sluk alt</th>';
    echo  '<th style="text-align: right; width:20%"> <label class="switch"> <input name="alloff"';
    echo  ' type="checkbox"> <span class="slider round"></span></label></th>';
    echo '</tr>';
    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">Rescan</th>';
    echo  '<th style="text-align: right; width:20%"> <label class="switch"> <input name="rescan"';
    echo  ' type="checkbox" > <span class="slider round"></span></label></th>';
    echo '</tr>';
    echo '</table>';
    echo '</form>';
    echo '<script> attachCheckboxHandlers(); </script>';
}


if (isset($_POST['settings_x'])) {
    $ip= htmlspecialchars($_COOKIE['IP']);
    $port= htmlspecialchars($_COOKIE['PORT']);
    $login= htmlspecialchars($_COOKIE['BOXID']);
    echo '<form settings.php" method="post">';
    echo '<table style="width:100%">';
    echo '<tr style="background-color:#3EA5C4;">';
    echo '<td style="border:none;text-align: left"><a href="/"><img src="images/undo.png" alt="undo" id="undo" width="60" height="60"></a></td>';
    echo '<td style="border:none;text-align: right"><input type="image" src="images/save.png" alt="Submit" name="save" width="60" height="60"></td>';
    echo '</tr>';
    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">IP/Hostname</th>';
    echo  '<th style="text-align: right; width:20%"> <input type="text" name="ip" value='.$ip.' id="inputForm"></th>';

    echo '</tr>';
    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">Port</th>';
    echo  '<th style="text-align: right; width:20%"> <input type="text" name="port" value='.$port.' id="inputForm"></th>';

    echo '</tr>';
    echo '<tr>';
    echo '<th style="text-align: left; font-size:xx-large;">PC-Box Id</th>';
    echo  '<th style="text-align: right; width:20%"> <input type="text" name="login" value='.$login.' id="inputForm"></th>';

    echo '</tr>';
    echo '</table>';
    echo '</form>';
}


if (isset($_POST['save_x'])) {
    setcookie('IP', $_POST['ip'], time() + (10 * 365 * 24 * 60 * 60));
    setcookie('PORT', $_POST['port'], time() + (10 * 365 * 24 * 60 * 60));
    setcookie('BOXID', $_POST['login'], time() + (10 * 365 * 24 * 60 * 60));
    header("Location:/");
}

?>

</body>
</html>


