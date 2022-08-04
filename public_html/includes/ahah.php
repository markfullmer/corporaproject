<?php
require './../functions/functions.php';
require './../../variables.php';
echo select_single_value('progress', '1', 'text_updater', $db);
?>
