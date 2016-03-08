<?php

if (isset($_POST['target'])) {
  header("Location: {$_POST['target']}", true, 302);
  exit();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$destination = "http://". $_SERVER['HTTP_HOST'] . $_SERVER['HTTP_URI'] . "";

?>

<HTML>
  <HEAD>
    <title>Evil Portal</title>
    <script type="text/javascript" src="jquery-2.2.1.min.js"></script>
  </HEAD>

  <BODY>
    <center>
      <h1>Evil Portal</h1>
      <p>This is the default Evil Portal page</p>
      <p style="display: none;" id="pleaseWaitText">You now have internet access. You may close this window!</p>
      <br/>
      <button id="authButton" type="button" onclick="submitAuthorizationRequest()">Authorize</button>

      <form id="goToTarget" method="POST" action="evilportal/index.php">
        <input type="hidden" name="target" value="<?=$destination?>">
      </form>

    </center>

    <script type="text/javascript">
    function submitAuthorizationRequest() {
      console.log("Testing");
      $.post("evilportal/index.php", JSON.stringify({action: "authorize"}), function(results) {
        console.log(results);
        if (results.authorized) {
          $("#pleaseWaitText").show();
          $("#authButton").hide();
          document.getElementById("goToTarget").submit();
        } else {
          console.log(results);
          alert("There was an error authorizing you.");
        }
      });
    }
    </script>

  </BODY>

<HTML>