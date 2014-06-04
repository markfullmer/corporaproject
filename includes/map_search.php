<?php  
include('../variables/variables.php');
include('../functions/functions.php');
header("Content-type: text/xml");
$search = new Map($_GET['word']);
echo $search->renderResults();
?>

