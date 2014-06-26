    <footer>
        <?php 
        if ($_SESSION['uid'] == '1') {
            //ahah('wordcount_reset','630','10');
            //ahah('words_in_texts','675','10');
        }
        ?>
        <div id="copyright">
            <a href="http://www.ched.gov.ph/"><img src="./img/ched-logo.png"/></a>
            <a href="http://www.pnu.edu.ph/3ns/"><img src="./img/3ns-logo.jpg"/></a>
            <a href="http://www.lnu.edu.ph/"><img src="./img/lnu-logo.jpg" /></a>
        <?php 
        $meta = get_all('meta','1',$db); 
        echo $meta['content']; 
        echo '<br />Loading time: '; echo microtime(true) - $now; 
        ?>
        </div>
    </footer>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.1.min.js"><\/script>')</script>
    <script src="js/vendor/bootstrap.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
</body>
</html>