<?php
    if ($type == 'semantic' && $id != 'all') {
        $data['name'] = select_single_value('domain',$id,'name',$db);
    }
    if ($type == 'readability') { $data['name'] = 'Readability Instrument'; $id='all';}
    if ($type == 'semantic' && $id == 'all') { $data['name'] = 'Semantic Domains'; }
    if ($type == 'statistical') { $data['name'] = 'Statistical Analysis'; }
    $name = strtolower($data['name']);
    $name = ucwords($name);
    if ($type == 'text' && $id != 'all') {
        if (isset($_SESSION['permissions'])) {
            $lang = select_single_value('text',$id,'language',$db);
            if (check_language_permission($lang,$db)) { $name .=' (<a href="./edit.php?type=text&id='.$id.'">edit</a>)'; }
        }
    }
    if ($type == 'article' && 'id' != 'all') { 
        if (check_permissions_single('7',$db)) { $name .= ' (<a href="./edit.php?type=article&id='.$id.'">edit</a>)'; }
    }

echo '<section';
if ($id == 'all') { echo ' class="all"'; }
echo '>';
if (isset($_REQUEST['message'])) { 
    $messageid = $_REQUEST['message'];
    $message = get_name($messageid,'messages',$db); 
    echo '<div class="spaced-box">'.$message[$messageid]['name'].'</div>';
    } 
?>
	<article>
		<header>
        <h2><?php echo $name; ?>
        </h2>
        <?php if (isset($data['author'])) { $author = strtolower($data['author']); $author = ucwords($author); $author = $author; echo '<p>By '.$author.'<p>'; } ?>
        <?php if (isset($data['year'])) { echo '<p>Published: '.$data['year'].'<p>'; } ?>
        <?php if (isset($data['genre'])) { '<p>Genre: '.select_single_value('genre',$data['genre'],'name',$db).'<p>'; } ?>
        </header>
<?php
if ($type == 'statistical') {
        echo statistical_analysis_controller($db);
    }
if ($id == 'all') {
    if ($type == 'readability') {
        readability_form($db);
    }
    if ($type == 'word') {
        // Begin code for displaying filters
        $filter = '';
        $results = select_single_value('meta','6','content',$db);
               if (isset($_REQUEST['language'])) { $language = $_REQUEST['language']; } 
        else { $language = 'all'; }
        if ($language == 'all' && empty($_REQUEST['english_loan']) && empty($_REQUEST['blacklist'])) { 
            $l = get_name('all','language',$db);
            $l_keys = array_keys($l);
            $total_words = 0;
            foreach ($l_keys as $l) {
                $total_words = $total_words+select_single_value('language',$l,'distinct_words',$db);
            }
        }
        elseif(empty($_REQUEST['english_loan']) && empty($_REQUEST['blacklist'])) { 
            $total_words = select_single_value('language',$language,'distinct_words',$db);
        }
        elseif($language == 'all' && empty($_REQUEST['english_loan'])) {
            $sql = 'SELECT DISTINCT name FROM word WHERE blacklist == "1"'; 
            $result = $db->query($sql)->fetchAll();
            $total_words = count($result);
        }
        elseif($language == 'all' && empty($_REQUEST['blacklist'])) {
            $sql = 'SELECT DISTINCT name FROM word WHERE englishword = "1"'; 
            $result = $db->query($sql)->fetchAll();
            $total_words = count($result);
        }
        elseif(empty($_REQUEST['english_loan'])) {
            $sql = 'SELECT DISTINCT name FROM word WHERE language='.$language.' AND blacklist = "1"'; 
            $result = $db->query($sql)->fetchAll();
            $total_words = count($result);
        }
        elseif(empty($_REQUEST['blacklist'])) {
            $sql = 'SELECT DISTINCT name FROM word WHERE language='.$language.' AND englishword = "1"'; 
            $result = $db->query($sql)->fetchAll();
            $total_words = count($result);
        }
        else {
            $sql = 'SELECT DISTINCT name FROM word WHERE language='.$language.' AND englishword = "1" AND blacklist = "1"'; 
            $result = $db->query($sql)->fetchAll();
            $total_words = count($result);
        }
        if ($total_words < $results) { $limit = $total_words; $offset = 0; }
        else { $limit = $results; }
        if (isset($_REQUEST['submit_inc'])) {
            if ($limit+$_REQUEST['offset'] >= $total_words) { $limit = $total_words-$_REQUEST['offset']; }
            else { $limit = $results; }
            $offset = $_REQUEST['offset'];
        }
        else { $offset = 0; }
        $offsetplus = $offset+$results;
        $remaining = $total_words-$offset-$limit;
        $end = $offset+$limit;
        $start = $offset+1;
		if (isset($_REQUEST['order'])) { $order = $_REQUEST['order']; } else { $order = 'count'; }
        if ($remaining > $results) { $remaining = $results; }
        echo '<form action="index.php?type=word" method="post">';
        term_dropdown('language',$language,$db);
        echo '<input type="submit" value="Filter" name="submit" />';
        echo '</select><input type="hidden" name="id" value="all" />';
        echo '<input type="hidden" name="offset" value="'.$offsetplus.'" />';
        echo ' Displaying results '.$start.'-'.$end.' of '.number_format($total_words);
        if ($remaining > 0) { echo ' <input type="submit" value="Next '.$remaining.' results" name="submit_inc" />'; }
        echo '<br /><input type="checkbox" name="english_loan" value="1" ';
        if (isset($_REQUEST['english_loan'])) { echo 'checked="checked"'; $english_loan = $_REQUEST['english_loan'];}
        else { $english_loan = 'no'; }
        echo ' /> Show English loan words';
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="blacklist" value="1" ';
        if (isset($_REQUEST['blacklist'])) { echo 'checked="checked"'; $blacklist = $_REQUEST['blacklist'];}
        else { $blacklist = 'no'; }
        echo ' /> Show blacklisted words';
        echo '</form>';
        // Form for spreadsheet
        if (isset($_SESSION['uid'])) {
            echo '<form action="export-words.php" method="post" />';
            echo '<input type="hidden" name="language" value="'.$language.'" />';
            if ($language == 'all') {
                $lang_name = 'all languages ';
            }
            else {
                $lang_name = select_single_value('language',$language,'name',$db).' words ';
            }
            echo ' <input type="submit" value="Export '.$lang_name.'to spreadsheet" name="submit" /> <span class="subtext">(May take some time)</span>';
            echo '</form>';
        }
        // Display results
        $view = select_frequent_words($language,$offset,$limit,$order,$english_loan,$blacklist,$db);
        if (isset($view)) {
            $inc = $start;
            echo '<table class="default"><tr><td>Word</td><td>Definition</td><td>Part of Speech</td><td>Sample Usage</td><td>Count</td></tr>';
            foreach ($view as $key =>$value) { 
                $languages = get_name('all','language',$db);
                $lang = $value['language'];
                $count = number_format($value['count']);
                $pos = select_single_value('pos',$value['pos'],'name',$db);
                $postwo = select_single_value('pos',$value['postwo'],'name',$db);
                $standard_spelling = $value['standard_spelling'];
                $pos_array = array();
                if (isset($pos)) { $pos_array[] = $pos; }
                if ($value['definition'] != '') { $meaning = $value['definition']; }
                else { $meaning = $value['english_equivalent']; }
                if (isset($postwo)) { $pos_array[] = $postwo; }
                $parts_of_speech = join(', ',$pos_array);
                if (isset($languages[$lang]['name'])) { $lang_display = $languages[$lang]['name']; }
                else { $lang_display = 'Uncategorized'; }
                $find = ' '.$value['name'].' ';
                $replace = ' <span class="highlight">'.$value['name'].'</span> ';
                $sample_sentence = str_replace($find,$replace,$value['sample_sentence']);
                $sample_sentence = $sample_sentence;
                if (check_language_permission($lang,$db)) { $word = '<a href="edit.php?type=word&id='.$value['id'].'">'.$value['name'].'</a>'; }
                else { $word = $value['name'];}
                echo '<tr><td>'.$inc.'. '.$word;
                if ($standard_spelling != '' ) { echo ' <i>('.$standard_spelling.')</i> '; }
                if ($language == 'all') { echo ' ('.$lang_display.') '; }
                echo '</td><td>'.$meaning.'</td><td>'.$parts_of_speech.'</td><td>'.$sample_sentence.'</td><td>'.$count.'</td></tr>'; 
                $inc++;
            }
            echo '</table>';
        }
    }
    if ($type == 'semantic') {
        $domains = get_name('all','domain',$db);
       $num = 1;
        foreach ($domains as $key => $value) {
            echo $num.'. <a href="index.php?type=semantic&id='.$key.'">'.$value['name'].'</a><br />';
            $num++;
        }
    }
    if ($type == 'text') {
        $genres = get_name('all','genre',$db);
        $results = select_single_value('meta','7','content',$db);
        if (isset($_REQUEST['language'])) { $language = $_REQUEST['language']; } 
        else { $language = 'all'; }
        if ($language == 'all') { 
            $l = get_name('all','language',$db);
            $l_keys = array_keys($l);
            $l_join = join(',',$l_keys);
            $sql = 'SELECT id FROM text WHERE language IN ("'.$l_join.'")';
            $result = $db->query($sql)->fetchAll();
            $total_texts =  count($result);
        }
        else { $total_texts = count_values('text','language',$language,$db); }
        if ($total_texts < $results) { $limit = $total_texts; $offset = 0; }
        else { $limit = $results; }
        if (isset($_REQUEST['submit_inc'])) {
            if ($limit+$_REQUEST['offset'] >= $total_texts) { $limit = $total_texts-$_REQUEST['offset']; }
            else { $limit = $results; }
            $offset = $_REQUEST['offset'];
        }
        else { $offset = 0; $limit = $results; }
        $start = $offset+1;
        $remaining = $total_texts-$offset-$limit;
        if (isset($_REQUEST['genre'])) { $genre = $_REQUEST['genre']; } else { $genre = 'all'; }
        if (isset($_REQUEST['order'])) { $order = $_REQUEST['order']; } else { $order = 'name'; }
        if (isset($_REQUEST['readability'])) { $readability = $_REQUEST['readability']; } else { $readability = ''; }
        $filter = '';
        $end = $offset+$limit;
        $view = select_texts($language,$genre,$offset,$limit,$order,$readability,$filter,$db);
        $offsetplus = $offset+$view['count'];
        if ($remaining > $results) { $remaining = $results; }
        echo '<form action="index.php?type=text" method="post">';
        term_dropdown('language',$language,$db);
        term_dropdown('genre',$genre,$db);
        print_grade_levels($readability,$language,$db);
        print_order_filter($order);
        echo '<input type="submit" value="Filter" name="submit" />';
        echo '</select><input type="hidden" name="id" value="all" />';
        echo '<input type="hidden" name="offset" value="'.$offsetplus.'" />';
        
        if ($total_texts > 0) {
            if ($end <= 100) { $end = $view['count']; }
            if ($offset == 0) { 
                echo ' Results '.$start.'-'.$end.' of '.$view['total'].' results';
            }
            else {
                echo ' Results '.$start.'-'.$end;
            }
            if ($remaining > 0) { 
                echo ' <input type="submit" value="Next '.$remaining.' results" name="submit_inc" />'; }
            }
        echo '</form>';

        if (isset($view)) {
            echo '<table class="default"><tr><td>Title</td><td>Author</td><td>Genre</td><td>Grade Level</td><td>Words</td></tr>';
            foreach ($view as $key =>$value) {
                if (isset($value['id'])) {
                    $gen = $value['genre'];
                    if (isset($genres[$gen]['name'])) { $genre = $genres[$gen]['name']; }
                    else { $genre = 'Uncategorized'; }
                    $author = strtolower($value['author']);
                    $author = ucwords($author);
                    $title = strtolower($key);
                    $title = ucwords($title);
                    echo '<tr><td><a href="./index.php?type=text&id='.$value['id'].'">'.$title.'</a>';
                    if (isset($_SESSION['permissions'])) {
                        if (check_language_permission($value['language'],$db)) { echo ' (<a href="./edit.php?type=text&id='.$value['id'].'">edit</a>)'; }
                    }
                    echo '</td><td>'.$author.'</td><td>'.$genre.'</td><td>'.$value['readability'].'</td><td>'.$value['word_count'].'</td></tr>'; 
                }    
            }
            echo '</table>';
        }
    }
}
// Display individual semantic domain
$language_array = get_name('all','language',$db);
foreach ($language_array as $key => $value) {
   if ($value['name'] != 'English') {
    $languages[] = $key;
   }
}
if ($type == 'semantic' && $id != 'all') {
    $results = get_all_semantic($id,$db);
    echo '<table class="default"><tr><td>English</td>';
    foreach ($languages as $language) {
        echo '<td>'.select_single_value('language',$language,'name',$db).'</td>';
    }
    echo '</tr>';
    foreach ($results as $key => $value) {
        echo '<tr /><td>'.$key.'</td>';
        foreach ($languages as $language) {
            echo '<td>';
            if (isset($value[$language])) { echo $value[$language]; }
            else { echo ''; }
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}
// Content display for non table views
if (isset($data['content'])) { 
$content = $data['content'];
    if ($type == 'text' && $frequent_mod == '1') {
        // // add language permission check
        $language = select_single_value('language',$data['language'],'name',$db);
        $frequent_words = get_frequent_words($data['language'],$db);
        echo 'The highlighted words below are not among the 1,000 most frequent in '.$language.'. To manually mark them as a common word, click on the word. 
        These additions can be reviewed on the <a href="edit.php?type=language&id='.$data['language'].'">'.$language.'</a> language page.<hr />';
        print highlight_frequent('edit',$data['id'],$frequent_words,$content,$db);
    }
    else {
        if ($type == 'text') { echo '<a href="?type=text&id='.$data['id'].'&frequent_mod=1">Manually tag words in this document as frequent</a><br />'; }
        echo nl2br($content); 
    }
}
?>
    </article> 
</section>
<?php 
if ($id != 'all' && $type != 'semantic') {
    echo '<aside>';
    if (isset($data['sentence_count'])) { echo '<div class="statistics"><h2>Text Statistics</h2><p>Sentences: '.$data['sentence_count'].'<p>'; }
    if (isset($data['word_count'])) { echo '<p>Words: '.$data['word_count'].'<p>'; } 
    if (isset($data['words_per_sentence'])) { echo '<p>Words per sentence: '.$data['words_per_sentence'].'</p><p class="subtext">Shorter sentences indicate simpler reading level. Very simple texts have less than 10 words per sentence. Difficult texts can have more than 20.</p>'; } 
    if (isset($data['readability'])) { echo '<p>Grade Level: '.$data['readability'].'</p><p class="subtext">Gives approximate grade level, based on the percent of familiar words and and sentence length.</p>'; } 
    if (isset($data['sentence_count'])) { echo '</div>'; }
    else {
    if (isset($_REQUEST['word_search'])) { $word_search = $_REQUEST['word_search']; }
    else { $word_search = ''; }
    if (isset($_REQUEST['language'])) { $language = $_REQUEST['language']; }
    else { $language='all';}
    echo '<div class="blurb-box">';
    echo '<h2>Multi-Language Dictionary</h2>';
    echo '<form action="./index.php" method="post">';
    echo '<input type="text" name="word_search" value="'.$word_search.'" style="width:100%" placeholder="Enter a word" /><br />Language: ';
    term_dropdown('language',$language,$db);
    echo '<input type="submit" name="submit_search" value="Find" />';
    echo '</form>';
    if ($word_search != '') { search($word_search,$language,$db); }
    echo '</div>';
    $meta = get_all('meta','3',$db); echo nl2br($meta['content']);
    view_totals($db);
    }
    echo '</aside>';
}
?> 

