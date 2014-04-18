<section>
  <article>
<?php $meta = get_all('meta','4',$db); echo nl2br($meta['content']); ?>
  </article>
     
</section>

<aside>
<?php
  /*
  if (isset($_SESSION['uid'])) { 
  $admin_menu = get_admin_menu($db);
        foreach ($admin_menu as $key => $value) { 
        if (in_array($key,$_SESSION['permissions'])) { echo '<li><a href="'.$value['url'].'">'.$value['name'].'</a></li>'; }
        }
    }
  */
$meta = get_all('meta','3',$db); echo nl2br($meta['content']);
// gets list of languages from taxonomy
$languages = get_name('all','language',$db);

echo '<table class="default"><tr><td>Language</td><td>Texts</td><td>Total Words</td><td>Distinct Words</td></tr>';
$stats = array();
foreach ($languages as $key => $value) 
{
$stats[$key]['language'] = $value['name'];
$stats[$key]['texts'] = 0;
$stats[$key]['total'] = 0;
$stats[$key]['distinct'] = 0;

// Get distinct words
$stats[$key]['distinct'] = select_single_value('language',$key,'distinct_words',$db);

// Get number of texts
$stats[$key]['texts'] = count_values('text','language',$key,$db);

// get total words in each language
$stats[$key]['total'] = select_single_value('language',$key,'total_words',$db);
}

function cmp($a, $b) {
    if ($a['total'] == $b['total']) {
        return 0;
    }
    return ($a['total'] > $b['total']) ? -1 : 1;
}

usort($stats, "cmp");
foreach ($stats as $key => $value) {
echo '<tr><td>'.$value['language'].'</td><td>'.number_format($value['texts']).'</td><td>'.number_format($value['total']).'</td><td>'.number_format($value['distinct']).'</td></tr>';
}
echo '</table>';
        ?>
</aside> 

