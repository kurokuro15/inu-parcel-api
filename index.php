<?php
require_once "classes/conection/conection.php";

$conection = new conection();
// $sqlstr = 'SELECT * FROM User;';
print_r($conection->encrypt('1234','validate'));

?>
