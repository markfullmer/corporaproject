<?php
function br2nl($text)
{
	$breaks = array("<br />","<br>","<br/>");  
    return str_ireplace($breaks, "\r\n", $text);
}
function clean_sentence($raw,$genre,$db) {
	$raw = nl2br($raw);
	$find = array('/\r/','/\n/','/\t/');
	$replace = array(' ',' ');
	$raw = preg_replace($find, $replace, $raw);
	trim(preg_replace('/\t+/', '', $raw));
	$raw = str_replace('<br /> <br />','<br /> ',$raw); 
	$raw = str_replace('<br />  <br />','<br /> ',$raw); 
	$language = get_name($genre,'genre',$db);
	if (isset($language[$genre])) {
	if (in_array($language[$genre]['name'],array('Poem','Song'))) {  
		$raw = str_replace('.<br />','<br />',$raw);
		$raw = str_replace('<br />','. ',$raw);
		}
	}
	$search = array('&lsquo;', '&rsquo;', '&ldquo;', '&rdquo;', '&mdash;'); 
	$fix = array("'", "'", '"', '"', '-'); 
	$raw = str_replace($search, $fix, $raw);
	$raw = str_replace('<br />', ' ', $raw);
	$raw = strip_tags($raw);
	$raw = preg_replace('/[>][<]/', '> <', $raw);
	$raw = strtolower($raw);
	$raw = str_replace('  ', ' ',$raw);
	$raw = str_replace('  ', ' ',$raw);
	$raw = str_replace('  ', ' ',$raw);
	$raw = trim($raw);
	$raw = str_replace(',',' ',$raw);
	$raw = preg_replace("/[^a-zA-Z\s.?!;]/", "", $raw);
	$raw = preg_replace("/[?!;]/",". ", $raw);
	$raw = trim($raw);
	$raw = str_replace('. .','.',$raw);
	$raw = str_replace('. .','.',$raw);
	$raw = str_replace('..','.',$raw);
	$raw = str_replace('..','.',$raw); 
	return $raw;
}
function check_language_permission($item_language,$db) {
	$language_name = select_single_value('language',$item_language,'name',$db);
    $sql = 'SELECT pid FROM permissions WHERE name ="'.$language_name.'" LIMIT 1';
    $q = $db->prepare($sql);
    $q->execute(array());
    $row = $q->fetch();
    $language_permission = $row['pid'];
    if (isset($_SESSION['permissions'])) {
    	if (in_array($language_permission,$_SESSION['permissions'])) { return true; }
    }
    else { return false; }
}
function check_permissions_single($permission,$db) {
    if (isset($_SESSION['permissions'])) {
    	if (in_array($permission,$_SESSION['permissions'])) { return true; }
    }
    else { return false; }
}
function check_permissions($permission) {
	if (empty($_SESSION['permissions'])) { die('You do not have permission to access this.'); }
	else { 
		$intersect = array_intersect($_SESSION['permissions'],$permission);
		if (empty($intersect)) { die('You do not have permission to access this.'); }
	}
}
function count_values($table,$criteria,$value,$db) {
    $sql = 'SELECT id FROM '.$table.' WHERE '.$criteria.'='.$value; 
    $result = $db->query($sql)->fetchAll();
    return count($result);
}
function delete_basic($type,$id,$db) {
	if ($type == 'language') {
		$name = select_single_value('language',$id,'name',$db);
		$sql = 'DELETE FROM permissions WHERE name = :name LIMIT 1';
		$q = $db->prepare($sql);
		$q->execute(array(':name'=>$name));
	}
	$sql = "DELETE FROM ".$type." WHERE id = :id LIMIT 1";
	$q = $db->prepare($sql);
	$q->execute(array(':id'=>$id));
	header('Location: ./edit.php?type='.$type.'&id=all');
	exit();
}
function delete_text($id,$db) {
	$words = select_single_value('text',$id,'word_list',$db);
	$word_list = unserialize($words);
	$sql = "DELETE FROM text WHERE id = :id LIMIT 1";
	$q = $db->prepare($sql);
	$q->execute(array(':id'=>$id));
	return $word_list;
}
function doflush() {
    echo(str_repeat(' ', 256));
    if (@ob_get_contents()) {
        @ob_end_flush();
    }
    flush();
}
function fix_spelling($db) {
	$spelling = array();
	$sql = 'SELECT * FROM spelling';
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$original = strtolower($row['original']);
    	$spelling[$original] = strtolower($row['revised']);
    }
    foreach ($spelling as $key => $value) {
    	$sql = 'UPDATE word SET standard_spelling=:standard_spelling WHERE name = :name AND language = :language';
		$q = $db->prepare($sql);
		$q->execute(array(':standard_spelling'=>$value,':name'=>$key,':language'=>'24'));
		echo 'updated '.$key.' to '.$value.'<br />';
    }
}
function fix_blacklist($db) {
	$blacklist = array();
	$sql = 'SELECT * FROM blacklist';
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$blacklist[] = strtolower($row['blacklist']);
    }
    foreach ($blacklist as $value) {
    	$sql = 'UPDATE word SET blacklist=:blacklist WHERE name = :name AND language = :language';
		$q = $db->prepare($sql);
		$q->execute(array(':blacklist'=>'1',':name'=>$value,':language'=>'24'));
		echo 'blacklisted '.$value.'<br />';
    }
}

function get_frequent_words($language,$db) {
		$generated = array();
		$manual = array();
		$frequent_generated = select_single_value('language',$language,'frequent_words',$db);
		$frequent_manual = select_single_value('language',$language,'frequent_manual',$db);
		if ($frequent_manual != '') { $manual = unserialize($frequent_manual); }
		if ($frequent_generated != '') { $generated = unserialize($frequent_generated); }
		$frequent = array_merge($generated,$manual);
		$frequent = array_values($frequent);
	return $frequent;
}
function get_all($type,$id,$db) {
	$sql = 'SELECT * FROM '.$type.' WHERE  id = :id LIMIT 1';
   	$statement = $db->prepare($sql);
	$statement->execute(array(':id'=>$id));
	$row = $statement->fetch();
	return $row;
}
function get_all_semantic($id,$db) {
	$sql = "SELECT id,name,english_equivalent,language FROM word WHERE domain ='".$id."'";
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) { 
    	$id = $row['english_equivalent'];
    	$language = $row['language'];
    	$result[$id][$language] = $row['name'];
    }
	if (isset($result)) { return $result; }
}
function get_all_ids($table,$column,$condition,$db) {
	$sql = "SELECT id FROM ".$table." WHERE ".$column." ='".$condition."'";
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) { $result[] = $row['id']; }
	if (isset($result)) { return $result; }
}
function get_name($id,$type,$db) {
	if ($id == 'all') { $sql = "SELECT id,name FROM ".$type; }
	else { $sql = "SELECT id,name FROM ".$type." WHERE id =".$id." LIMIT 1";}
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$key = $row['id'];
		$result[$key]['name'] = $row['name'];
	}
	if (isset($result)) { return $result; }
}
function get_all_user($db) {
   	$statement = $db->prepare("SELECT id,first,last,content,email FROM user WHERE id != '1' LIMIT 1");
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$key = $row['id'];
		$result[$key]['first'] = $row['first'];
		$result[$key]['last'] = $row['last'];
		$result[$key]['email'] = $row['email'];
		$result[$key]['content'] = $row['content'];
	}
	return $result;
}
function get_permissions($db) {
   	$statement = $db->prepare("SELECT * FROM permissions");
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$key = $row['pid'];
		$result[$key]['name'] = $row['name'];
		$result[$key]['url'] = $row['url']; 
	}
	return $result;
}
function get_admin_menu($db) {
   	$statement = $db->prepare("SELECT * FROM permissions WHERE pid != '3' AND url !='language'");
	$statement->execute(array());
	if (isset($statement)) {
    	while ($row = $statement->fetch()) {
			$key = $row['pid'];
			$result[$key]['name'] = $row['name'];
			$result[$key]['url'] = $row['url'];
		}
	}
	return $result;
}
function get_author($id,$db) {
    $statement = $db->prepare("SELECT name FROM user WHERE id = :id");
    $statement->execute(array(':id' => $id));
    $row = $statement->fetch();
    $name = $row['name'];
    return $name;
}
function get_user($id,$db) {
	if ($id == 'add') { $row['name'] = 'Name'; $row['email'] = 'Email'; $row['access'] = ''; $row['password'] = ''; $row['content'] = 'Biography';}
	else {
   	$statement = $db->prepare("SELECT name,email,access,content FROM user WHERE id = :id");
	$statement->execute(array(':id' => $id));
    $row = $statement->fetch();
	}
    return $row;
}
function import_bulk_words($limit,$offset,$db) {
	$possess = get_name('all','pos',$db);
	$posses = array();
	foreach ($possess as $key => $value) {
		$name = $value['name'];
		$posses[$name] = $key;
	}
	$sql = "SELECT * FROM corpus_words WHERE count > '1' LIMIT ".$limit." OFFSET ".$offset;
        $statement = $db->prepare($sql);
		$statement->execute(array());
		if (isset($statement)) {
    		while ($row = $statement->fetch()) {
				$language = '24';
				$definition = $row['meaning'];
				$name = $row['word'];
				if ($row['posone'] != '') {
					$pos1 = $row['posone'];
					$pos = $posses[$pos1];
				}
				else { $pos = '0'; }
				if ($row['postwo'] != '') {	
					$pos2 = $row['postwo'];	
					$postwo = $posses[$pos2];
				}
				else { $postwo = '0'; }
				$sample_sentence = $row['sentence'];
				$english_equivalent = $row['englishword'];
				$domain = '0';
				$blacklist = $row['blacklist'];	
				$englishword = $row['english'];	
				/*
			if (in_array($name,$existential)) {
				$sql = 'UPDATE word SET definition=:definition,pos=:pos,postwo=:postwo,sample_sentence=:sample_sentence,blacklist=:blacklist,englishword=:englishword WHERE name = :name AND language = :language';
				$q = $db->prepare($sql);
				$q->execute(array(':definition'=>$definition,':pos'=>$pos,':postwo'=>$postwo,':sample_sentence'=>$sample_sentence,':blacklist'=>$blacklist,':englishword'=>$englishword,':name'=>$name,':language'=>$language));
			}
			else {
				*/
				insert_word($name,$language,$definition,$pos,$postwo,$sample_sentence,$english_equivalent,$domain,$blacklist,$englishword,$db);
			// }
			}
		}	
}
function import_bulk_text($limit,$offset,$db) {
        $total = 100;
        $current = 0;
        // TEXT CLEANER
  		$find[] = "\\";  // left side double smart quote
  		$replace[] = '';

        $sql = "SELECT name,content,author,genre,language FROM text_importer LIMIT ".$limit." OFFSET ".$offset;
        $statement = $db->prepare($sql);
		$statement->execute(array());
		if (isset($statement)) {
    		while ($row = $statement->fetch()) {
			$id = 0;
			$type = 'text';
			$name = $row['name'];
			$content = str_replace($find,$replace,$row['content']);
			$author = $row['author'];
			$language = $row['language'];
			$year = '';
			$genrename = $row['genre'];
			$genre_grab = get_all_ids('genre','name',$genrename,$db);
			if (isset($genre_grab[0])) { $genre = $genre_grab[0]; }
			else { $genre = 0; }
			$action = 'Add';
			process_text($id,$db,$type,$name,$content,$language,$author,$year,$genre,$action);
			}
        }
    echo 'done!';
}
function import_semantic($db) {
    $inc = 0;
	$words = array();
	$existing = get_name('all','word',$db);
	foreach ($existing as $key => $value) {
		$existential[] = $value['name'];
	}
	$sql = 'SELECT * FROM semantic';
	$statement = $db->prepare($sql);
	$statement->execute(array());
	while ($row = $statement->fetch()) {
    $title = strtolower($row['title']);
    $title = ucwords($title);
    $sql = 'SELECT id FROM domain WHERE name = :name LIMIT 1';
	$q = $db->prepare($sql);
	$q->execute(array(':name'=>$title));
	$set = $q->fetch();
	$domain =  $set['id'];
	$findme   = '-';
	$pos = false;
	$pos = strpos($row['kana'], $findme);
	if ($pos === 0) { 
		$row['kana'] = substr_replace( $row['kana'], '', $pos, 1 );
 	}
 	$row['kana'] = trim($row['kana']);
 	$pos = false;
	$pos = strpos($row['waray'], $findme);
	if ($pos === 0) { 
		$row['waray'] = substr_replace( $row['waray'], '', $pos, 1 );
 	}
 	$row['waray'] = trim($row['waray']);
 	$pos = false;
	$pos = strpos($row['inabaknon'], $findme);
	if ($pos === 0) { 
		$row['inabaknon'] = substr_replace( $row['inabaknon'], '', $pos, 1 );
 	}
 	$row['inabaknon'] = trim($row['inabaknon']);
 	$pos = false;
	$pos = strpos($row['english'], $findme);
	if ($pos === 0) { 
		$row['english'] = substr_replace( $row['english'], '', $pos, 1 );
 	}
 	$row['english'] = trim($row['english']);
 	$pos = false;
	$pos = strpos($row['filipino'], $findme);
	if ($pos === 0) { 
		$row['filipino'] = substr_replace( $row['filipino'], '', $pos, 1 );
 	}
 	$row['filipino'] = trim($row['filipino']);
    if ($row['waray'] != '' && $row['waray'] != '-') {
        $words[$inc]['word'] = strtolower($row['waray']);
        $words[$inc]['english'] = $row['english'];
        $words[$inc]['domain'] = $domain;
        $words[$inc]['language'] = '24';
        $inc++;
    }
    if ($row['kana'] != '' && $row['kana'] != '-') {
        $words[$inc]['word'] = strtolower($row['kana']);
        $words[$inc]['english'] = $row['english'];
        $words[$inc]['domain'] = $domain;
        $words[$inc]['language'] = '27';
        $inc++;
    }
    if ($row['filipino'] != '' && $row['filipino'] != '-') {
        $words[$inc]['word'] = strtolower($row['filipino']);
        $words[$inc]['english'] = $row['english'];
        $words[$inc]['domain'] = $domain;
        $words[$inc]['language'] = '26';
        $inc++;
    }
        if ($row['inabaknon'] != '' && $row['inabaknon'] != '-') {
        $words[$inc]['word'] = strtolower($row['inabaknon']);
        $words[$inc]['english'] = $row['english'];
        $words[$inc]['domain'] = $domain;
        $words[$inc]['language'] = '28';
        $inc++;
    }
     if ($row['english'] != '' && $row['english'] != '-') {
        $words[$inc]['word'] = strtolower($row['english']);
        $words[$inc]['english'] = $row['english'];
        $words[$inc]['domain'] = $domain;
        $words[$inc]['language'] = '25';
        $inc++;
    }
} 	// end loading data

	foreach ($words as $key => $value) {
		if (($value['language'] == '24') && (in_array($value['word'],$existential))) {
			$sql = 'UPDATE word SET domain=:domain,english_equivalent=:englishword WHERE name = :name AND language ="24"';
			$q = $db->prepare($sql);
			$q->execute(array(':domain'=>$value['domain'],':englishword'=>$value['english'],':name'=>$value['word']));
			// echo 'updated<br />';
		}
		else {
    		$sql = 'INSERT INTO word (name,language,english_equivalent,domain) VALUES (:name,:language,:english_equivalent,:domain)';
			$q = $db->prepare($sql);
			$q->execute(array(':name'=>$value['word'],':language'=>$value['language'],':domain'=>$value['domain'],':english_equivalent'=>$value['english']));
			// echo 'inserted<br />';
		}
	}
}
function insert_article($name,$content,$db) {
	$sql = 'INSERT INTO article (name,content) VALUES (:name,:content)';
	$q = $db->prepare($sql);
	$q->execute(array(':name'=>$name,':content'=>$content));
	$sql = 'SELECT id FROM article ORDER BY id DESC LIMIT 1';
	$q = $db->prepare($sql);
	$q->execute(array());
	$row = $q->fetch();
	return $row['id'];
}
function insert_basic($type,$content,$db) {
	$sql = 'INSERT INTO '.$type.' (name) VALUES (:name)';
	$q = $db->prepare($sql);
	$q->execute(array(':name'=>$content));
	if ($type == 'language') {
		$sql = 'INSERT INTO permissions (name,url) VALUES (:name,:url)';
		$q = $db->prepare($sql);
		$q->execute(array(':name'=>$content,':url'=>'language'));
	}
	header('Location: ./edit.php?type='.$type.'&id=all');
	exit();
}
function insert_word($name,$language,$definition,$pos,$postwo,$sample_sentence,$english_equivalent,$domain,$blacklist,$englishword,$db) {
	$sql = 'INSERT INTO word (name,language,definition,pos,postwo,sample_sentence,english_equivalent,domain,blacklist,englishword) VALUES (:name,:language,:definition,:pos,:postwo,:sample_sentence,:english_equivalent,:domain,:blacklist,:englishword)';
	$q = $db->prepare($sql);
	$q->execute(array(':name'=>$name,':language'=>$language,':definition'=>$definition,':pos'=>$pos,':postwo'=>$postwo,':sample_sentence'=>$sample_sentence,':english_equivalent'=>$english_equivalent,':domain'=>$domain,':blacklist'=>$blacklist,':englishword'=>$englishword));
}

function print_grade_levels($readability,$language,$db) {
	$grades = array();
	if (isset($language)) {
		if ($language == 'all') { $language =  ''; }
		else {$language = 'WHERE language = '.$language; }
	}
	else { $languag = ''; }
	$sql = 'SELECT readability FROM text '.$language; 
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$grades[] = $row['readability'];
	}
	$grade_sort = array_count_values($grades);
	ksort($grade_sort);
	echo '<select name="readability" />';
    echo '<option value="">Grade Level (all)</option>';
	foreach ($grade_sort as $grade => $count) {
		if ($grade != '0') {
			echo '<option value="'.$grade.'" ';
			if ($grade == $readability) { echo 'selected="selected"'; }
			echo '>Grade '.$grade.' ('.$count.')</option>';
		}
	}
    echo '</select>';
}
function print_order_filter($order) {
	echo '<select name="order" />';
    echo '<option value="name" ';
    if ($order == 'name') { echo 'selected="selected"';}
    echo '>Order By</option>';
    echo '<option value="name">Title (A-Z)</option>';
    echo '<option value="namez" ';
    if ($order == 'namez') { echo 'selected="selected"';}
    echo '>Title (Z-A)</option>';
    echo '<option value="wordsa" ';
    if ($order == 'wordsa') { echo 'selected="selected"';}
    echo '>Word count (high-low)</option>';
    echo '<option value="wordsz" ';
    if ($order == 'wordsz') { echo 'selected="selected"';}
    echo '>Word count (low-high)</option>';
    echo '</select>';
}

function term_dropdown($table,$item,$db) {
	$terms = get_name('all',$table,$db); 
	asort($terms);
    echo '<select name="'.$table.'"><option value="all">'.ucwords($table).'</option>';
    foreach ($terms as $key => $value) { 
        echo '<option value="'.$key.'"';
        if ($item == $key) { echo 'selected="selected"'; }
        echo '>'.$value['name'].'</option>'; 
    }
	echo '<option value="0"';
    if ($item == '0') { echo 'selected="selected"'; }
    echo '>Uncategorized</option>';
    echo '</select>';
}
function multiterm_dropdown($table,$item,$db) {
	$terms = get_name('all',$table,$db); 
    echo '<select name="'.$table.'[]"><option value="all">'.ucwords($table).'</option>';
    foreach ($terms as $key => $value) { 
        echo '<option value="'.$key.'"';
        if ($item == $key) { echo 'selected="selected"'; }
        echo '>'.$value['name'].'</option>'; 
    }
	echo '<option value="0"';
    if ($item == '0') { echo 'selected="selected"'; }
    echo '>Uncategorized</option>';
    echo '</select>';
}
function dropdown_limited($table,$id,$db) {
	$terms = get_name('all',$table,$db); 
    echo '<select name="'.$table.'" required="required"><option value="">'.ucwords($table).'</option>';
    foreach ($terms as $key => $value) { 
    	if (check_language_permission($key,$db)) {
        	echo '<option value="'.$key.'"';
        	if ($id == $key) { echo 'selected="selected"'; }
        	echo '>'.$value['name'].'</option>'; 
        }
    }
	echo '<option value="0"';
    if ($id == '0') { echo 'selected="selected"'; }
    echo '>Uncategorized</option>';
    echo '</select>';
}

function process_text($id,$db,$type,$name,$content,$language,$author,$year,$genre,$action) {
	$clean = clean_sentence($content,$genre,$db);
	$sentence_array = explode('.',$clean);
	$sentence_count = count($sentence_array);
	if ($sentence_count == 0) { $sentence_count = '1';}
	$word_count = str_word_count($clean);
	$words_per_sentence = number_format($word_count/$sentence_count,2);
	$word_array_list = word_array($clean);
	$wordlist = serialize($word_array_list);
	if ($action == 'Add') {
		$sql = 'INSERT INTO '.$type.' (name,content,language,author,year,genre,sentence_count,word_list,word_count,words_per_sentence) VALUES (:name,:content,:language,:author,:year,:genre,:sentence_count,:word_list,:word_count,:words_per_sentence)';
		$q = $db->prepare($sql);
		$q->execute(array(':name'=>$name,':content'=>$content,':language'=>$language,':author'=>$author,':year'=>$year,':genre'=>$genre,':sentence_count'=>$sentence_count,':word_list'=>$wordlist,':word_count'=>$word_count,':words_per_sentence'=>$words_per_sentence));
		$sql = 'SELECT id FROM text ORDER BY id DESC LIMIT 1';
		$q = $db->prepare($sql);
		$q->execute(array());
		$row = $q->fetch();
		$id = $row['id'];
		$word_array_mod = $word_array_list;
		}
	elseif ($action == 'Update') {
		$old_word_list = select_single_value('text',$id,'word_list',$db);
		$old_word_count = select_single_value('text',$id,'word_count',$db);
		$old_word_array = unserialize($old_word_list);
		if (empty($old_word_array)) {$old_word_array = array(); }
		$union = $old_word_array+$word_array_list;
		$new_words = array_keys($union);
		foreach ($new_words as $word) {	
			$word = strtolower($word);
			if (empty($word_array_list[$word])) { $word_array_list[$word] = 0;}
			if (empty($old_word_array[$word])) { $old_word_array[$word] = 0;}
			$word_array_mod[$word] = $word_array_list[$word]-$old_word_array[$word]; 
		}
		$sql = 'UPDATE text SET name=:name,content=:content,language=:language,genre=:genre,author=:author,year=:year,sentence_count=:sentence_count,word_list=:word_list,word_count=:word_count,words_per_sentence=:words_per_sentence WHERE id = :id';
		$q = $db->prepare($sql);
		$q->execute(array(':name' => $name,':content' => $content,':language' => $language,':genre' => $genre,':author' => $author,':year'=>$year,':sentence_count' => $sentence_count,':word_list' => $wordlist,':word_count' => $word_count,':words_per_sentence' => $words_per_sentence,':id' => $id));
		$word_count = $word_count-$old_word_count;
	}
	process_words($word_array_mod,$sentence_array,$language,$db);
	update_total_words($word_count,$language,$db);
	update_distinct_words($language,$db);
	update_frequent_words($language,$db);
	update_readability($language,$id,$db);
}
function process_words($word_array_mod,$sentence_array,$language,$db) {
	$blank = '';
	$existing_words = array();
	$existing = array();
	unset($word_array_mod[$blank]);
	$words = array_keys($word_array_mod);
	$names = implode("','",$words);
	$comma_separated = "'".$names."'";
	// Get words from DB only that are in the text (efficiency)
	$sql = "SELECT name,count FROM word WHERE name IN (".$comma_separated.") AND language=".$language; 
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$name = strtolower($row['name']);
		$existing[$name] = $row['count'];
	}
	// Remove blanks
	unset($existing[$blank]);
	// By definition, the above query grabs words that need to be updated, since they exist in the text & DB
	$existing_words = array_keys($existing);
	// Subtract the words in the DB from the words in the text to get new words
	$new_words = array_diff($words,$existing_words);
	$values = array();
	$count = count($sentence_array);
	$sentences_spaced = array();
	foreach ($sentence_array as $key => $sentence) {
		$sentences_spaced[$key] = ' '.$sentence.' ';
	}
	// Add new words to the DB using a batched query (efficiency)
	$sql = 'INSERT INTO word (name,count,language,sample_sentence) VALUES ';
	foreach ($new_words as $word) {	
		// Loop through sentences to find a sample for each new word
		$inc = 0;
		$sentence = '';
		$found = 0;
		// Exits the while loop if a sentence has the word or (failsafe) if the array has been exhausted
		while ($found != '1' && $inc < $count) {
			$spaced_word = ' '.$word.' ';
			$pos = strpos($sentences_spaced[$inc],$spaced_word);
			if ($pos !== false) { 
				$sentence = $sentence_array[$inc]; 
				$found = 1;
			}
			$inc++;
		}
		if($word == end($new_words)){ $sql .= "(?,?,?,?)"; }
		else { $sql .= "(?,?,?,?), "; }
		$values[] = $word;
		$values[] = $word_array_mod[$word]; 
		$values[] = $language;
		$values[] = $sentence;
	}
	if (isset($values[2])) {$q = $db->prepare($sql);
	$q->execute($values); }
	
	// Update Existing Words
	$names = implode("','",$existing_words);
	$comma_separated = "'".$names."'";
	$sql = "UPDATE word SET count= CASE name ";
	foreach ($existing_words as $word ) {
		$count = $existing[$word]+$word_array_mod[$word];
    $sql .= sprintf("WHEN '%s' THEN %s ", $word, $count);
    if($word==end(array_keys($existing))){
      $sql .= "END WHERE language = ".$language." AND name IN (".$comma_separated.")";
  		}
  	}
    $q = $db->prepare($sql);
	$q->execute();
    /*
    The Old Way of Doing It
	foreach ($existing_words as $word) {
		$count = $existing[$word]+$word_array_mod[$word];
		$sql = 'UPDATE word SET count = :count WHERE name = :name AND language = :language';
		$q = $db->prepare($sql);
		$q->execute(array(':count' => $count,':name' => $word,':language'=>$language));
	}
	*/
	$new = count($new_words);
	$updated = count($existing_words);
	// echo $new.' new words were added. '.$updated.' words were updated';
}
function progressbar($current, $total,$now) {
    echo "<div";

    echo "><span style='position:absolute;z-index:1;background:#FFF;'>" . round($current / $total * 100) . "% Processed ".$current." out of ".$total."</span>";
    echo '<progress value="'.$current.'" max="'.$total.'"" style="position:absolute;margin-top:20px;background:#FFF;"></progress></div>';
    	echo '<div style="position:absolute;margin-top:50px;z-index:1;background:#FFF;">
    	<a href="./edit.php?type=language&id=all&message=2">Return to language page</a>';
    	echo '<br />Memory usage: ';
    	var_dump(memory_get_usage());
    	echo '<br />Peak usage: ';
		var_dump(memory_get_peak_usage());
		echo '<br />Time elapsed: ';
		echo microtime(true) - $now;
	    echo '</div>'; 
    doflush();
}

function highlight_frequent($type,$text_id,$frequent_words,$text,$db) {
	$output = '';
	$clean = clean_sentence($text,'6',$db);
	$no_period = preg_replace("/[.]/"," ", $clean);
	$stripped = explode(' ',$no_period);
	foreach ($stripped as $key => $value) {
		if (strlen($value) > '25') { unset($stripped[$key]); }
	}
	foreach ($stripped as $word) {
		if (!in_array($word,$frequent_words)) {
			if ($type == 'normal') { 
				$output .= '<span class="highlight">'.$word.'</span> ';
			}
			elseif ($type == 'edit') {
				$output .= '<span class="highlight"><a href="save.php?type=frequent&word='.$word.'&referrer='.$text_id.'&submit=Add">'.$word.'</a></span> ';
			}
		}
		else {
			$output .= $word.' ';
		}
	}	
	return $output;
}

function readability_form($db) {
	if (isset($_POST['submit'])) {
		$frequent_data = select_single_value('language',$_POST['language'],'frequent_words',$db);
		if (empty($frequent_data)) {
			echo '<p>The language you selected does not yet have a list of frequent words. Therefore, the <a href="./index.php?type=article&id=1">customized readability score</a> cannot be calculated. However, below is the <a href="http://en.wikipedia.org/wiki/Flesch%E2%80%93Kincaid_readability_tests">Flesch-Kincaid Reading</a>, which calculates text comprehension on sentence length and syllable length:</p><br />';
			echo '<div class="textbox" style="width:400px;">Flesch-Kincaid Reading Ease: <b>';
			include ('text-statistics/TextStatistics.php');
			$statistics = new TextStatistics;
			echo $statistics->flesch_kincaid_reading_ease($_POST['text']);
			echo '</b><br />(scale is 0-100, from easiest to most difficult)</div>';
			echo '<p>Note: Since Filipino languages are agglutinative, words often contain more syllables than Germanic- or Latin- based languages. The Flesch-Kincaid Reading Ease rates texts based on syllable length, so a text in a Filipino language will be calculated to have a higher grade level than its actual difficulty. However, the score can still be used to compare Filipino texts to each other.</p>';
			echo '<p><a href="./index.php?type=readability"><button>Find the grade level for another text</button></a></p>';
		}
		else {
			echo '<span style="float:right;"><a href="./index.php?type=readability"><button>Find the grade level for another text</button></a></span>';
			$readability = readability_calculator($_POST['language'],$_POST['text'],$_POST['genre'],$db);
			$difficult = 100-number_format($readability['percent_frequent_words'],0);
			echo '<div class="textbox" style="width:300px;">This text is calculated to be at';
			echo '<h3>Grade '.number_format($readability['score'],1).' reading level</h3></div>';
			echo '<br />This calculation was based on the following factors: <br />';
			echo '<ul><li>The text contains <b>'.$readability['word_count'].'</b> words';
			echo '<li>It has an average of <b>'.number_format($readability['words_per_sentence'],0).'</b> words per sentence</li>';		
			echo '<li><b>'.$difficult.' percent</b> of words do not occur frequently in the corpus (highlighted below)</li>';
			echo '<br /><div class="textbox">'.highlight_frequent('normal',0,$readability['frequent_words'],$_POST['text'],$db).'</div>';
		}
	}
	else {
		echo '<form action="./index.php?type=readability" method="post">
			Choose the language of the text:';
			term_dropdown('language','none',$db);
			echo '<br />Choose the genre (poem readability treats line breaks as sentences)';
			term_dropdown('genre','none',$db);
			echo '<br />Paste your text below<br />
			<textarea name="text" style="width:100%;height:400px;max-width:800px;"></textarea><br />
			<input type="submit" name="submit" value="Calculate Grade Level" />
			</form>';
	}
}

function parse_text($text,$genre,$db) {
	$clean = clean_sentence($text,$genre,$db);
	$sentence_array = explode('.',$clean);
	$sentence_count = count($sentence_array);
	if ($sentence_count == 0) { $sentence_count = '1';}
	$word_count = str_word_count($clean);
	$words_per_sentence = number_format($word_count/$sentence_count,2);
	$word_array_list = word_array($clean);
	$results = array();
	$results['word_array'] = $word_array_list;
	$results['words_per_sentence'] = $words_per_sentence;
	$results['word_count'] = $word_count;
	return $results;
}
function readability_calculator($language,$text,$genre,$db) {
	$frequent = 0;
	$total = 0;
	$frequent_words = select_single_value('language',$language,'frequent_words',$db);
	if ($frequent_words != '') {
		$frequent_word_array = get_frequent_words($language,$db);
		$words_constant = select_single_value('language',$language,'words_constant',$db);
		$sentences_constant = select_single_value('language',$language,'sentences_constant',$db);
		$parsed_text = parse_text($text,$genre,$db);
		$word_array_list = $parsed_text['word_array']; 
		$words_per_sentence = $parsed_text['words_per_sentence']; 
		foreach ($word_array_list as $key => $value) {
			if (in_array($key,$frequent_word_array)) { $frequent = $frequent+$value; }
			$total = $total+$value;
		}
		$percent_frequent_words = $frequent/$total*100;
		$readability['score'] = ($sentences_constant*$words_per_sentence)+($words_constant*(100-$percent_frequent_words)) +0.839; 
		$readability['percent_frequent_words'] = $percent_frequent_words;
		$readability['words_per_sentence'] = $words_per_sentence;
		$readability['word_count'] = $parsed_text['word_count'];
		$readability['frequent_words'] = $frequent_word_array;
		return $readability;
	}
	else {
	 	return 'The language you selected does not have enough texts in the database to calculate the grade level of this text.<br />';
	}	
}

function select_frequent_words($language,$offset,$limit,$order,$english_loan,$blacklist,$db) {
	$language_condition = 'language IN';
	if ($language == '0') { $language_condition = 'language NOT IN'; }
	if ($language == 'all' || $language == '0') { 
		$language_array = get_name('all','language',$db);
		$keys = array_keys($language_array);
		$language_ids = join(',',$keys);
		if ($language == 'all') { $language_ids .=',0'; }
	}
	else { $language_ids = $language; }
	if ($english_loan == 'no') {
		$eng_filter = "AND englishword <> '1'";
	}
	else { $eng_filter = "AND englishword = '1'";}
	if ($blacklist == 'no') {
		$black_filter = "AND blacklist <> '1'";
	}
	else { $black_filter = "AND blacklist = '1'";}
	$sql = "SELECT * FROM word WHERE ".$language_condition." (".$language_ids.") ".$black_filter." ".$eng_filter." ORDER BY ".$order." DESC LIMIT ".$limit." OFFSET ".$offset;
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while ($row = $statement->fetch()) {
    	$key = $row['id'];
    	$result[$key]['id'] = $row['id'];
    	$result[$key]['name'] = $row['name'];
		$result[$key]['count'] = $row['count'];
		$result[$key]['language'] = $row['language'];
		$result[$key]['pos'] = $row['pos'];
		$result[$key]['postwo'] = $row['postwo'];
		$result[$key]['definition'] = $row['definition'];
		$result[$key]['sample_sentence'] = $row['sample_sentence'];
		$result[$key]['english_equivalent'] = $row['english_equivalent'];
		$result[$key]['standard_spelling'] = $row['standard_spelling'];
	}
	if (isset($result)) { return $result; }
	else { echo 'No hits match your criteria.'; }
}
function select_texts($language,$genre,$offset,$limit,$order,$readability,$filter,$db) {
	$language_condition = 'language IN';
	$genre_condition = 'genre IN';
	if ($order == 'name') { $order = 'name ASC'; }
	elseif ($order == 'namez') { $order = 'name DESC'; }
	elseif ($order == 'wordsa') { $order = 'word_count DESC'; }
	elseif ($order == 'wordsz') { $order = 'word_count ASC'; }
	else {$order == 'name ASC'; }
	if ($readability != '') {$readability = ' AND readability='.$readability.' '; }
	if ($language == '0') { $language_condition = 'language NOT IN'; }
	if ($genre == '0') { $genre_condition = 'genre NOT IN'; }
	if ($language == 'all' || $language == '0') { 
		$language_array = get_name('all','language',$db);
		$keys = array_keys($language_array);
		$language_ids = join(',',$keys); 
		if ($language == 'all') { $language_ids .=',0'; }
	}
	else { $language_ids = $language; }
	if ($genre == 'all' || $genre == '0') { 
		$genre_array = get_name('all','genre',$db);
		$keys = array_keys($genre_array);
		$genre_ids = join(',',$keys); 
		if ($genre == 'all') { $genre_ids .=',0'; }
	}
	else { $genre_ids = $genre; }
	$sql = "SELECT id,name,language,genre,author,word_count,readability FROM text WHERE ".$language_condition." (".$language_ids.") AND ".$genre_condition." (".$genre_ids.")".$readability." ORDER BY ".$order." LIMIT 10000 OFFSET ".$offset;
	$inc = '0';
   	$statement = $db->prepare($sql);
	$statement->execute(array());
    while (($row = $statement->fetch())) {
    	if ($inc <= $limit) {
    		$key = $row['name'];
    		$result[$key]['id'] = $row['id'];
			$result[$key]['genre'] = $row['genre'];
			$result[$key]['author'] = $row['author'];
			$result[$key]['word_count'] = $row['word_count'];
			$result[$key]['readability'] = $row['readability'];
			$result[$key]['language'] = $row['language'];
		}
		$inc++;
	}

	if (isset($result)) { 
		$result['count'] = count($result);
		$result['total'] = $inc;
		return $result; 
	}
	else { echo 'No hits match your criteria.'; }
}
function search($word,$language,$db) {
	$results = select_single_value('meta','8','content',$db); // get number of search results allowed
	$word = strtolower($word);
	$inc = 0;
	$exact = array();
	if ($language == 'all') { $sql = 'SELECT * FROM word WHERE name ="'.$word.'"'; }
	else { $sql = 'SELECT * FROM word WHERE name ="'.$word.'" AND language ='.$language; }
	$q = $db->prepare($sql);
	$q->execute(array());
	while ($row = $q->fetch()) {
		$inc++;
		$exact[$inc]['word'] = $row['name'];
		$exact[$inc]['language'] = $row['language'];
		$exact[$inc]['id'] = $row['id'];
		$exact[$inc]['definition'] = $row['definition'];
		$exact[$inc]['pos'] = $row['pos'];
		$exact[$inc]['postwo'] = $row['postwo'];
		$exact[$inc]['sample_sentence'] = $row['sample_sentence'];
		$exact[$inc]['english_equivalent'] = $row['english_equivalent'];
	}
	if ($inc < '1') { // No results; check if there are results in the "English Equivalent column"
		if ($language == 'all') { $sql = 'SELECT * FROM word WHERE english_equivalent ="'.$word.'"'; }
		else { $sql = 'SELECT * FROM word WHERE english_equivalent ="'.$word.'" AND language ='.$language; }
		$q = $db->prepare($sql);
		$q->execute(array());
		while ($row = $q->fetch()) {
			$inc++;
			$exact[$inc]['word'] = $row['name'];
			$exact[$inc]['language'] = $row['language'];
			$exact[$inc]['id'] = $row['id'];
			$exact[$inc]['definition'] = $row['definition'];
			$exact[$inc]['pos'] = $row['pos'];
			$exact[$inc]['postwo'] = $row['postwo'];
			$exact[$inc]['sample_sentence'] = $row['sample_sentence'];
			$exact[$inc]['english_equivalent'] = $row['english_equivalent'];
		}
	}
	if ($inc < '1') { // Check for nonstandard spellings
		$standard = '';
		$sql = 'SELECT revised FROM spelling WHERE original ="'.$word.'"'; 
		$q = $db->prepare($sql);
		$q->execute(array());
		while ($row = $q->fetch()) {
			$standard = $row['revised'];
		}
		$sql = 'SELECT * FROM word WHERE name ="'.$standard.'"'; 
		$q = $db->prepare($sql);
		$q->execute(array());
		while ($row = $q->fetch()) {
			$inc++;
			$exact[$inc]['word'] = $row['name'];
			$exact[$inc]['language'] = $row['language'];
			$exact[$inc]['id'] = $row['id'];
			$exact[$inc]['definition'] = $row['definition'];
			$exact[$inc]['pos'] = $row['pos'];
			$exact[$inc]['postwo'] = $row['postwo'];
			$exact[$inc]['sample_sentence'] = $row['sample_sentence'];
			$exact[$inc]['english_equivalent'] = $row['english_equivalent'];
		}
	}
	if ($inc < '1') { // No results
		$outcome = 0;
		if ($language != 'all') {
			echo '<h3>Zero results found in the selected language.</h3>';
		}
		else {
			echo '<h3>Zero results found in the dictionary.</h3>';
		}
	}
	else { // There are results
		if (isset($exact[2]) && ($exact[1]['language'] != $exact[2]['language'])) {
			echo 'The word <i>'.$word.'</i> is listed in multiple languages:<br />'; 
			$existing_language = array();
			foreach ($exact as $hit) {	
				$la = $hit['language'];
				if (!in_array($la,$existing_language)) {
					$this_language = get_name($la,'language',$db);
					echo '<a href="?word_search='.$hit['word'].'&language='.$la.'">'.$hit['word'].' (';
					if (isset($this_language[$la]['name'])) {
						echo $this_language[$la]['name'];
					}
					else {
						echo 'uncategorized';
					}
					echo ')</a><br />';
					$existing_language[] = $la;
				}
			}
		 	echo '<br />';
		}
		else {
			// query for alternate spellings
			$sql = 'SELECT original FROM spelling WHERE revised ="'.$exact[1]['word'].'"'; 
			$q = $db->prepare($sql);
			$q->execute(array());
			while ($row = $q->fetch()) {
				$alternate[] = $row['original'];
			}
			$sql = 'SELECT revised FROM spelling WHERE original ="'.$word.'"'; 
			$q = $db->prepare($sql);
			$q->execute(array());
			while ($row = $q->fetch()) {
				$alternate[] = $row['revised'];
			}
			// print actual word
			echo '<h3>'.$exact[1]['word'].' (';
			$la = $exact[1]['language'];
			$this_language = get_name($la,'language',$db);
			echo $this_language[$la]['name'];
			echo ')';
			// give edit button for authorized users
			if (check_language_permission($la,$db)) {
				echo ' <a href="edit.php?type=word&id='.$exact[1]['id'].'">edit</a>';
			}
			echo '</h3>';
			// print alternate spellings, if any
			if (isset($alternate)) {
				$alternate = array_unique($alternate); 
				$end = end($alternate);
				echo ' (alternate: ';
				foreach ($alternate as $result) {
					if ($result != $end) {
						echo $result.', ';
					}
					else {
						echo $result;
					}
				}
				echo ')<br/>';
			}
			$outcome = $exact[1]['id'];
			if ($exact[1]['pos'] != '0') { 
				echo '<b>Part of speech</b>: ';
				$pos = select_single_value('pos',$exact[1]['pos'],'name',$db);
				echo $pos;
			}
			if ($exact[1]['postwo'] != '0') { 
				$postwo = select_single_value('pos',$exact[1]['postwo'],'name',$db);
				echo ', '.$postwo; 
			}
			if ($exact[1]['definition'] != '') { echo '<br /><b>Meaning: </b>'.$exact[1]['definition'].'<br />'; }
			elseif($exact[1]['english_equivalent'] != '' && $exact[1]['english_equivalent'] != '0' && $exact[1]['language'] != '25') { echo '<br /><b>English Equivalent: </b>'.$exact[1]['english_equivalent'].'<br />'; }
			// Provide language equivalents
			if ($exact[1]['english_equivalent'] != '') { 
				$sql = 'SELECT id,name,language FROM word WHERE english_equivalent =:english_equivalent AND language != :language'; 
				$q = $db->prepare($sql);
				$q->execute(array(':english_equivalent'=>$exact[1]['english_equivalent'],':language'=>$exact[1]['language']));
				$already = array();
				$first = 0;
				while ($row = $q->fetch()) { 
					if (isset($row) && $first != '1') {
						echo '<b>Language Equivalents:</b><br />'; 
						$first = 1;
					}
					if (!in_array($row['language'],$already)) {
						echo select_single_value('language',$row['language'],'name',$db);
						echo ': <a href="./index.php?word_search='.$row['name'].'&&language='.$row['language'].'">'.$row['name'].'</a><br />';
					}
				$already[] = $row['language'];
				}
			}
			if ($exact[1]['sample_sentence'] != '') { 
				echo '<br /><b>Sample sentence:</b> ';
			    $find = ' '.$exact[1]['word'].' ';
                $replace = ' <span class="highlight">'.$exact[1]['word'].'</span> ';
                $sample_sentence = str_replace($find,$replace,$exact[1]['sample_sentence']);
                echo $sample_sentence; 
			}	
			$sql = 'SELECT id,name,language FROM word WHERE name LIKE "%'.$word.'%" AND id <> '.$outcome.' LIMIT 50';
			$q = $db->prepare($sql);
			$q->execute(array());
			$first = 0;
			while ($row = $q->fetch()) {
				if (isset($row) && $first != '1') {
					echo '<br /><b>Related words</b>: ';
					$first = 1;
				}
				$percent = 0;
				similar_text($word,$row['name'],$percent);
				if ($percent > '50' && $word != $row['name']) {
					$lang = select_single_value('language',$row['language'],'name',$db);
					echo '<a href="./index.php?word_search='.$row['name'].'&language='.$row['language'].'">'.$row['name'].'</a> ('.$lang.') ';
				}
			}
		}
	}
}
function select_single_value($table,$id,$column,$db) {
	$sql = 'SELECT '.$column.' FROM '.$table.' WHERE id = :id LIMIT 1';
	$q = $db->prepare($sql);
	$q->execute(array(':id'=>$id));
	$row = $q->fetch();
	return $row[$column];
}
function sentence_count($input) {
$sentencemarkers = array('. ','! ','? ','; ');
$sentencearray = explode( $sentencemarkers[0], str_replace($sentencemarkers, $sentencemarkers[0], $input) );
$count = count($sentencearray);
return $count;
}
function update_article($id,$name,$content,$db) {
	$sql = 'UPDATE article SET name = :name, content = :content WHERE id = :id';
	$q = $db->prepare($sql);
	$q->execute(array(':name' => $name,':content' => $content,':id' => $id));
}
function update_basic($type,$id,$content,$db) {
	if ($type == 'language') {
		$name = select_single_value('language',$id,'name',$db);
		$sql = 'UPDATE permissions SET name = :name WHERE name = :old';
		$q = $db->prepare($sql);
		$q->execute(array(':name'=>$content,':old'=>$name));
	}
	$sql = 'UPDATE '.$type.' SET name = :name WHERE id = :id';
	$q = $db->prepare($sql);
	$q->execute(array(':name' => $content,':id' => $id));
}
function update_frequent_words($language,$db) {
	$frequent = array();
	$total_words = select_single_value('language',$language,'total_words',$db);
	$limit = select_single_value('language',$language,'frequent_word_value',$db);
	if ($total_words >= $limit) { 
		$sql = 'SELECT name FROM word WHERE language=? AND blacklist=? AND englishword=? ORDER BY count DESC LIMIT '.$limit;
   		$statement = $db->prepare($sql);
		$statement->execute(array($language,0,0));
    	while ($row = $statement->fetch()) { $frequent[] = $row['name']; }
    	$frequent_words = serialize($frequent);
    	$sql = 'UPDATE language SET frequent_words = :frequent_words WHERE id = :id';
		$q = $db->prepare($sql);
		$q->execute(array(':frequent_words' => $frequent_words,':id' => $language));
	}
}
function update_meta($id,$content,$db) {
	$sql = 'UPDATE meta SET content = :content WHERE id = :id';
	$q = $db->prepare($sql);
	$q->execute(array(':content' => $content,':id' => $id));
}
function update_distinct_words($language,$db) {
	$sql = 'SELECT DISTINCT name FROM word WHERE language='.$language.' AND blacklist != "1" AND englishword != "1" AND count > "0" AND standard_spelling =""'; 
    $result = $db->query($sql)->fetchAll();
    $distinct = count($result);
	$sql = 'UPDATE language SET distinct_words = :distinct_words WHERE id = :language';
	$q = $db->prepare($sql);
	$q->execute(array(':distinct_words' => $distinct,':language'=>$language));
}

function update_language($id,$frequent_word_value,$sentences_constant,$words_constant,$db) {
	$sql = 'UPDATE language SET frequent_word_value = :frequent_word_value, sentences_constant = :sentences_constant, words_constant = :words_constant WHERE id = :id';
	$q = $db->prepare($sql);
	$q->execute(array(':frequent_word_value' => $frequent_word_value,':sentences_constant' => $sentences_constant,':words_constant' => $words_constant,':id' => $id));
}
function update_readability_bulk($language,$db) {
	$frequent_words = select_single_value('language',$language,'frequent_words',$db);
	if ($frequent_words !='') {
		$frequent = 0;
		$total = 0;
		$frequent_word_array = get_frequent_words($language,$db);
		$words_constant = select_single_value('language',$language,'words_constant',$db);
		$sentences_constant = select_single_value('language',$language,'sentences_constant',$db);
		$text_count = count_values('text','language',$language,$db);
		$limit = '25';
		$offset = '0';
		$done = false;
		while (!$done) {
			if ($offset > $text_count) { $progress = $text_count; }
			else { $progress = $offset; }
			$sql = 'UPDATE progress SET text_updater = :offset WHERE id = :id';
			$q = $db->prepare($sql);
			$q->execute(array(':offset' => $progress,':id' => '1')); 
			$sql = 'SELECT id,word_list,words_per_sentence FROM text WHERE language ='.$language.' LIMIT '.$limit.' OFFSET '.$offset;
   			$statement = $db->prepare($sql);
			$statement->execute(array());
			// prepare the update
    		while ($row = $statement->fetch()) { 
    			$id = $row['id'];
    			$word_array_list = unserialize($row['word_list']);
    			$words_per_sentence = $row['words_per_sentence']; 
				foreach ($word_array_list as $key => $value) {
					if (in_array($key,$frequent_word_array)) { $frequent = $frequent+$value; }
					$total = $total+$value;
				}
				$percent_frequent_words = $frequent/$total*100;
				$readability[$id] = ($sentences_constant*$words_per_sentence)+($words_constant*(100-$percent_frequent_words)) +0.839; 
	    	}
	    	// perform the update
	    	$ids = array_keys($readability);
    		$id_list = implode("','",$ids);
			$comma_separated = "'".$id_list."'";
			$sql = "UPDATE text SET readability = CASE id ";
			foreach ($readability as $key => $score ) {
    			$sql .= sprintf("WHEN '%s' THEN %s ", $key, $score);
    			if ($key == end($ids)){
      				$sql .= "END WHERE language = ".$language." AND id IN (".$comma_separated.")";
  				}
  			}
      		$q = $db->prepare($sql);
			$q->execute();
			// move to the next batch
	    	if ($offset > $text_count) { $done = true; }
	    	$offset = $offset+$limit;
	    }
    }
}

function update_readability($language,$id,$db) {
	$frequent = 0;
	$total = 0;
	$frequent_words = select_single_value('language',$language,'frequent_words',$db);
	if ($frequent_words != '') {
		$words_constant = select_single_value('language',$language,'words_constant',$db);
		$sentences_constant = select_single_value('language',$language,'sentences_constant',$db);
		$frequent_word_array = get_frequent_words($language,$db);
		$word_list = select_single_value('text',$id,'word_list',$db);
		$word_array_list = unserialize($word_list);
		$words_per_sentence = select_single_value('text',$id,'words_per_sentence',$db);
		foreach ($word_array_list as $key => $value) {
			if (in_array($key,$frequent_word_array)) { $frequent = $frequent+$value; }
			$total = $total+$value;
		}
		$percent_frequent_words = $frequent/$total*100;
		$readability = ($sentences_constant*$words_per_sentence)+($words_constant*(100-$percent_frequent_words)) +0.839; 
		$sql = 'UPDATE text SET readability = :readability WHERE id = :id';
		$q = $db->prepare($sql);
		$q->execute(array(':readability' => $readability,':id' => $id));
	}
}
function update_total_words($word_count,$language,$db) {
	$old_total_words = select_single_value('language',$language,'total_words',$db);
	$words = $old_total_words+$word_count;
	$sql = 'UPDATE language SET total_words = :value WHERE id = :id';
	$q = $db->prepare($sql);
	$q->execute(array(':value' => $words,':id' => $language));
}
function view_totals($db) {
	$languages = get_name('all','language',$db);
	echo '<table class="default"><tr><td>Language</td><td>Texts</td><td>Total Words</td><td>Word Forms</td></tr>';
    $stats = array();
    foreach ($languages as $key => $value) {
        $stats[$key]['language'] = $value['name'];
        $stats[$key]['texts'] = 0;
        $stats[$key]['total'] = 0;
        $stats[$key]['distinct'] = 0;
        // Get distinct words
        $stats[$key]['distinct'] = select_single_value('language',$key,'distinct_words',$db);
        // Get number of texts
        $stats[$key]['texts'] = count_values('text','language',$key,$db);
        // get total words in each language
        $stats[$key]['total'] = select_single_value('language',$key,'total_words',$db);
    }
    function cmp($a, $b) {
        if ($a['total'] == $b['total']) { return 0; }
        return ($a['total'] > $b['total']) ? -1 : 1;
    }
    usort($stats, "cmp");
    foreach ($stats as $key => $value) {
        echo '<tr><td>'.$value['language'].'</td><td>'.number_format($value['texts']).'</td><td>'.number_format($value['total']).'</td><td>'.number_format($value['distinct']).'</td></tr>';
    }
    echo '</table>';
}
function word_array($input) {
	$input = preg_replace("/[.]/"," ", $input);
	$c = explode(' ',$input);
	foreach ($c as $key => $value) {
		if (strlen($value) > '25') { unset($c[$key]); }
	}
	natcasesort($c);
	$output = array_count_values($c);
	return $output;
}
?>