    <footer>
        <div id="copyright">
        <?php 
        $meta = get_all('meta','1',$db); 
        echo $meta['content']; 
        //echo '<br />Loading time: '; echo microtime(true) - $now; 
        ?>
        </div>
        <a href="http://www.ched.gov.ph/"><img src="/img/ched-logo.png" width="100" align="left" /></a><a href="http://www.pnu.edu.ph/3ns/"><img src="/img/3ns-logo.jpg" width="100" align="left"  /></a><a href="http://www.lnu.edu.ph/"><img src="/img/lnu-logo.jpg" width="100" align="left"  /></a>
    </footer>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.10.1.min.js"><\/script>')</script>
    <script src="js/vendor/bootstrap.min.js"></script>
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
</body>
</html>