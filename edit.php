<?php session_start();
$now = microtime(true); 
include('functions/functions.php');
include('variables/variables.php');
include('includes/header.php');
include('includes/nav.php');
if (empty($_SESSION['uid'])) { die('You do not have permission to access this page.'); }

if (isset($_REQUEST['type'])) { $type = $_REQUEST['type']; } else { die(); }
if (isset($_REQUEST['id'])) { $id = $_REQUEST['id']; } else { die(); }
if ($type == 'user') { check_permissions($permission = array(1)); }
if ($type == 'language') { check_permissions($permission = array(2)); }
if ($type == 'text') { check_permissions($permission = array(4)); }
if ($type == 'word') { check_permissions($permission = array(5)); }
if ($type == 'pos') { check_permissions($permission = array(6)); }
if ($type == 'article') { check_permissions($permission = array(7)); }
if ($type == 'meta') { check_permissions($permission = array(10));  } 
if ($type == 'genre') { check_permissions($permission = array(11)); }
if ($type == 'domain') { check_permissions($permission = array(12)); }
if ($type == 'meta' && $id == 'add') { die('You cannot do this.'); } 
if ($type == 'meta' && $id == 'all') { $id = '999'; }
if ($type == 'text' && (!in_array($id,array('add','all')))) { 
    $item_language = select_single_value('text',$id,'language',$db);
    if (!check_language_permission($item_language,$db)) { die('You do not have permission to access this.'); }
}
?>

<section>
<?php if (isset($_REQUEST['message'])) { 
    $messageid = $_REQUEST['message'];
    $message = get_name($messageid,'messages',$db); 
    echo '<div class="message" id="message">'.$message[$messageid]['name'].'</div>';
    } 
?>
<article>
<?php
if ($type == 'language' && $id == 'all') { echo '<div class="message">If you delete a language, any texts or words already in the database will remain, but they will be listed as "Uncategorized".</div>'; }
  if ($type == 'language') { // Add additional action buttons
    
    $sql = 'SELECT COUNT(*) FROM text';
    $q = $db->prepare($sql);
    $q->execute(array());
    $values['total'] = $q->fetchColumn(); 
    $values['type'] = 'sentence';
    $values['label'] = 'Update sample sentence database';
    $values['batch'] = 10;
    $values['message'] = 'Sentences updated correctly';
    echo ahah($values); 
    $values['type'] = 'uncategorized_words';
    $values['total'] = 1;
    $values['label'] = 'Remove language-uncategorized words';
    $values['batch'] = 1;
    $values['message'] = 'All uncategorized words removed from the database';
    echo ahah($values);
  } 

?>
  <form method="post" action="save.php">
<?php         
if ($id == 'all') {
  if (in_array($type,array('user','text','article','language','pos','genre','domain'))) { 
  echo '<p><a href="edit.php?type='.$type.'&id=add">+ Add a new '.$type.'</a><p>';   
  echo '<h2>Current '.$type.' list</h2>';
  $data = get_name($id,$type,$db);
  if (empty($data)) { die('There are no '.$type.'s yet.'); }
  foreach ($data as $key => $value) { 
    if ($type == 'user' && $key =='1' && $_SESSION['uid'] != '1') { }
    else {
      if ($type == 'text') { 
        $item_language = select_single_value('text',$key,'language',$db);
        if (check_language_permission($item_language,$db)) { 
          echo '<a href="edit.php?type='.$type.'&id='.$key.'">'.$value['name'].'</a> (<a href="save.php?type='.$type.'&id='.$key.'&submit=delete">delete</a>)<br />';
        }
      }
      else {
        echo '<a href="edit.php?type='.$type.'&id='.$key.'">'.$value['name'].'</a>';
        if ($type != 'article' && !in_array($id,array(1,2,3))) { echo ' (<a href="save.php?type='.$type.'&id='.$key.'&submit=delete">delete</a>)'; }
        echo '<br />';
      }
    }
  } 
}
elseif ($type != 'meta') { $id = 'add'; }
}
else {
  if ($id == 'add') {  // Prepare Add form
    $status = 'Add'; 
    $data['name'] = ''; 
    $content = ''; 
    $data['email'] = '';
    $data['author'] = '';
    $data['year'] = '';
    $data['language'] = '';
    $data['genre'] = '';
  }
  else { $status = 'Update'; }

if ($type == 'user' && $id =='1' && $_SESSION['uid'] != '1') { die('You do not have permission to access this.'); }
$data = get_all($type,$id,$db); 
if (in_array($type,array('language','domain','genre','word','pos'))) { $content = $data['name']; }
else { $content = br2nl($data['content']); }
            
if ($type == 'user') {
    if ($data['access'] != '') { $p = unserialize($data['access']); $perms = array_keys($p); }
    else { $p = array('0'); $perms = array_keys($p); }  
}  

echo '<header><h2>'.$status.' '.$type.'</h2></header>';

if ($type == 'text') { 
  dropdown_limited('language',$data['language'],$db);
  term_dropdown('genre',$data['genre'],$db);
  echo '<br />';
  echo '<input type="text" name="name" value="'.$data['name'].'" placeholder="Title" required="required" style="width:400px" /><br />';
  echo '<input type="text" name="author" value="'.$data['author'].'" placeholder="Author" /><br />';
  echo '<input type="text" name="year" value="'.$data['year'].'" placeholder="Year" /><br />';          
}
if (in_array($type, array('user','article'))) { echo '<input type="text" name="name" value="'.$data['name'].'" style="width:300px" placeholder="Name"/><br />'; }          
if (in_array($type, array('text','article'))) { echo '<textarea name="content" style="width:100%;height:400px;">'.$content.'</textarea>'; }
if (!in_array($type, array('user','meta','article','text'))) { echo '<input type="text" name="content" value="'.$content.'" <br />'; }

if ($type == 'meta') {
  $sql = 'SELECT * FROM meta'; 
  $statement = $db->prepare($sql);
  $statement->execute(array());
    while ($row = $statement->fetch()) {
      if ($row['id'] == '1') { $footer = $row['content']; }
      if ($row['id'] == '2') { $title = $row['content']; }
      if ($row['id'] == '3') { $sidebar = $row['content']; }
      if ($row['id'] == '6') { $words_to_display = $row['content']; }
      if ($row['id'] == '7') { $texts_to_display = $row['content']; }
      if ($row['id'] == '8') { $search_results = $row['content']; }
    }
  echo '<br />Website title: ';
  echo '<input type="text" name="title" value="'.$title.'" style="width:100%;" />';
  echo '<br />Footer/copyright info: ';
  echo '<input type="text" name="footer" value="'.$footer.'" style="width:100%;" />';
  echo '<br />Words to display on "Words" page: ';
  echo '<input type="text" name="words_to_display" value="'.$words_to_display.'" style="width:50px;" />';
  echo '<br />Texts to display on "Texts" page: ';
  echo '<input type="text" name="texts_to_display" value="'.$texts_to_display.'" style="width:50px;" />';
  echo '<br />Search result specificity: ';
  echo '<input type="text" name="search_results" value="'.$search_results.'" style="width:50px;" />';
  echo '<span class="subtext">50% to 80% is recommended. 50% will have very broad results; 80% will have narrower results.';
  echo '<br />Sidebar text: ';
  echo '<textarea name="sidebar" style="width:100%;height:400px;" />'.br2nl($sidebar).'</textarea>';
}

if ($type == 'word') {
  dropdown_limited('language',$data['language'],$db);
  echo '<span class="subtext">Note: the same word may exist in other languages.</span><br />';
  echo 'Definition: <input type="text" name="definition" value="'.$data['definition'].'" style="width:100%;" placeholder="Meaning" /><br />';
  echo 'Sample Sentence: <textarea name="sample_sentence" style="width:100%" />'.$data['sample_sentence'].'</textarea>';
  echo 'Primary Part of Speech: ';
  multiterm_dropdown('pos',$data['pos'],$db);
  echo '<br />Secondary Part of Speech: ';
    $terms = get_name('all','pos',$db); 
    echo '<select name="pos[]"><option value="all">none</option>';
    foreach ($terms as $key => $value) { 
        echo '<option value="'.$key.'"';
        if ($data['postwo'] == $key) { echo 'selected="selected"'; }
        echo '>'.$value['name'].'</option>'; 
    }
  echo '<option value="0"';
    if ($data['postwo'] == '0') { echo 'selected="selected"'; }
    echo '>Uncategorized</option>';
    echo '</select>';
  echo '<br />';
  echo 'English Equivalent (1 word): <input type="text" name="english_equivalent" value="'.$data['english_equivalent'].'" style="width:150px;" placeholder="Meaning" /><br />';
  echo 'Semantic Domain: ';
  term_dropdown('domain',$data['domain'],$db);
  echo '<br />';
  echo '<input type="checkbox" name="blacklist" value="1" ';
  if ($data['blacklist'] == '1') { echo 'checked="checked" '; }
  echo '/> Do not display this word in page results<br />';
  echo '<input type="checkbox" name="englishword" value="1" ';
  if ($data['englishword'] == '1') { echo 'checked="checked" '; }
  echo '/> This is an English loan word<br />';
  echo 'Standard spelling: <input type="text" name="standard_spelling" value="'.$data['standard_spelling'].'" placeholder="Leave blank if this is standard spelling" style="width:300px;" /><br />';
}
if ($type == 'language' && $status == 'Update') { 
  echo '<p>The following fields pertain to the '.$data['name'].' readability instrument. The generic formula for calculating grade level is: </p>'; 
  echo '<div class="code-box">Grade level = (sentences_constant*words_per_sentence) + (words_constant*(100-percent_frequent_words)) + 0.839</div>';
  echo '<p>Based on corpus data, the validated readability formula for '.$data['name'].' is:</p>';
  echo '<div class="code-box">Grade level = ('.$data['sentences_constant'].'*words_per_sentence) + ('.$data['words_constant'].'*(100-percent_frequent_words)) + 0.839</div>';
  echo '<div class="blurb-box">How many of the most frequent words in '.$data['name'].' should be considered easy? (default is 1,000) <input type="text" name="frequent_word_value" value="'.$data['frequent_word_value'].'" /></div>'; 
  echo '<div class="blurb-box">Words Constant (default=.086): <input type="text" name="words_constant" value="'.$data['words_constant'].'" /></div>'; 
  echo '<div class="blurb-box">Sentences Constant (default=.141): <input type="text" name="sentences_constant" value="'.$data['sentences_constant'].'" /></div>'; 
}

if ($type == 'user') { 
  echo 'Email <input type="email" name="email" value="'.$data['email'].'" /><br />'; 
  echo 'Password <input type="password" name="password" value="" />'; 
  $permissions = get_permissions($db); 
  echo '<div class="blurb-box"><table style="width:100%;"><tr><td><h3>Permissions</h3></td><td><h3>Which languages can this user administer?</h3></td></tr><tr><td>';
  foreach($permissions as $key => $value) {
    if ($value['url'] != 'language') {
      echo '<input type="checkbox" name="permission['.$key.']" ';
      if (in_array($key,$perms)) { echo 'checked="checked"'; }
      echo ' />'.$value['name'].'<br />';
    }
  }
  echo '</td><td>';
  foreach($permissions as $key => $value) {
    if ($value['url'] == 'language') {
      echo '<input type="checkbox" name="permission['.$key.']" ';
      if (in_array($key,$perms)) { echo 'checked="checked"'; }
      echo ' />'.$value['name'].'<br />';
    }
  }
  echo '</td></tr></table></div>';
}
echo '<input type="hidden" name="type" value="'.$type.'" />';
echo '<input type="hidden" name="id" value="'.$id.'" />';
echo '<input type="submit" name="submit" value="'.$status.'" /></form>';

if ($type == 'language' && $status == 'Update') { 
  $total = count_values('text','language',$id,$db);
  $message = 'Readability updated for all texts.';
  $url = 'includes/task.php?type=readability&language='.$id.'&total='.$total;
  echo '
<button value="Submit" onclick="runTask(\''.$url.'\'),checker('.$total.',\''.$message.'\')">Recalculate readability for all texts</button>
<progress id="progressBar" value="0" max="'.$total.'" class="hide"></progress>
<span id="progress" class="hide"><span id="finished">0</span> out of '.$total.'</span>';
echo '<div id="result"></div>';

  if (isset($data['frequent_words'])) {
    echo '<div class="blurb-box"><h2>Frequent words in '.$data['name'].':</h2>';
    $words = unserialize($data['frequent_words']); 
    if (isset($words[0])) { foreach ($words as $word) { echo $word.', '; } }
    else { echo 'There are not enough words yet to generate a frequent word list.'; }
    echo '</div>';

  }
  echo '<div class="blurb-box"><h2 id="frequent_manual">Manually added frequent words in '.$data['name'].' (click X to remove):</h2>';
    if (!empty($data['frequent_manual'])) {
      $words = unserialize($data['frequent_manual']); 
      if (isset($words[0])) { foreach ($words as $word) { 
        echo $word.'(<a href="';
        echo './save.php?type=frequent&word='.$word.'&language='.$data['id'].'&submit=Update';
        echo '">X</a>), '; } }
    }
    else { echo 'There are no manually added words yet.'; }
    echo '</div>';
    
}
}
?>
          </article>
</section>
<aside>
  <?php 

    if (isset($data['sentence_count'])) { echo '<p>Sentences: '.$data['sentence_count'].'<p>'; }
    if (isset($data['word_count'])) { echo '<p>Words: '.$data['word_count'].'<p>'; } 
    if (isset($data['words_per_sentence'])) { echo '<p>Words per sentence: '.$data['words_per_sentence'].'</p><p class="subtext">Shorter sentences indicate simpler reading level. Very simple texts have less than 10 words per sentence. Difficult texts can have more than 20.</p>'; } 
  if (isset($data['readability'])) { echo '<p>Grade Level: '.$data['readability'].'</p><p class="subtext">Gives approximate grade level, based on the percent of familiar words and and sentence length.</p>'; } 
    else { $meta = get_all('meta','3',$db); echo nl2br($meta['content']); }
    ?>
</aside> 
<?php
include('includes/footer.php'); 
?>
           
