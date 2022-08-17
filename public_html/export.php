<?php

require 'functions/functions.php';
require '../variables.php';
require 'includes/header.php';
require 'includes/nav.php';

echo 'exporting...';

function get_pos($term, $pos) {
  if (!empty($term)) {
    if (isset($pos[$term])) {
      return $pos[$term]['name'];
    }
  }
  if ($term == 0) {
    return '';
  }
  return $term;
}

function select_waray_export($db) {
  $pos = get_name('all', 'pos', $db);
  $sql = "SELECT * FROM word WHERE language=24 AND englishword=0 and blacklist=0 ORDER BY `count` DESC LIMIT 28000";
  $statement = $db->prepare($sql);
  $statement->execute([]);
  $names = [];
  $result[0] = ["word", "one_pos", "two_pos", "one_def", "one_ex"];
  $inc = 1;
  while ($row = $statement->fetch()) {
    if (!in_array($row['name'], $names)) {
      $names[] = $row['name'];
      $inc++;
      $key = $row['id'];
      $result[$inc]['name'] = $row['name'];
      $result[$inc]['one_pos'] = get_pos($row['pos'], $pos);
      $result[$inc]['two_pos'] = get_pos($row['postwo'], $pos);
      $result[$inc]['one_def'] = $row['definition'];
      $result[$inc]['one_ex'] = $row['sample_sentence'];
    }
  }
  if (isset($result)) {
    print_r($pos);
    echo '<pre>';
    print_r($result);
    echo '</pre>';
    $fp = fopen('export.xls', 'a+');
    foreach ($result as $key => $value) {
      // array_walk($value, 'cleanData');
      fputcsv($fp, $value);
    }
    fclose($fp);
  }
}
select_waray_export($db);
die();


$result = select_frequent_words($_GET['language'], $values['offset'], 10, 'count', 'no', 'no', $db);

$header = ["Word", "Count", "Part(s) of Speech", "Meaning", "Sample Sentence"];
    foreach ($result as $key => $value) {
      if ($key > 1000) {
        continue;
      }
      $pos_array = [];
      if ($value['pos'] != 0) {
        $one = $value['pos'];
        $pos_array[] = $pos[$one]['name'];
      }
      if ($value['postwo'] != 0) {
        $two = $value['postwo'];
        $pos_array[] = $pos[$two]['name'];
      }
      $parts_of_speech = join('/ ', $pos_array);
      $altered[$key]['name'] = $value['name'];
      $altered[$key]['count'] = $value['count'];
      $altered[$key]['pos'] = $parts_of_speech;
      $altered[$key]['definition'] = $value['definition'];
      $altered[$key]['sample'] = $value['sample_sentence'];
    }

    // $fp = fopen('export.xls', 'a+');
    // foreach ($altered as $key => $value) {
    //   array_walk($value, 'cleanData');
    //   fputcsv($fp, $value);
    // }
    // fclose($fp);
