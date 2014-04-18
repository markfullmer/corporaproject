<?php
if (isset($_REQUEST['submit'])) {
    include('../variables/variables.php');
	if ($_REQUEST['submit'] == 'Save') {
		exec("mysqldump --user=".$dbuser." --password='".$dbpass."' ".$dbname." > saved_db.sql");
		$file = 'save.log';
		$current = file_get_contents($file);
		$current .= date('r')."\n";
		file_put_contents($file, $current);
			header('Location: ../index.php?message=3');
	}
	elseif ($_REQUEST['submit'] == 'Restore') {
		$confirm = strtolower($_REQUEST['confirm']);
		if ($confirm == 'yes') {
			if (isset($_REQUEST['restore'])) {
				if ($_REQUEST['restore'] == 'saved') {
					exec("mysql --user=".$dbuser." --password='".$dbpass."' ".$dbname." < saved_db.sql");
					header('Location: ../index.php?message=4');
exit();
				}
elseif ($_REQUEST['restore'] == 'original') {
					exec("mysql --user=".$dbuser." --password='".$dbpass."' ".$dbname." < original_db.sql");
					header('Location: ../index.php?message=5');
exit();
				}
				else {
				header('Location: ../index.php?message=6');
exit();
				}
			}
		}
		else {
			header('Location: ../index.php?message=6');
exit();
		}
	}
}
else { echo 'No request'; }
?>
