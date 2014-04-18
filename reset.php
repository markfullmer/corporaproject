<?php session_start();
$now = microtime(true); 
include('functions/functions.php');
include('variables/variables.php');
include('includes/header.php');
include('includes/nav.php');


    $toDay = date('d-m-Y');

    $dbhost =   "localhost";
    $dbuser =   "markfull_mer";
    $dbpass =   "mld9Z70p8";
    $dbname =   "markfull_languages";

 //   exec("/../opt/lampp/bin/mysqldump --user=".$dbuser." --password='".$dbpass."' --host=".$dbhost." ".$dbname." > /../home/mark/Desktop/".$toDay."_DB.sql");
// backup
// exec("mysqldump --user=".$dbuser." --password='".$dbpass."' ".$dbname." > ".$toDay."_DB.sql");

// restore
exec("mysql --user=".$dbuser." --password='".$dbpass."' ".$dbname." < ".$toDay."_DB.sql");

include('includes/footer.php'); 
?>
