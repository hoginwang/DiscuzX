<?php
require('aes.class.php');
$aes = new AES();
var_dump($aes->encrypt(1 + "|" + 123454354));
$c = $aes->encrypt(1 + "|" + 123454354);
var_dump($aes->decrypt($c));


?>
