<?php
$destination = "http://". $_SERVER['HTTP_HOST'] . $_SERVER['HTTP_URI'] . "";
?>

<HTML>
    <HEAD>
        <title>Evil Portal</title>
        <script>
            function reloadPage() {
                setTimeout(function() {
                    window.location = "evilportal/index.php";
                }, 100);
            } 
        </script>
    </HEAD>

    <BODY>
        <center>
            <h1>Evil Portal</h1>
            <p>This is the default Evil Portal page</p>

            <form method="POST" action="evilportal/index.php" onsubmit="reloadPage()">
                <input type="hidden" name="target" value="<?=$destination?>">
                <button type="submit">Authorize</button>
            </form>

        </center>

    </BODY>

</HTML>