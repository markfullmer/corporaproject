<header>
    <nav>
        <form class="navbar-form pull-right" method="post" action="login.php">
            <?php
            if (isset($_SESSION['uid'])) { echo '<a href="login.php?logout=1">Log out</a></div>'; }
            else { echo '
            <input class="span2" type="text" name="email" placeholder="Email">
            <input class="span2" type="password" name="pass" placeholder="Password">
            <button type="submit" name="submit">Log in</button>
            '; } ?>
        </form>

    <h1><a href="index.php"><?php $meta = get_all('meta','2',$db); echo $meta['content']; ?></a></h1>
        <ul>
            <li class="active"><a href="./index.php">Home</a></li>
            <li><a href="./index.php?type=article&id=1">About</a></li>
            <li class="dropdown" tabindex="0"><a href="#">Word Lists</a>
                <ul class="dropdown-menu">
                    <li><a href="./index.php?type=word&id=all">Frequency Lists</a></li>
                    <li><a href="./index.php?type=semantic&id=all">Semantic Lists</a></li>
                    <li><a href="./index.php?type=statistical&id=all">Statistical Analysis</a></li>
                </ul>
            </li>
            <li><a href="./index.php?type=text&id=all">Texts</a></li>
            <li><a href="./index.php?type=article&id=3">Contact</a></li>
            <li><a href="./index.php?type=article&id=8">Documentation</a></li>
            <?php 
                if (isset($_SESSION['uid'])) { 
                    echo '<li class="dropdown" tabindex="0"><a href="#">Edit Website</a><ul class="dropdown-menu">';
                    $admin_menu = get_admin_menu($db);
                    foreach ($admin_menu as $key => $value) { 
                    if (in_array($key,$_SESSION['permissions'])) { echo '<li><a href="'.$value['url'].'">'.$value['name'].'</a></li>'; }
                    }

                } 
                ?>  
                </ul>
            </li>
        </ul>
    </nav>
</header>