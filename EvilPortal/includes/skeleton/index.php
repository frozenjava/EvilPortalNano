<?php
$destination = "http://". $_SERVER['HTTP_HOST'] . $_SERVER['HTTP_URI'] . "";
?>

<HTML>
    <HEAD>
        <title>Evil Portal</title>
    </HEAD>

    <BODY>
        <center>
            <h1>Evil Portal</h1>
            <p>This is the default Evil Portal page</p>

            <form method="POST" action="evilportal/index.php">
                <input type="hidden" name="target" value="<?=$destination?>">
                <button type="submit">Authorize</button>
            </form>

        </center>

    </BODY>

</HTML>