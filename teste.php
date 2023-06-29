<?php
ini_set('display_errors', true); error_reporting(E_ALL);
$teste1 =  mail('eliakim.dev@gmail.com', 'My Subject', 'teste eliakim ramos ');
var_dump($teste1);
$teste2 = mail('falecom@eliakimramos.com.br', 'My Subject', 'teste eliakim ramos2 ');
var_dump($teste2);
var_dump(error_get_last());
?>