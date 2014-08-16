<?php 
session_start();
if (empty($_SESSION['uid'])) {
	die();
}
include('./../functions/functions.php');
include('./../variables/variables.php');

echo 'helper script';
if ($_GET['type'] == 'wordcount_reset') {
	$sql = 'UPDATE word SET count =""';
	$statement = $db->prepare($sql);
	$statement->execute(array());
}

$total = $_GET['total'];
if (isset($_GET['batch'])) {
	$limit = $_GET['batch'];
}
else {
	$limit = '10';
}
$offset = '0';
$values = array();
$done = false;
// Prepare the task runner
if ($_GET['type'] == 'Export all words') {
	$header = array("Word","Count","Part(s) of Speech","Meaning","Sample Sentence");
	$fp = fopen('export.xls', 'w');
	fputcsv($fp,$header);
	fclose($fp);
}
if ($_GET['type'] == 'readability') {
	$language = $_GET['language'];
	$frequent_words = select_single_value('language',$language,'frequent_words',$db);
	if ($frequent_words !='') {
		$values['frequent_word_array'] = get_frequent_words($language,$db);
		$values['words_constant'] = select_single_value('language',$language,'words_constant',$db);
		$values['sentences_constant'] = select_single_value('language',$language,'sentences_constant',$db);
		$values['text_count'] = count_values('text','language',$language,$db);
	}
}
while (!$done) {
	if ($offset > $total) { $progress = $total; }
	else { $progress = $offset; }
	
	// Update the progress table
	$sql = 'UPDATE progress SET text_updater = :offset WHERE id = :id';
	$q = $db->prepare($sql);
	$q->execute(array(':offset' => $progress,':id' => '1')); 
	$values['limit'] = $limit;
	$values['offset'] = $offset;
	// Execute the task
	task($values);

	// move to the next batch
	if ($offset > $total) { $done = true; }
	    $offset = $offset+$limit;
}

function task($values) {
	global $db;
	if ($_GET['type'] == 'Export all words') {
		$result = select_frequent_words($_GET['language'],$values['offset'],$values['limit'],'count',false,false,$db);
		$pos = get_name('all','pos',$db);
		foreach ($result as $key => $value) {
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
  			$altered[$key]['name'] = $value['name'];
  			$altered[$key]['count'] = $value['count'];
  			$altered[$key]['pos'] = $parts_of_speech;
  			$altered[$key]['definition'] = $value['definition'];
  			$altered[$key]['sample'] = $value['sample_sentence'];
		}
		$fp = fopen('export.xls', 'a+');
		foreach ($altered as $key => $value) {
			array_walk($value, 'cleanData');
			fputcsv($fp, $value);
		}
		fclose($fp);		 
	}
	if ($_GET['type'] == 'words_in_texts') {
		// Get the data
		$sql = 'SELECT id,name,content,language,author,year,genre FROM text LIMIT '.$values['limit'].' OFFSET '.$values['offset'];
   		$statement = $db->prepare($sql);
		$statement->execute(array());
		// Perform the action
    	while ($row = $statement->fetch()) { 
    		process_text($row['id'],$db,'text',$row['name'],$row['content'],$row['language'],$row['author'],$row['year'],$row['genre'],'Update');
    	}
	}
	if ($_GET['type'] == 'sentence') {
		$sql = "SELECT id,content,genre,language FROM text LIMIT ".$values['limit']." OFFSET ".$values['offset'];
		$statement = $db->prepare($sql);
		$statement->execute(array());
		while ($row = $statement->fetch()) { 
			$clean = clean_sentence($row['content'],$row['genre'],$db);
			$sentence_array = explode('.',$clean);
			foreach ($sentence_array as $sentence) {
				if (strlen($sentence) > 50) {
					$sql = 'INSERT INTO sentences (content,language,text) VALUES (:content,:language,:text)';
					$q = $db->prepare($sql);
					$q->execute(array(':content'=>$sentence,':language'=>$row['language'],':text'=>$row['id']));
				}
			}
 		}
	}
	if ($_GET['type'] == 'wordcount_reset') {
		$sql = 'SELECT id,word_list,language FROM text LIMIT '.$values['limit'].' OFFSET '.$values['offset'];
   		$statement = $db->prepare($sql);
		$statement->execute(array());
		// Perform the action
    	while ($row = $statement->fetch()) { 
    		// Update Existing Words
    		$existing = array();
    		$word_list = unserialize($row['word_list']);
    		$words = array_keys($word_list);
			$names = implode("','",$words);
			$comma_separated = "'".$names."'";
			$sql = 'SELECT name,count FROM word WHERE name IN ('.$comma_separated.') AND language = :language';
			$q = $db->prepare($sql);
			$q->execute(array(':language'=>$row['language']));
			while ($vals = $q->fetch()) { 
				$id = $vals['name'];
				$existing[$id] = $vals['count'];
			}
			echo $existing['nga'];
			echo '<br />';
			$sql = "UPDATE word SET count= CASE name ";
			foreach ($word_list as $word => $count) {
				$total = $count+$existing[$word];
    			$sql .= sprintf("WHEN '%s' THEN %s ", $word, $total);
    			if($word==end($words)){
      				$sql .= "END WHERE language = ".$row['language']." AND name IN (".$comma_separated.")";
 				}
 			}

  		}
    	$q = $db->prepare($sql);
		$q->execute();
	}
	if ($_GET['type'] == 'readability') {
		$frequent = 0;
		$total = 0;
		$sql = 'SELECT id,word_list,words_per_sentence FROM text WHERE language ='.$_GET['language'].' LIMIT '.$values['limit'].' OFFSET '.$values['offset'];
   		$statement = $db->prepare($sql);
		$statement->execute(array());
		
		// do the math
    	while ($row = $statement->fetch()) { 
    		$id = $row['id'];
    		$word_array_list = unserialize($row['word_list']);
    		$words_per_sentence = $row['words_per_sentence']; 
			foreach ($word_array_list as $key => $value) {
				if ($key != '') {
					if (in_array($key,$values['frequent_word_array'])) { 
						$frequent = $frequent+$value; 
					}
					$total = $total+$value;
				}
			}
			$percent_frequent_words = $frequent/$total*100;
			$readability[$id] = ($values['sentences_constant']*$words_per_sentence)+($values['words_constant']*(100-$percent_frequent_words)) +0.839; 
	    }
	    
	    // perform the update
	    $ids = array_keys($readability);
    	$id_list = implode("','",$ids);
		$comma_separated = "'".$id_list."'";
		$sql = "UPDATE text SET readability = CASE id ";
		foreach ($readability as $key => $score ) {
    		$sql .= sprintf("WHEN '%s' THEN %s ", $key, $score);
    		if ($key == end($ids)){
      			$sql .= "END WHERE language = ".$_GET['language']." AND id IN (".$comma_separated.")";
  			}
  		}
    }
}

?>