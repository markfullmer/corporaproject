<?php session_start();
$now = microtime(true); 
include('../functions/functions.php');
include('../variables/variables.php');
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php $meta = get_all('meta','2',$db); echo $meta['content']; ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../css/reset.css">
        <link rel="stylesheet" href="../css/styles.css">
        <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:600,400' rel='stylesheet' type='text/css'>
        <script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
    </head>
        <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
        <![endif]-->

        <!-- This code is taken from http://twitter.github.com/bootstrap/examples/hero.html -->
<header>
    <nav>
        <form class="navbar-form pull-right" method="post" action="../login.php">
            <?php
            if (isset($_SESSION['uid'])) { echo '<a href="../login.php?logout=1">Log out</a></div>'; }
            else { echo '
            <input class="span2" type="text" name="email" placeholder="Email">
            <input class="span2" type="password" name="pass" placeholder="Password">
            <button type="submit" name="submit">Log in</button>
            '; } ?>
        </form>

    <h1><a href="../index.php"><?php $meta = get_all('meta','2',$db); echo $meta['content']; ?></a></h1>
        <ul>
            <li class="active"><a href="../index.php">Home</a></li>
            <li><a href="../index.php?type=article&id=1">About</a></li>
            <li><a href="../index.php?type=word&id=all">Words</a></li>
            <li><a href="../index.php?type=text&id=all">Texts</a></li>
            <li><a href="../index.php?type=article&id=3">Contact</a></li>
            <?php 
                if (isset($_SESSION['uid'])) { 
                    echo '<li class="dropdown" tabindex="0"><a href="#">Edit Website</a><ul class="dropdown-menu">';
                    $admin_menu = get_admin_menu($db);
                    foreach ($admin_menu as $key => $value) { 
                    if (in_array($key,$_SESSION['permissions'])) { echo '<li><a href="../'.$value['url'].'">'.$value['name'].'</a></li>'; }
                    }
                } 
                ?>  
                </ul>
            </li>
        </ul>
    </nav>
</header>
<?php
function readlastline($file)
{
       $linecontent = " ";
       $contents = file($file);
       $linenumber = sizeof($contents)-1;
       $linecontent = $contents[$linenumber];
       unset($contents,$linenumber);
       return $linecontent;
}
$file = 'save.log';
$last = readlastline($file);
$date = date('F j, Y',strtotime($last));
echo '<div class="spaced-box">';
echo '<h1>Backup this site</h1>';
echo '<form action="backup.php" method="POST">';
echo 'You last saved this website on '.$date;
echo '<br /><input type="submit" name="submit" value="Save" />';
echo '</div>';
echo '<div class="spaced-box">';
echo '<h1>Restore this site to a previous version</h1>';
echo '<p><input type="radio" name="restore" value="saved" /> Restore to the version saved on '.$date;
echo '<br /><span class="subtext">All data you entered after '.$date.' will be lost</span></p>';
echo '<p><input type="radio" name="restore" value="original" /> Remove all data you created and revert to the original website';
echo '<br /><span class="subtext">This will delete all texts & words you entered, but keep original data like parts of speech and genres.</span></p>';
echo '<br />To confirm you really want to do this, type "YES" in the box: <input type="text" name="confirm" />';
echo '<br /><input type="submit" name="submit" value="Restore" />';
echo '</div>';
echo '</form>';
include('../includes/footer.php'); 
?>