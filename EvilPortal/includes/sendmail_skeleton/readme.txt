To Use Sendmail Function you need to setup smtp settings first.
then Setup sender mail in Myportal.php:

$token = generateRandomString(8); //token (8)is lenght 8 , you can change that !
$sub = "Wifi Access Token"; //Subject of the mail 
$sender = "ENTER SENDER MAIL HERE"; //Sender of the mail
$body = "Your Access Token: $token"; //body of the mail

