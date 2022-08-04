<?php  
require '../../variables.php';
require '../functions/functions.php';
header("Content-type: text/xml");
$search = new Map($_GET['word']);
echo $search->renderResults();
?>

