<?php

$pin = "1234";

$hash = password_hash($pin, PASSWORD_DEFAULT);

echo $hash;

?>