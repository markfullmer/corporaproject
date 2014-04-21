<?php 
$db = new PDO('mysql:host=localhost;dbname=DBNAME', 'USER', 'PASSWORD');
$db->query('SET NAMES "utf8"')->execute();