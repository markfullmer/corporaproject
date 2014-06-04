<?php  
include('../variables/variables.php');


// Get parameters from URL
$center_lat = 11.205345;
$center_lng = 124.902649;
$radius = 6;

// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

header("Content-type: text/xml");

$sql = 'SELECT name,language FROM word WHERE english_equivalent = :search';
  $statement = $db->prepare($sql);
  $statement->execute(array(':search' => $_GET['word']));
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
foreach ($result as $language => $words) {
  $coordinates = getlocation($language);
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


echo $dom->saveXML();

function getlocation($language) {
  global $db;
  $sql = 'SELECT id,name,latitude,longitude FROM language WHERE id = :id';
  $statement = $db->prepare($sql);
  $statement->execute(array(':id' => $language));
  return $statement->fetch();
}
?>

