<?php

/**
 *
 */
class Map {
  public $word;
  public $results;
  public $rendered;

  /**
   *
   */
  public function __construct($word) {
    $this->word = $word;
    $this->results = $this->getWords();
  }

  /**
   *
   */
  public function getWords() {
    global $db;
    $sql = 'SELECT name,language FROM word WHERE english_equivalent = :search';
    $statement = $db->prepare($sql);
    $statement->execute([':search' => $this->word]);
    $result = [];
    while ($row = $statement->fetch()) {
      $language = $row['language'];
      if (empty($result[$language])) {
        $result[$language] = [];
      }
      if (!in_array($row['name'], $result[$language])) {
        $result[$language][] = $row['name'];
      }
    }
    return $result;
  }

  /**
   *
   */
  public function renderResults() {
    // Start XML file, create parent node.
    $dom = new DOMDocument("1.0");
    $node = $dom->createElement("markers");
    $parnode = $dom->appendChild($node);
    foreach ($this->results as $language => $words) {
      $coordinates = $this->getlocation($language);
      if (isset($coordinates['latitude'])) {
        $values = implode(', ', $words);
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

  /**
   *
   */
  public function getlocation($language) {
    global $db;
    $sql = 'SELECT id,name,latitude,longitude FROM language WHERE id = :id';
    $statement = $db->prepare($sql);
    $statement->execute([':id' => $language]);
    return $statement->fetch();
  }

}
