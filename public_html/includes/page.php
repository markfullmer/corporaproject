<?php

/**
 * @file
 */

$data['name'] = '';
if ($type == 'semantic' && $id != 'all') {
  $data['name'] = select_single_value('domain', $id, 'name', $db);
}
if ($type == 'readability') {
  $data['name'] = 'Readability Instrument';
  $id = 'all';
}
if ($type == 'semantic' && $id == 'all') {
  $data['name'] = 'Semantic Domains';
}
if ($type == 'statistical') {
  $data['name'] = 'Statistical Analysis';
}
if ($type == 'map') {
  $data['name'] = 'Map';
  $id = 'all';
}
if ($type == 'languagesimilarity') {
  $data['name'] = 'LanguageSimilarity';
  $id = 'all';
}
$name = strtolower((string) $data['name']);
$name = ucwords($name);
if ($type == 'text' && $id != 'all') {
  if (isset($_SESSION['permissions'])) {
    $lang = select_single_value('text', $id, 'language', $db);
    if (check_language_permission($lang, $db)) {
      $name .= ' (<a href="./edit.php?type=text&id=' . $id . '">edit</a>)';
    }
  }
}
if ($type == 'article' && 'id' != 'all') {
  if (check_permissions_single('7', $db)) {
    $name .= ' (<a href="./edit.php?type=article&id=' . $id . '">edit</a>)';
  }
}


if (isset($_REQUEST['message'])) {
  $messageid = $_REQUEST['message'];
  $message = get_name($messageid, 'messages', $db);
  echo '<div class="spaced-box">' . $message[$messageid]['name'] . '</div>';
}
echo '<article';
if ($id == 'all') {
  echo ' class="all"';
}
echo '>';
?>
        <header>
        <h2 id="main-content"><?php echo $name; ?>
        </h2>
        <?php if (isset($data['author'])) {
          $author = strtolower((string) $data['author']);
          $author = ucwords($author);
          $author = $author;
          echo '<p>By ' . $author . '<p>';
        } ?>
        <?php if (isset($data['year'])) {
          echo '<p>Published: ' . $data['year'] . '<p>';
        } ?>
        <?php if (isset($data['genre'])) {
          '<p>Genre: ' . select_single_value('genre', $data['genre'], 'name', $db) . '<p>';
        } ?>
        </header>
<?php
if ($type == 'map') {
  echo map();
}
if ($type == 'languagesimilarity') {
  include 'classes/LanguageSimilarity.php';
  $similarity = new languageSimilarity();
  if (isset($_REQUEST['submit'])) {
    $similarity->showResults();
  }
  elseif (isset($_REQUEST['languages'])) {
    $similarity->compareLanguages();
  }
  else {
    $similarity->showForm();
  }
  echo $similarity->results;
}
if ($type == 'sentence') {
  echo sentence_controller();
}
if ($type == 'statistical') {
  echo statistical_analysis_controller($db);
}
if ($id == 'all') {
  if ($type == 'readability') {
    readability_form($db);
  }
  if ($type == 'word') {
    if (check_permissions($permission = [3])) {
      echo word_list_controller($db);
    }
  }
  if ($type == 'semantic') {
    $domains = get_name('all', 'domain', $db);
    $num = 1;
    foreach ($domains as $key => $value) {
      echo $num . '. <a href="index.php?type=semantic&id=' . $key . '">' . $value['name'] . '</a><br />';
      $num++;
    }
  }
  if ($type == 'text') {
    $genres = get_name('all', 'genre', $db);
    $results = select_single_value('meta', '7', 'content', $db);
    if (isset($_REQUEST['language'])) {
      $language = $_REQUEST['language'];
    }
    else {
      $language = '24';
    }
    if ($language == 'all') {
      $l = get_name('all', 'language', $db);
      $l_keys = array_keys($l);
      $l_join = join(',', $l_keys);
      $sql = 'SELECT id FROM text WHERE language IN ("' . $l_join . '")';
      $result = $db->query($sql)->fetchAll();
      $total_texts = is_countable($result) ? count($result) : 0;
    }
    else {
      $total_texts = count_values('text', 'language', $language, $db);
    }
    if ($total_texts < $results) {
      $limit = $total_texts;
      $offset = 0;
    }
    else {
      $limit = $results;
    }
    if (isset($_REQUEST['submit_inc'])) {
      if ($limit + $_REQUEST['offset'] >= $total_texts) {
        $limit = $total_texts - $_REQUEST['offset'];
      }
      else {
        $limit = $results;
      }
      $offset = $_REQUEST['offset'];
    }
    else {
      $offset = 0;
      $limit = $results;
    }
    $start = $offset + 1;
    $remaining = $total_texts - $offset - $limit;
    if (isset($_REQUEST['genre'])) {
      $genre = $_REQUEST['genre'];
    }
    else {
      $genre = 'all';
    }
    if (isset($_REQUEST['order'])) {
      $order = $_REQUEST['order'];
    }
    else {
      $order = 'name';
    }
    if (isset($_REQUEST['readability'])) {
      $readability = $_REQUEST['readability'];
    }
    else {
      $readability = '';
    }
    $filter = '';
    $end = $offset + $limit;
    $view = select_texts($language, $genre, $offset, $limit, $order, $readability, $filter, $db);
    $offsetplus = $offset + $view['count'];
    if ($remaining > $results) {
      $remaining = $results;
    }
    echo '<form action="index.php?type=text" method="post">';
    term_dropdown('language', $language, $db);
    term_dropdown('genre', $genre, $db);
    print_grade_levels($readability, $language, $db);
    print_order_filter($order);
    echo '<input type="submit" value="Filter" name="submit" />';
    echo '</select><input type="hidden" name="id" value="all" />';
    echo '<input type="hidden" name="offset" value="' . $offsetplus . '" />';

    if ($total_texts > 0) {
      if ($end <= 100) {
        $end = $view['count'];
      }
      if ($offset == 0) {
        echo ' Results ' . $start . '-' . $end . ' of ' . $view['total'] . ' results';
      }
      else {
        echo ' Results ' . $start . '-' . $end;
      }
      if ($remaining > 0) {
        echo ' <input type="submit" value="Next ' . $remaining . ' results" name="submit_inc" />';
      }
    }
    echo '</form>';
    if (isset($view)) {
      echo '<table class="default"><tr><td>Title</td><td>Author</td><td>Genre</td><td>Grade Level</td><td>Words</td></tr>';
      foreach ($view as $key => $value) {
        if (isset($value['id'])) {
          $gen = $value['genre'];
          if (isset($genres[$gen]['name'])) {
            $genre = $genres[$gen]['name'];
          }
          else {
            $genre = 'Uncategorized';
          }
          $author = strtolower((string) $value['author']);
          $author = ucwords($author);
          $title = strtolower((string) $key);
          $title = ucwords($title);
          echo '<tr><td><a href="./index.php?type=text&id=' . $value['id'] . '">' . $title . '</a>';
          if (isset($_SESSION['permissions'])) {
            if (check_language_permission($value['language'], $db)) {
              echo ' (<a href="./edit.php?type=text&id=' . $value['id'] . '">edit</a>)';
            }
          }
          echo '</td><td>' . $author . '</td><td>' . $genre . '</td><td>' . $value['readability'] . '</td><td>' . $value['word_count'] . '</td></tr>';
        }
      }
      echo '</table>';
    }
  }
}
// Display individual semantic domain.
$language_array = get_name('all', 'language', $db);
foreach ($language_array as $key => $value) {
  if ($value['name'] != 'English') {
    $languages[] = $key;
  }
}
if ($type == 'semantic' && $id != 'all') {
  $results = get_all_semantic($id, $db);
  echo '<table class="default"><tr><td>English</td>';
  foreach ($languages as $language) {
    echo '<td>' . select_single_value('language', $language, 'name', $db) . '</td>';
  }
  echo '</tr>';
  foreach ($results as $key => $value) {
    echo '<tr /><td>' . $key . '</td>';
    foreach ($languages as $language) {
      echo '<td>';
      if (isset($value[$language])) {
        echo $value[$language];
      }
      else {
        echo '';
      }
      echo '</td>';
    }
    echo '</tr>';
  }
  echo '</table>';
}
// Content display for non table views.
if (isset($data['content'])) {
  $content = $data['content'];
  if ($type == 'text' && $frequent_mod == '1') {
    // // add language permission check
    $language = select_single_value('language', $data['language'], 'name', $db);
    $frequent_words = get_frequent_words($data['language'], $db);
    echo 'The highlighted words below are not among the 1,000 most frequent in ' . $language . '. To manually mark them as a common word, click on the word.
        These additions can be reviewed on the <a href="edit.php?type=language&id=' . $data['language'] . '">' . $language . '</a> language page.<hr />';
    print highlight_frequent('edit', $data['id'], $frequent_words, $content, $db);
  }
  else {
    if ($type == 'text') {
      echo '<a href="?type=text&id=' . $data['id'] . '&frequent_mod=1">Manually tag words in this document as frequent</a><br />';
    }
    echo nl2br((string) $content);
  }
}
?>
    </article>
<?php
if ($id != 'all' && $type != 'semantic') {
  echo '<aside>';
  if (isset($data['sentence_count'])) {
    echo '<div class="statistics"><h2>Text Statistics</h2><p>Sentences: ' . $data['sentence_count'] . '<p>';
  }
  if (isset($data['word_count'])) {
    echo '<p>Words: ' . $data['word_count'] . '<p>';
  }
  if (isset($data['words_per_sentence'])) {
    echo '<p>Words per sentence: ' . $data['words_per_sentence'] . '</p><p class="subtext">Shorter sentences indicate simpler reading level. Very simple texts have less than 10 words per sentence. Difficult texts can have more than 20.</p>';
  }
  if (isset($data['readability'])) {
    echo '<p>Grade Level: ' . $data['readability'] . '</p><p class="subtext">Gives approximate grade level, based on the percent of familiar words and and sentence length.</p>';
  }
  if (isset($data['sentence_count'])) {
    echo '</div>';
  }
  else {
    if (isset($_REQUEST['word_search'])) {
      $word_search = $_REQUEST['word_search'];
    }
    else {
      $word_search = '';
    }
    if (isset($_REQUEST['language'])) {
      $language = $_REQUEST['language'];
    }
    else {
      $language = 'all';
    }
    echo '<div class="blurb-box">';
    echo '<h2>Multi-Language Dictionary</h2>';
    echo '<form action="./index.php" method="post">';
    echo '<input type="text" id="search" name="word_search" value="' . $word_search . '" placeholder="Enter a word" /><br />Language: ';
    term_dropdown('language', $language, $db);
    echo '<input type="submit" name="submit_search" value="Find" />';
    echo '</form>';
    if ($word_search != '') {
      search($word_search, $language, $db);
    }
    echo '</div>';
    $meta = get_all('meta', '3', $db);
    echo nl2br((string) $meta['content']);
    view_totals($db);
  }
  echo '</aside>';
}
