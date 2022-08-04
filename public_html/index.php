<?php session_start();
$now = microtime(true);
date_default_timezone_set('America/Chicago');
include('functions/functions.php');
include('../variables.php');
include('includes/header.php');
include('includes/nav.php');
if (isset($_REQUEST['type'])) { $type = $_REQUEST['type']; } else { $type = 'article'; $id = '2'; }
if (isset($_REQUEST['frequent_mod'])) { $frequent_mod = $_REQUEST['frequent_mod']; } else { $frequent_mod = false; }
if (isset($_REQUEST['id'])) { $id = $_REQUEST['id']; }
if ($type == 'text' && $id != 'all') { check_permissions($permission = array(3)); }
$individual_pages = array('semantic','readability','statistical','map','sentence','languagesimilarity');
if (!in_array($type,$individual_pages)) { $data = get_all($type,$id,$db); }
include('includes/page.php');
include('includes/footer.php');
?>

