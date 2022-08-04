<?php
$dbhost = "database";
$dbuser = "lamp";
$dbpass = "lamp";
$dbname = "lamp";

try {
  $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
  // set the PDO error mode to exception
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $db->query('SET NAMES "utf8"')->execute();
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
