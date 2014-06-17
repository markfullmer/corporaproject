<?php

echo '<h2>Recalculate all Word counts</h2>';
include('../../variables/variables.php');
include('../../functions/functions.php');

// empty count for all words
$texts = get_name('all','text',$db);

$inc = 1;
while ($inc < 520)  {
	$row = get_all('text',$inc,$db);
	process_text($row['id'],$db,'text',$row['name'],$row['content'],$row['language'],$row['author'],$row['year'],$row['genre'],'Update');
	$inc++;
	echo 'Updated '.$row['name'].'<br />';
}


?>