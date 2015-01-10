<?php

class LanguageSimilarity {
  var $word;
  var $results;
  var $rendered;

  function __construct() {
  }

  public function showResults() {
    if($_REQUEST['one'] != '' and $_REQUEST['two'] != '') {
      $one = htmlspecialchars($_REQUEST['one']);
      $two = htmlspecialchars($_REQUEST['two']);
      similar_text($one,$two,$graphemic);
      $meta_one=metaphone($one);
      $meta_two=metaphone($two);
      similar_text($meta_one,$meta_two,$phonemic);
      $this->results = '<b>Graphemic similarity:</b> ' . $graphemic . '<br />';
      $this->results .= 'Percentile representation of the number of changes that need to be
      made to individual letters to make the two strings identical.<br />';
      $this->results .= '<b>Phonemic similarity:</b> ' . $phonemic . '<br />';
      $this->results .= 'Percentile representation of the number of changes that need to be
      made to individual letters to make the two strings sound identical.<br />';
      $this->results .='<br /><a href="/index.php?type=languagesimilarity">Back to input form</a>';
    }
    else {
      $this->results = 'You need to enter a word in both fields. <a href="/index.php?type=languagesimilarity">Try again?</a>';
    }
  }

  public function showform() {
    $form = '<form action="index.php?type=languagesimilarity" method="post">
      <textarea name="one" placeholder="Word or Text to compare" rows="4" cols="50"></textarea>
      <textarea name="two" placeholder="Word or Text to compare" rows="4" cols="50"></textarea>
      <br /><input type="submit" name="submit" value ="Check Similarity" />
      </form>
      ';
    $explanation = 'The above form calculate the graphemic and phonemic similarity of two words or texts. The table below shows various word similarity to the word "BABAYI". The computations for these algorithms are based on the <a href="http://php.net/manual/en/function.metaphone.php">metaphone()</a> and <a href="http://php.net/manual/en/function.similar-text.php">similar_text()</a> functions.
    <table class="default"><tr><th></th><th>Graphemic Similarity</th><th>Phonemic Similiarity</th></tr>
    <tr><th>BABAYE (Alternate Spelling)</th><td>83</td><td>100</td></tr>
    <tr><th>BABAE (Tagalog)</th><td>72</td><td>80</td></tr>
    <tr><th>KABABAYEN (Plural)</th><td>66</td><td>75</td></tr>
    <tr><th>DANDA (Inabaknon)</th><td>36</td><td>0</td></tr>
    <tr><th>FEMALE (English)</th><td>16</td><td>0</td></tr></table>';
    $this->results = $form . $explanation;
  }

  public function compareLanguages() {
    global $db;
    $sql = 'SELECT * FROM word WHERE domain != 0';
    $statement = $db->prepare($sql);
    $statement->execute(array());
    while ($row = $statement->fetch()) {
        $results{$row['english_equivalent']}[$row['language']] = $row['name'];
    }
    foreach ($results as $key => $row) {
      if (isset($row[24]) && isset($row[26]) && isset($row[28]) && isset($row[27])) {
        $complete[$key] = $row;
      }
    }
    foreach ($complete as $eng => $set) {
      similar_text($set['24'],$set['26'],$percent);
      $stats['wt'][] = $percent;
      similar_text($set['26'],$set['24'],$percent);
      $stats['tw'][] = $percent;
      similar_text($set['24'],$set['27'],$percent);
      $stats['ws'][] = $percent;
      similar_text($set['27'],$set['24'],$percent);
      $stats['sw'][] = $percent;
      similar_text($set['24'],$set['28'],$percent);
      $stats['wi'][] = $percent;
      similar_text($set['28'],$set['24'],$percent);
      $stats['iw'][] = $percent;
      similar_text($set['26'],$set['27'],$percent);
      $stats['ts'][] = $percent;
      similar_text($set['27'],$set['26'],$percent);
      $stats['st'][] = $percent;
      similar_text($set['26'],$set['28'],$percent);
      $stats['ti'][] = $percent;
      similar_text($set['28'],$set['26'],$percent);
      $stats['it'][] = $percent;
      similar_text($set['27'],$set['28'],$percent);
      $stats['si'][] = $percent;
      similar_text($set['28'],$set['27'],$percent);
      $stats['is'][] = $percent;
      similar_text($eng,$set['26'],$percent);
      $stats['et'][] = $percent;
      similar_text($eng,$set['24'],$percent);
      $stats['ew'][] = $percent;
      similar_text($eng,$set['27'],$percent);
      $stats['es'][] = $percent;
      similar_text($eng,$set['28'],$percent);
      $stats['ei'][] = $percent;
    }
    $tw = number_format(array_sum($stats['tw'])/1943,2);
    $wt = number_format(array_sum($stats['wt'])/1943,2);
    $ws = number_format(array_sum($stats['ws'])/1943,2);
    $sw = number_format(array_sum($stats['sw'])/1943,2);
    $wi = number_format(array_sum($stats['wi'])/1943,2);
    $iw = number_format(array_sum($stats['iw'])/1943,2);
    $ts = number_format(array_sum($stats['ts'])/1943,2);
    $st = number_format(array_sum($stats['st'])/1943,2);
    $ti = number_format(array_sum($stats['ti'])/1943,2);
    $it = number_format(array_sum($stats['it'])/1943,2);
    $si = number_format(array_sum($stats['si'])/1943,2);
    $is = number_format(array_sum($stats['is'])/1943,2);

    $et = number_format(array_sum($stats['et'])/1943,2);
    $ew = number_format(array_sum($stats['ew'])/1943,2);
    $es = number_format(array_sum($stats['es'])/1943,2);
    $ei = number_format(array_sum($stats['ei'])/1943,2);
    echo '<table class="default"><tr><th></th><th>Tagalog</th><th>Waray</th><th>Sugbuanon</th><th>Inabaknon</th><th>English</th></tr>';
    echo '<tr><th>Tagalog</th><td></td><td>'.$tw.'</td><td>'.$ts.'</td><td>'.$ti.'</td><td>'.$et.'</td></tr>';
    echo '<tr><th>Waray</th><td>'.$wt.'</td><td></td><td>'.$ws.'</td><td>'.$wi.'</td><td>'.$ew.'</td></tr>';
    echo '<tr><th>Sugbuanon</th><td>'.$st.'</td><td>'.$sw.'</td><td></td><td>'.$si.'</td><td>'.$es.'</td></tr>';
    echo '<tr><th>Inabaknon</th><td>'.$it.'</td><td>'.$iw.'</td><td>'.$is.'</td><td></td><td>'.$ei.'</td></tr></table>';

  }

  public function getWords() {
    global $db;
    $sql = 'SELECT name,language FROM word WHERE english_equivalent = :search';
    $statement = $db->prepare($sql);
    $statement->execute(array(':search' => $this->word));
    $result = array();
    while ($row = $statement->fetch()) {
      $language = $row['language'];
      if (empty($result[$language])) {
        $result[$language] = array();
      }
      if (!in_array($row['name'],$result[$language])) {
        $result[$language][] = $row['name'];
      }
    }
  return $result;
  }

  public function renderResults() {
    // Start XML file, create parent node
    $dom = new DOMDocument("1.0");
    $node = $dom->createElement("markers");
    $parnode = $dom->appendChild($node);
    foreach ($this->results as $language => $words) {
      $coordinates = $this->getlocation($language);
      if (isset($coordinates['latitude'])) {
        $values = implode(', ',$words);
        $node = $dom->createElement("marker");
        $newnode = $parnode->appendChild($node);
        $newnode->setAttribute("name", $values);
        $newnode->setAttribute("address", $coordinates['name']);
        $newnode->setAttribute("lat", $coordinates['latitude']);
        $newnode->setAttribute("lng", $coordinates['longitude']);
        $newnode->setAttribute("distance", 5);
      }
    }
    return $dom->saveXML();
  }

  public function getlocation($language) {
    global $db;
    $sql = 'SELECT id,name,latitude,longitude FROM language WHERE id = :id';
    $statement = $db->prepare($sql);
    $statement->execute(array(':id' => $language));
    return $statement->fetch();
  }
}
?>
