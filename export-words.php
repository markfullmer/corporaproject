<?php
header("Content-Type: text/plain");
include('functions/functions.php');
include('variables/variables.php');

  function cleanData(&$str)
  {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

  # filename for download
  $filename = "frequent_words_" . date('Y-m-d') . ".xls";

  header("Content-Disposition: attachment; filename=\"$filename\"");
  header("Content-Type: application/vnd.ms-excel");
$words = array();
$flag = false;
      echo ("Language \t Word \t Count \t Meaning \t Part of Speech 1 \t Part of Speech 2 \t Sample Sentence \r\n");

if (isset($_REQUEST['language'])) { 
	if ($_REQUEST['language'] == 'all') {
		$sql = 'SELECT * FROM word ORDER BY name ASC';
	}
	else {
		$language = $_REQUEST['language']; 
 		$sql = 'SELECT * FROM word WHERE language = "'.$language.'" ORDER BY count DESC';
 	}
}
else {
	$sql = 'SELECT * FROM word ORDER BY name ASC';
} 
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$key = $row['name'];
    	$result[$key]['language'] = select_single_value('language',$row['language'],'name',$db);
    	$result[$key]['word'] = $row['name'];
		$result[$key]['count'] = $row['count'];
		$result[$key]['definition'] = $row['definition'];
		$result[$key]['pos'] = select_single_value('pos',$row['pos'],'name',$db);
		$result[$key]['postwo'] = select_single_value('pos',$row['postwo'],'name',$db);
		$result[$key]['sample_sentence'] = $row['sample_sentence'];
	}

	 
foreach ($result as $key => $value) {
	array_walk($value, 'cleanData');
    echo implode("\t", array_values($value)) . "\r\n";
 }		


		
 
?>