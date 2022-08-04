<?php

/**
 * @file
 */

session_start();
$now = microtime(TRUE);
date_default_timezone_set('America/Chicago');
require 'functions/functions.php';
require '../variables.php';
require 'includes/header.php';
require 'includes/nav.php';
if (isset($_REQUEST['type'])) {
  $type = $_REQUEST['type'];
}
else {
  $type = 'article';
  $id = '2';
}
if (isset($_REQUEST['frequent_mod'])) {
  $frequent_mod = $_REQUEST['frequent_mod'];
}
else {
  $frequent_mod = FALSE;
}
if (isset($_REQUEST['id'])) {
  $id = $_REQUEST['id'];
}
if ($type == 'text' && $id != 'all') {
  check_permissions($permission = [3]);
}
$individual_pages = ['semantic', 'readability', 'statistical', 'map', 'sentence', 'languagesimilarity'];
if (!in_array($type, $individual_pages)) {
  $data = get_all($type, $id, $db);
}
require 'includes/page.php';
require 'includes/footer.php';
