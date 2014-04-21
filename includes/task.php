<?php 
include('./../functions/functions.php');
include('./../variables/variables.php');

$language = $_GET['language'];
update_readability_bulk($language,$db);


?>