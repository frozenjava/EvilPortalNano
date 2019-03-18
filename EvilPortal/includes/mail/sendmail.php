<?php

require 'SMTPMailer.php';
$mail = new SMTPMailer();

$mail->addTo($reciver);

$mail->Subject('Mail message for you');
$mail->Body(
    '<h3>Mail message</h3>
    This is a <b>html</b> message.<br>
    Greetings!'
);

if ($mail->Send()) echo 'Mail sent successfully';
else               echo 'Mail failure';
