<?php

add_action('admin_menu', 'evc_stats_add_page', 40);
function evc_stats_add_page() {

  $page = add_submenu_page( 'evc', 'Анализ групп ВКонтакте', 'Анализ групп', 'activate_plugins', 'evc-stats', 'evc_stats_page' );
 
  add_action("load-$page", 'evc_stats_screen_options');
  add_action( 'admin_print_styles-' . $page, 'evc_stats_styles' );
}

function evc_stats_styles () {
  wp_register_style( 'evc-stats-style', plugins_url('css/style.css', __FILE__) );
  wp_enqueue_style( 'evc-stats-style' );
}

function evc_stats_screen_options() {
  $option = 'per_page';
  $args = array(
         'label' => 'Posts',
         'default' => 20,
         'option' => 'vkposts_per_page'
         );
  add_screen_option( $option, $args );
}

add_filter('set-screen-option', 'evc_stats_filter_screen_options', 10, 3);
function evc_stats_filter_screen_options($status, $option, $value) {
  if ( 'vkposts_per_page' == $option ) return $value;
  return $value;
}

// display the admin options page
function evc_stats_page() {
  $options = get_option('evc_vk_api_widgets');
	
?>
  <div class="wrap">
    <h2><?php _e('Анализ групп ВКонтакте', 'evc'); ?></h2>

<?php
    echo '<div class="updated"><p><a href = "http://ukraya.ru/46/easy-vkontakte-connect-1-0-avtoposting-na-stenu-gruppy-s-kartinkami-analiz-grupp-vkontakte">Руководство</a> по работе с модулем "<b>Анализ групп</b>" и <a href = "http://ukraya.ru/116/reshenie-problem-po-rabote-s-plaginom-easy-vkontakte-connect-evc-1-1">техническая поддержка</a>.</p></div>'; 
		evc_ad();
    
  //print__r($options);
  // Need Access Token
  if (!isset($options['site_access_token']) || empty($options['site_access_token'])){   
    echo '<div class="error"><p>Необходимо настроить API ВКонтакте. Откройте вкладку "<a href="'.admin_url('admin.php?page=evc-vk-api').'">Для виджетов</a>".</p></div>';
    return false;
  }  
  
  
  if (false === ($evc_vk_groups_visits = get_transient('evc_vk_groups_visits')))
    $vk_group = '30370632';
  else {
    arsort($evc_vk_groups_visits); 
    $vk_group = key($evc_vk_groups_visits); 
  }
  
  $vk_gdata = array();
  $vk_posts = '';  
  
  if ( isset($_REQUEST['vk_group']) && !empty($_REQUEST['vk_group']) ) {
    $vk_group = $_REQUEST['vk_group'];
  }
  
  $gid = evc_stats_get_group_id($vk_group);

  if (!is_array($gid) ) {
    
    $vk_groupa = evc_stats_get_group($gid);
    $vk_gdata['url'] = 'vk.com/'.$vk_groupa['screen_name'];
    
    $vk_posts = evc_stats_vkposts($gid);
    //print__r($vk_posts);
  }
  else
    return false;
  
  $g_description = wp_strip_all_tags($vk_groupa['description']);
  $g_description = str_replace(array("\r\n","\r","\n"),' ',$g_description); 
  if (isset($g_description) && !empty($g_description))
    $g_description = '<div class = "a02_summary">'.$g_description.'</div>';
  else 
    $g_description = '';
  
  $refresh_g_url = remove_query_arg( array('refresh_w', 'refresh','captcha_key', 'captcha_sid'), $_SERVER['REQUEST_URI'] );
  $refresh_g_url = add_query_arg( array('refresh_g' => 1), $refresh_g_url );
  
  ?>

<div class = "bootstrap-wpadmin" id="evc">

  <div class="media a02" id = "evc" >
    <a class="pull-left" href="<?php echo $vk_gdata['url']; ?>">
      <img class="media-object" src="<?php echo $vk_groupa['photo_medium']; ?>">
    </a>
    <div class="media-body ">
      <h4 class="media-heading"><?php echo $vk_groupa['name']; ?></h4>
      <?php echo $g_description; ?>
      <p><strong>Сайт:</strong> <a href = "http://<?php echo $vk_gdata['url']; ?>"><?php echo $vk_groupa['screen_name']; ?></a>
      <br/><strong>Подписчиков:</strong> <?php echo number_format($vk_groupa['members_count'], 0, '.', ' '); ?>
      <br/><small class = "muted">Refresh: <?php echo human_time_diff($vk_groupa['timestamp'], current_time('timestamp', 1)); ?> ago (<a href = "<?php echo $refresh_g_url; ?>">refresh</a>)</small></p>
    </div>
  </div>
  
<div class = "navbar_wrapper">
<div class="navbar" data-spy = "affix"  >
<div class="navbar-inner">
<div class="container">
                  
<ul class="nav evc-stats-options" data-option-key="sortBy">
  <li><a href="javascript:void(0)" class = "external totop" rel="tooltip" data-original-title="Наверх" data-placement="bottom" ><i class="icon-circle-arrow-up icon"></i></a></li>                  
  <li class="active" data-key="sortBy" data-option-value="date"><a data-value = "dates" href="javascript:void(0)" rel="tooltip" data-original-title="Сортировать по времени публикации" data-placement="bottom" ><i class="icon-time icon"></i></a></li>                    
  <li data-key="sortBy" data-option-value="likes"><a data-value = "likes" href="javascript:void(0)" rel="tooltip" data-original-title="Сортировать по лайкам" data-placement="bottom"><i class="icon-heart icon"></i></a></li>
  <li data-key="sortBy" data-option-value="reposts"><a data-value = "reposts" href="javascript:void(0)" data-placement="bottom" rel="tooltip" data-original-title="Сортировать по репостам"><i class="icon-bullhorn icon"></i></a></li>
  <li data-key="sortBy" data-option-value="comments"><a data-value = "comments" href="javascript:void(0)" data-placement="bottom" rel="tooltip" data-original-title="Сортировать по комментариям"><i class="icon-comment icon"></i></a></li>
                        
  <li class="divider-vertical"></li>

  <li data-key="sortAscending" ><a data-value = "true" href="javascript:void(0)" data-placement="bottom" rel="tooltip" data-original-title="Сортировать по возрастанию" ><i class="icon-arrow-down icon" ></i></a></li>
  <li data-key="sortAscending" class = "active"><a data-value = "false" href="javascript:void(0)" data-placement="bottom" rel="tooltip" data-original-title="Сортировать по убыванию"><i class="icon-arrow-up icon"></i></a></li>            

  <li class="divider-vertical"></li>
                        
  <li><a href="http://ukraya.ru/58/reshenie-problem-po-rabote-s-plaginom-easy-vkontakte-connect-evc-1-0" data-placement="bottom" rel="tooltip" data-original-title="Помощь" class = "external"><i class="icon-question-sign icon"></i></a></li>                      
</ul>

<ul class="nav pull-right">
  <li>
    <form class="navbar-form pull-left" method="get" action="">
      <input type="hidden" name="page" value="evc-stats">
      <div class="input-append">
        <input type="text" class="span3" name="vk_group" placeholder="url or name" value="<?php echo isset($vk_gdata['url']) ? $vk_gdata['url'] : ''; ?>">
        <button class="btn" type="submit"><i class="icon-arrow-right icon"></i></button>
      </div>
    </form>
  </li>
  <li class="dropdown">
    <a data-toggle="dropdown" class="dropdown-toggle" href="#">Все группы <b class="caret"></b></a>
    <ul class="dropdown-menu">
    <?php echo evc_stats_get_all_groups(); ?>
    </ul>
  </li>
</ul>

</div>
</div><!-- /navbar-inner -->
</div>
</div><!-- /navbar_wrapper -->
            
<?php
  if (is_numeric($gid)) {
    $refresh_w = evc_stats_get_group_posts_refresh_time($gid);
    if ($refresh_w) {
      $refresh_w_url = remove_query_arg( array('refresh_g', 'refresh', 'captcha_key', 'captcha_sid'), $_SERVER['REQUEST_URI'] );
      $refresh_w_url = add_query_arg( array('refresh_w' => 1), $refresh_w_url );
      $refresh_w = '<p><small class = "muted">Refresh: '.human_time_diff($refresh_w, current_time('timestamp', 1)).' ago (<a href = "'. $refresh_w_url.'">refresh</a>)</small></p>';
    }
    echo $refresh_w;
  }
?>            
            
<?php     
  echo $vk_posts;
?>
</div>

</div><!-- .wrap -->
<?php
}

function evc_stats_get_group_posts($owner_id, $captcha = array()) {

  $options = get_option('evc_vk_api_widgets');
  $time = current_time('timestamp', 1);
  
  $user = get_current_user_id();
  $screen = get_current_screen();
  $option = $screen->get_option('per_page', 'option');
   
  $per_page = get_user_meta($user, $option, true);
  if ( empty ( $per_page) || $per_page < 1 )
    $per_page = $screen->get_option( 'per_page', 'default' );

  $gid = $owner_id < 0 ? -1 * $owner_id : $owner_id;
  
  // Last Visit Date
  $evc_vk_groups_visits = get_transient('evc_vk_groups_visits');
  $evc_vk_groups_visits[$gid] = $time;  
  
  $evc_wall_posts_cache = get_transient( 'evc-w_' . $gid );   
  $refresh = (isset($_GET['refresh_w']) && $_GET['refresh_w']) ? true : false;
  
  if ( $evc_wall_posts_cache !== false && !$refresh) {
    set_transient( 'evc_vk_groups_visits', $evc_vk_groups_visits, YEAR_IN_SECONDS ); 
    return $evc_wall_posts_cache;
  }
    
  $params = array();
  $params = array(
    //'access_token' => $options['access_token'], //
    'access_token' => $options['site_access_token'], //
    'owner_id' => $owner_id < 0 ? $owner_id : -1 * $owner_id,
    'count' => $per_page,
    'extended' => 1,

  );
  $params = apply_filters('evc_vk_query', $params);     
 
  $query = http_build_query($params);

  $data = wp_remote_get(EVC_API_URL.'wall.get?'.$query, array('sslverify' => false));
 
  if (is_wp_error($data)) {
    echo evc_wp_error_handler($data, 'evc_stats_get_group_posts');
    return false;
  }
  
  // Remove emoticons / html entities //&#128522;
  $data['body'] = evc_removeEmoji($data['body']);
  $resp = json_decode($data['body'],true);
  
  if (isset($resp['error'])) {
    echo evc_vk_error_handler($resp, 'evc_stats_get_group_posts');
    return false; 
  }
  
  set_transient( 'evc_vk_groups_visits', $evc_vk_groups_visits, YEAR_IN_SECONDS );
  
  $evc_wall_posts_cache =  $resp['response'];
  $evc_wall_posts_cache['timestamp'] = $time;
  set_transient('evc-w_' . $gid, $evc_wall_posts_cache, 2 * HOUR_IN_SECONDS);
 
  return $resp['response'];
}

function evc_stats_get_group_posts_refresh_time ($gid) {
  $gid = $gid < 0 ? -1 * $gid : $gid;

  if ( false === ($evc_wall_posts_cache = get_transient('evc-w_' . $gid)) ) return false;
  return $evc_wall_posts_cache['timestamp'];
}


function evc_stats_vkposts ($gid) {
  $out = '';

  $wall_posts = evc_stats_get_group_posts($gid);

  $group = evc_stats_get_group($gid);
  $screen_name = $group ? $group['screen_name'] : '';
  
  //print__r($wall_posts);
  if (!$wall_posts) {
    return false; 
  }
  
  if (!isset($wall_posts['wall']) || empty($wall_posts['wall']))
    return '<div class = "">Записей не найдено.</div>';
     
  foreach($wall_posts['wall'] as $w){
    if (!is_array($w))
      continue; 
    $out .= evc_stats_the_vkpost($w, $wall_posts['groups'][0]['gid'], $screen_name);
  }  
  
  return '<div class = "vkposts">'.$out.'</div>';
}


function evc_stats_the_vkpost($w, $owner_id, $screen_name = '') {
  $content = !empty($w['text']) ? apply_filters('the_content',$w['text']) : '#' . $w['id'];
  
  $time = date("H:i", $w['date'] + ( get_option( 'gmt_offset' ) * 3600 ));
  $date = date("j F", $w['date'] + ( get_option( 'gmt_offset' ) * 3600 ));
  $url = !empty($screen_name) ? 'http://vk.com/'.$screen_name.'?w=wall-'.$owner_id . '_' . $w['id'] : 'http://vk.com/wall-'.$owner_id . '_' . $w['id'];
  
  if (isset($w['attachments'])) {
    foreach($w['attachments'] as $att) {
      
      if (isset($att['photo']) && $att['photo']) {
        if (isset($att['photo']['text']) && !empty($att['photo']['text']))
          $pm['photo_src'][] = array($att['photo']['src_big'], $att['photo']['text']);
        else
          $pm['photo_src'][] = $att['photo']['src_big'];
      }
        
      if (isset($att['video']) && $att['video'])
        $pm['photo_src'][] = $att['video']['image_big'];        
        
      if (isset($att['doc']) && $att['doc'])
        $pm['photo_src'][] = $att['doc']['thumb'];         
    }
    
    if (isset($pm['photo_src'])) { 
      $image = array_shift( $pm['photo_src']);    
      $image = is_array($image) ? $image[0] : $image;
    }
  }

  $pm['comments'] = $w['comments']['count'];
  $pm['likes'] = $w['likes']['count'];
  $pm['reposts'] = $w['reposts']['count']; 
  
  $out = '';
  
  $out .= '
  <div class = "a01 a01_wpcolor">
    <div class = "a01_wrap">';
  $out .= isset($image) ? '
      <div class = "a01_img"><img alt="" class="" src="'.$image.'"></div>' : '';    
  $out .= '
      <div class = "a01_info">
        <div class = "a01_summary">'.$content.'</div>
        <div class = "clear"><div class = "a01_stats">
          <span class = "likes"><i class="icon-heart icon-white"></i> '.$pm['likes'].'</span> <span class = "reposts"><i class="icon-bullhorn icon-white"></i> '.$pm['reposts'].'</span> <span class = "comments"><i class="icon-comment icon-white"></i> '.$pm['comments'].'</span>
        </div>
        <div class = "a01_meta">
          <a href = "'.$url.'">'.$time . '<br/><span class = "a01_date" data-date-gmt = "'.$w['date'].'" >'.$date.'</span><span class = "date hide" >'.$w['date'].'</span></a>
        </div></div>
      </div>
    </div>
  </div>';
  
  $out .= '';
  return $out;
}


function evc_stats_get_group_id($gurl) {
  $options = get_option('evc_vk_api_widgets');  
    
  $urla = explode ('/', $gurl);
  $group_screen_name = array_pop($urla);
  preg_match('/^(id|public|club|event)([0-9]+)/', $group_screen_name, $matches);
  if (!empty($matches[1]) && !empty($matches[2])) {
    $gid = ($matches[1] != 'id') ? (-1*$matches[2]) : $matches[2];    
    // If User, not Group, Event and etc.
    if ($gid > 0) return false;
    $group_screen_name = $gid;
  }
 
  if (is_numeric($gurl))
    $group_screen_name = $gurl < 0 ? -1 * $gurl : $gurl;
  if (is_numeric($group_screen_name))
    $group_screen_name = $group_screen_name < 0 ? -1 * $group_screen_name : $group_screen_name;
  
  $refresh = (isset($_GET['refresh_g']) && $_GET['refresh_g']) ? true : false;
  
  if (is_numeric($group_screen_name) && !$refresh) {
    $evc_vk_group = get_transient('evc-g_' . $group_screen_name);
    if ($evc_vk_group !== false) {
      $gid = -1 * $evc_vk_group['gid'];
      return $gid;
    }
  }
  
  // Refresh Group Info  
  $params = array();
  $params = array(
    //'access_token' => $options['access_token'],//
    'access_token' => $options['site_access_token'],//
    'gid' => $group_screen_name,
    'fields' => 'members_count,description'
  );
  
  $params = apply_filters('evc_vk_query', $params);   
   
  $query = http_build_query($params);
  $data = wp_remote_get(EVC_API_URL.'groups.getById?'.$query, array('sslverify' => false));
  
  if (is_wp_error($data)) {
    echo evc_wp_error_handler($data, 'evc_stats_get_group_id');
    return false;
  }
  
  $resp = json_decode($data['body'],true); 

  if (isset($resp['error'])) {
    echo evc_vk_error_handler($resp, 'evc_stats_get_group_id');
    return $resp; 
  }
  
  // Save Group Info 
  $evc_vk_group = $resp['response'][0];  
  $evc_vk_group['timestamp'] = current_time('timestamp', 1);
  set_transient('evc-g_' . $resp['response'][0]['gid'], $evc_vk_group, 24 * HOUR_IN_SECONDS);
   
  $gid = -1 * $resp['response'][0]['gid'];  
  
  return $gid;
}


function evc_stats_get_group($gid) {
  //$options = get_option('evc_options');  
    
  // Only Groups, not users
  $gid = $gid < 0 ? -1 * $gid : $gid;   
  if ( false === ($evc_vk_group = get_transient('evc-g_' . $gid)) ) return false;
  
  return $evc_vk_group;    
}

function evc_vk_error_handler ($e, $fn) {
  
  $out = '<div class = "bootstrap-wpadmin" style = "margin-top:20px;"><div class="alert alert-error">
  <strong>Error #'.$e['error']['error_code'].'</strong> '.$e['error']['error_msg'].' in <em>'.$fn.'</em>.
  </div>';
  
  if ($e['error']['error_code'] == 14) {
    $action_url = remove_query_arg( array('captcha_key', 'captcha_sid' ), $_SERVER['REQUEST_URI'] );
    $out .= '
<form class="" method="get" action="'.$action_url.'">
  <fieldset>
    <input type="hidden" name="page" value="evc-stats">
    <input type="hidden" name="captcha_sid" value="'.$e['error']['captcha_sid'].'">';
    
    $out .= isset($_GET['vk_group']) && !empty($_GET['vk_group']) ? '<input type="hidden" name="vk_group" value="'.$_GET['vk_group'].'">': '' ;
    
    $out .='<label><img src="'.$e['error']['captcha_img'].'" class="img-polaroid"></label>
    <input type="text" class="span2" name="captcha_key" value="">
    <span class="help-block">Введите текст с картинки, чтобы продолжить.</span>
    <button type="submit" class="btn">Submit</button>
  </fieldset>
</form>';

    $out .= '';  
  }
  
  $out .= '</div>';  
  
  return $out;
}


function evc_wp_error_handler ($e, $fn) {

  $out = '<div class = "bootstrap-wpadmin" style = "margin-top:20px;"><div class="alert alert-error">
  <strong>Error #'.$e->get_error_code().'</strong> '.$e->get_error_message().' in <em>'.$fn.'</em>.
  </div>';
  
  $out .= '</div>';  
  
  return $out;
}



function evc_stats_get_all_groups($count = 10) {
  //$options = get_option('evc_options');  
  $out = array();
  
  //$evc_vk_groups = get_transient('evc_vk_groups');
  //if (!$evc_vk_groups) return false;
  
  $evc_vk_groups_visits = get_transient('evc_vk_groups_visits');
  if (!$evc_vk_groups_visits) return false;
  
  arsort($evc_vk_groups_visits);  
  
  $i = 0;
  foreach ($evc_vk_groups_visits as $key => $val) {
    $key = $key < 0 ? -1 * $key : $key;
    if ( false === ($g = get_transient('evc-g_'.$key)) )
      continue;
      
    $out[] = '<li><a href="'.admin_url( 'admin.php?page=evc-stats&vk_group=').$g['gid'].'">'.$g['name'].'</a></li>';
    
    $i++;
    if ($i == $count) break;
  }
  
  return implode('', $out);
  
}

add_filter('evc_vk_query', 'evc_stats_vk_query');
function evc_stats_vk_query($params) {
    
  if (isset($_GET['captcha_sid']) && isset($_GET['captcha_key']) && !empty($_GET['captcha_sid']) && !empty($_GET['captcha_key']) ) {
    $p['captcha_sid'] = $_GET['captcha_sid'];
    $p['captcha_key'] = trim($_GET['captcha_key']);
    $params = wp_parse_args($p, $params);
  }
  return $params;
}

add_action('admin_bar_menu', 'evc_stats_admin_bar_group_analytics', 99);
function evc_stats_admin_bar_group_analytics () {
  global $wp_admin_bar;
  
  // Don't show for logged out users.
  if ( ! is_user_logged_in() || !current_user_can('publish_posts') )
    return;  
      
  $wp_admin_bar->add_menu( array(
    'id'    => 'evc_stats',
    'title' => 'Анализ групп ВКонтакте',
    'href'  => admin_url( 'admin.php?page=evc-stats' ),
    'meta'  => array(
      'title' => __('Анализ групп ВКонтакте'),
    ),
  ) );

    $wp_admin_bar->add_menu( array(
      'parent' => 'evc_stats',
      'id'     => 'evc_stats_ukraya',
      'title'  => __('Об анализе групп'),
      'href'  => 'http://ukraya.ru/46/easy-vkontakte-connect-1-0-avtoposting-na-stenu-gruppy-s-kartinkami-analiz-grupp-vkontakte',
    ) );
 
  if (is_admin()) {
    $wp_admin_bar->add_menu( array(
      'parent'    => 'evc_stats',
      'id'        => 'evc_stats_page',
      'title'     => __('Анализировать группу'),
      'href'      => admin_url( 'admin.php?page=evc-stats' ),
    ) );
  }

}

/*
register_uninstall_hook(__FILE__, 'evc_uninstall');
function evc_uninstall() {
  delete_option('evc_options');
  delete_option('evc_vk_groups_visits');
  
}
*/

if (!function_exists('print__r')) {
function print__r ($data) {
  print '<pre>' . print_r($data, 1) . '</pre>';
}
}

// https://drupal.org/node/2043439
function evc_removeEmoji($text) {
  $clean_text = "";
  // Match Emoticons
  $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
  $clean_text = preg_replace($regexEmoticons, '', $text);
  // Match Miscellaneous Symbols and Pictographs
  $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
  $clean_text = preg_replace($regexSymbols, '', $clean_text);
  // Match Transport And Map Symbols
  $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
  $clean_text = preg_replace($regexTransport, '', $clean_text);
  // Match flags (iOS)
  $regexTransport = '/[\x{1F1E0}-\x{1F1FF}]/u';
  $clean_text = preg_replace($regexTransport, '', $clean_text);
  
  $clean_text = preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}][\x{FE00}-\x{FEFF}]?|[\x{00a9}][\x{FE00}-\x{FEFF}]?|[\x{203C}][\x{FE00}-\x{FEFF}]?|[\x{2047}][\x{FE00}-\x{FEFF}]?|[\x{2048}][\x{FE00}-\x{FEFF}]?|[\x{2049}][\x{FE00}-\x{FEFF}]?|[\x{3030}][\x{FE00}-\x{FEFF}]?|[\x{303D}][\x{FE00}-\x{FEFF}]?|[\x{2139}][\x{FE00}-\x{FEFF}]?|[\x{2122}][\x{FE00}-\x{FEFF}]?|[\x{3297}][\x{FE00}-\x{FEFF}]?|[\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $clean_text);  
    
  return $clean_text;
}