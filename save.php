<?php session_start();
$now = microtime(true); 
include('functions/functions.php');
include('variables/variables.php');
if (isset($_REQUEST['submit'])) {
if (isset($_REQUEST['type'])) { $type = $_REQUEST['type']; }
if (isset($_REQUEST['id'])) { $id = $_REQUEST['id']; }
if ($type == 'user') { check_permissions($permission = array(1)); }
if ($type == 'language') { check_permissions($permission = array(2)); }
if ($type == 'text') { check_permissions($permission = array(4)); }
if ($type == 'word') { check_permissions($permission = array(5)); }
if ($type == 'pos') { check_permissions($permission = array(6)); }
if ($type == 'article') { check_permissions($permission = array(7)); }
if ($type == 'meta') { check_permissions($permission = array(8,9,10));  }
if ($type == 'genre') { check_permissions($permission = array(11)); }
if ($type == 'domain') { check_permissions($permission = array(12)); }
if ($type == 'meta' && $id == 'add') { die('You cannot do this.'); } 
if (isset($_REQUEST['content'])) { $content = $_REQUEST['content']; }
if (isset($_REQUEST['word'])) { $word = $_REQUEST['word']; }
if (isset($_REQUEST['name'])) { $name = ucwords(strtolower($_REQUEST['name'])); }
if (isset($_REQUEST['email'])) { $email = $_REQUEST['email']; }
if (isset($_REQUEST['language'])) { $language = $_REQUEST['language']; }
if (isset($_REQUEST['author'])) { $author = ucwords(strtolower($_REQUEST['author'])); }
if (isset($_REQUEST['year'])) { $year = $_REQUEST['year']; }
if (isset($_REQUEST['genre'])) { $genre = $_REQUEST['genre']; }
if (isset($_REQUEST['frequent_word_value'])) { $frequent_word_value = $_REQUEST['frequent_word_value']; }
if (isset($_REQUEST['sentences_constant'])) { $sentences_constant = $_REQUEST['sentences_constant']; }
if (isset($_REQUEST['words_constant'])) { $words_constant = $_REQUEST['words_constant']; }
if (isset($_REQUEST['title'])) { $title = $_REQUEST['title']; }
if (isset($_REQUEST['footer'])) { $footer = $_REQUEST['footer']; }
if (isset($_REQUEST['sidebar'])) { $sidebar = $_REQUEST['sidebar']; }
if (isset($_REQUEST['words_to_display'])) { $words_to_display = $_REQUEST['words_to_display']; }
if (isset($_REQUEST['texts_to_display'])) { $texts_to_display = $_REQUEST['texts_to_display']; }
if (isset($_REQUEST['search_results'])) { $search_results = $_REQUEST['search_results']; }
if (isset($_REQUEST['definition'])) { $definition = $_REQUEST['definition']; }
if (isset($_REQUEST['domain'])) { $domain = $_REQUEST['domain']; }
if (isset($_REQUEST['sample_sentence'])) { $sample_sentence = $_REQUEST['sample_sentence']; }
if (isset($_REQUEST['english_equivalent'])) { $english_equivalent = $_REQUEST['english_equivalent']; }
if (isset($_REQUEST['pos'])) { $pos_array = $_REQUEST['pos']; $pos = $pos_array[0]; $postwo = $pos_array[1]; }
if (isset($_REQUEST['standard_spelling'])) { $standard_spelling = $_REQUEST['standard_spelling']; }
if (isset($_REQUEST['referrer'])) { $referrer = $_REQUEST['referrer']; }
if (isset($_REQUEST['englishword'])) { $englishword = $_REQUEST['englishword']; }
	else { $englishword = 0; }
if (isset($_REQUEST['blacklist'])) { $blacklist = $_REQUEST['blacklist']; }
	else { $blacklist = 0; }
if ($_REQUEST['submit'] == 'delete' && (!in_array($type, array('text','meta')))) {
	delete_basic($type,$id,$db);
	}
if ($_REQUEST['submit'] == 'Add' && (in_array($type, array('language','genre','pos','domain')))) {
	insert_basic($type,$content,$db);
	}
if ($_REQUEST['submit'] == 'Update' && (in_array($type, array('language','genre','pos','domain')))) {
	update_basic($type,$id,$content,$db);
	if ($type == 'language') { 
		update_language($id,$frequent_word_value,$sentences_constant,$words_constant,$db);
		update_frequent_words($id,$db);
		header('Location: ./edit.php?type='.$type.'&id=all&message=2');
		exit();
	}
	else { 
		header('Location: ./edit.php?type='.$type.'&id=all');
		exit();
	}
}

if ($type == 'frequent') { 
	if ($_REQUEST['submit'] == 'Update') { 	
		$frequent_manual = select_single_value('language',$language,'frequent_manual',$db);
		$frequent = unserialize($frequent_manual);
		if (in_array($word,$frequent)) {
			foreach ($frequent as $key => $value) {
				if ($value == $word) {
					unset($frequent[$key]);
				}
			}	
		}
		$frequent = array_values($frequent);
		$frequent_manual = serialize($frequent);
		$sql = 'UPDATE language SET frequent_manual = :frequent_manual WHERE id = :id';
		$q = $db->prepare($sql);
		$q->execute(array(':frequent_manual'=>$frequent_manual,':id' => $language));
		header('Location: ./edit.php?type=language&id='.$language.'#frequent_manual');
		exit();
	}
	if ($_REQUEST['submit'] == 'Add') {
		$frequent = array();
		$language = select_single_value('text',$referrer,'language',$db);
		$frequent_manual = select_single_value('language',$language,'frequent_manual',$db);
		$frequent = unserialize($frequent_manual);
		if (!in_array($word,$frequent)) {
			$frequent[] = $word;	
		}
		$frequent_manual = serialize($frequent);
		$sql = 'UPDATE language SET frequent_manual = :frequent_manual WHERE id = :id';
		$q = $db->prepare($sql);
		$q->execute(array(':frequent_manual'=>$frequent_manual,':id' => $language));
		header('Location: ./index.php?type=text&id='.$referrer.'&frequent_mod=1');
		exit();
	}
}
if ($type == 'article') { 
	if ($_REQUEST['submit'] == 'Add') { $id = insert_article($name,$content,$db); }
	if ($_REQUEST['submit'] == 'Update') { update_article($id,$name,$content,$db); }
	header('Location: ./index.php?type=article&id='.$id);
	exit();
}
if ($type == 'meta') { 
	$sql = 'UPDATE meta SET content = :content WHERE id = :id';
	$q = $db->prepare($sql);
	$q->execute(array(':content'=>$title,':id' => '2'));
	$q->execute(array(':content'=>$footer,':id' => '1'));
	$q->execute(array(':content'=>$sidebar,':id' => '3'));
	$q->execute(array(':content'=>$words_to_display,':id' => '6'));
	$q->execute(array(':content'=>$texts_to_display,':id' => '7'));
	$q->execute(array(':content'=>$search_results,':id' => '8'));
	header('Location: ./index.php');
	exit();
}
if ($type == 'text') {
	if ($_REQUEST['submit'] == 'delete') { 	
		$language = select_single_value('text',$id,'language',$db);
		$word_count = select_single_value('text',$id,'word_count',$db);
		$word_count = $word_count*-1;
		$word_array_mod = delete_text($id,$db);
		foreach ($word_array_mod as $key => $value) {
			$neg_word_array_mod[$key] = $value*-1;
		}
		$sentence_array = array();
		process_words($neg_word_array_mod,$sentence_array,$language,$db);
		update_total_words($word_count,$language,$db);
		update_distinct_words($language,$db);
		header('Location: ./edit.php?type='.$type.'&id=all');
		exit();
	}
	if (str_word_count($content) > '100000') { header('Location: ./edit.php?type=text&id=add&message=1'); }
	else { 
		process_text($id,$db,$type,$name,$content,$language,$author,$year,$genre,$_REQUEST['submit']);
		header('Location: ./index.php?type='.$type.'&id=all&language='.$language);
		exit();
	}
}
if ($type == 'user') {
	check_permissions($permission = array(1));
	$perms = serialize($_POST['permission']);
	if ($_POST['password'] == '') { $password = ''; }
	else { $password = hash('ripemd160', $_POST['password']); }
	if ($id == 'add') {
		$sql = "INSERT INTO user (name,email,password,access) VALUES (:name,:email,:password,:access)";
		$q = $db->prepare($sql);
		$q->execute(array(':name'=>$name,':email'=>$_POST['email'],':password'=>$password,':access'=>$perms));
	}
	else {
		if ($password != '') {
			$sql = 'UPDATE '.$type.' SET password = :password WHERE id = :id';
			$q = $db->prepare($sql);
			$q->execute(array(':password'=>$password,':id' => $id));
			}
		$sql = 'UPDATE '.$type.' SET name = :name, email = :email, access = :access WHERE id = :id';
		$q = $db->prepare($sql);
		$q->execute(array(':name'=>$name,':email'=>$_POST['email'],':id' => $id,':access' => $perms));
	}
	$statement = $db->prepare("SELECT access FROM user WHERE id = :id");
	$statement->execute(array(':id'=>$id));
    $row = $statement->fetch();
	if (isset($row['access'])) { 
		$p = unserialize($row['access']);
		$_SESSION['permissions'] = array_keys($p);
	}
	header('Location: ./edit.php?type=user&id=all');
	exit();
}
if ($type == 'word') {
	$word = strtolower($content);
	if ($id == 'add') {
		$sql = "INSERT INTO word (name,language,definition,pos,postwo,sample_sentence,english_equivalent,domain,englishword,blacklist,standard_spelling) VALUES (:name,:language,:definition,:pos,:postwo,:sample_sentence,:english_equivalent,:domain,:englishword,:blacklist,:standard_spelling)";
		$q = $db->prepare($sql);
		$q->execute(array(':name'=>$word,':language'=>$language,':definition'=>$definition,':pos'=>$pos,':postwo'=>$postwo,':sample_sentence'=>$sample_sentence,':english_equivalent'=>$english_equivalent,':domain'=>$domain,':englishword'=>$englishword,':blacklist'=>$blacklist,':standard_spelling'=>$standard_spelling));
	}
	else {
		$sql = 'UPDATE word SET name = :name, language = :language, definition = :definition,pos = :pos,postwo = :postwo,sample_sentence = :sample_sentence,english_equivalent = :english_equivalent,domain = :domain,englishword = :englishword,blacklist = :blacklist,standard_spelling=:standard_spelling WHERE id = :id';
		$q = $db->prepare($sql);
		$q->execute(array(':name'=>$word,':language'=>$language,':definition'=>$definition,':pos'=>$pos,':postwo'=>$postwo,':sample_sentence'=>$sample_sentence,':english_equivalent'=>$english_equivalent,':domain'=>$domain,':englishword'=>$englishword,':blacklist'=>$blacklist,':standard_spelling'=>$standard_spelling,':id'=>$id));
	}
	header('Location: ./index.php?type=word&id=all&$language='.$language);
	exit();
}
else { 
	 header('Location: ./index.php'); 
}
}
//echo microtime(true) - $now; 
?>