<?php
header("Content-Type: text/plain");
include('functions/functions.php');
include('variables/variables.php');

$filename = "frequent_words_" . date('Y-m-d') . ".xls";
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.ms-excel");
$words = array();
$flag = false;
echo ("Language \t Word \t Count \t Part(s) of Speech \t Meaning \tSample Sentence \r\n");

$result = select_frequent_words($_POST['language'],0,100000,'count',$_POST['loan'],$_POST['blacklist'],$db);

$languages = get_name('all','language',$db);
$pos = get_name('all','pos',$db);

foreach ($result as $key => $value) {
  $lang = $value['language'];
  if (isset($languages[$lang]['name'])) { 
    $lang_display = $languages[$lang]['name']; 
  }
  else { $lang_display = 'Uncategorized'; }
  $pos_array = array();
  if ($value['pos'] != 0) { 
    $one = $value['pos'];
    $pos_array[] = $pos[$one]['name']; 
  }
  if ($value['postwo'] != 0) { 
    $two = $value['postwo'];
    $pos_array[] = $pos[$two]['name']; 
  }
  $parts_of_speech = join('/ ',$pos_array);

  $altered[$key]['language'] = $lang_display;
  $altered[$key]['name'] = $value['name'];
  $altered[$key]['count'] = $value['count'];
  $altered[$key]['pos'] = $parts_of_speech;
  $altered[$key]['definition'] = $value['definition'];
  $altered[$key]['sample'] = $value['sample_sentence'];
}

foreach ($altered as $key => $value) {
	array_walk($value, 'cleanData');
    echo implode("\t", array_values($value)) . "\r\n";
 }		 
?>