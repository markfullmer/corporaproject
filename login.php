<?php session_start();
include('variables/variables.php');
if (isset($_GET['logout'])) {
session_destroy();
header('Location: ./index.php');
}
if (isset($_POST['email']))
{
$pass = hash('ripemd160',$_POST['pass']);
$email = $_POST['email'];
$statement = $db->prepare("SELECT * FROM user WHERE email = :email AND password = :pass");
$statement->execute(array(':email' => $email,':pass' => $pass));
$row = $statement->fetch();
if (isset($row['id']))
{ 
$_SESSION['uid'] = $row['id'];
$_SESSION['name'] = $row['name'];
$_SESSION['email'] = $row['email'];
$p = unserialize($row['access']);
$_SESSION['permissions'] = array_keys($p);
header("Location: ./index.php");
}
}
if (empty($_SESSION['uid'])) { header('Location: ./index.php?logfail=1'); } ?>