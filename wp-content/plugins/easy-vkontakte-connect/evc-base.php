<?php

// 2014_09_30

define('EVC_API_URL','https://api.vk.com/method/');


register_activation_hook(__FILE__,'evc_activate');
function evc_activate (){
  $use_smilies = get_option('use_smilies');
  if ($use_smilies)
    update_option('use_smilies', false);
}



// load all the subplugins
add_action('plugins_loaded','evc_plugin_loader');
function evc_plugin_loader() {
  include_once('evc-share.php');
  include_once('evc-stats.php');
  include_once('evc-comments.php');
  include_once('evc-comments-seo.php');
  include_once('evc-polls.php');
  include_once('evc-authorization.php');
  include_once('evc-lock.php');
  include_once('evc-buttons.php');
  include_once('evc-albums.php');
}

// add the theme page
add_action('admin_menu', 'evc_add_page');
function evc_add_page() {
  global $evc_options_page;

  add_menu_page( 'Easy VKontakte Connect', 'EVC', 'activate_plugins', 'evc', 'evc_vk_api_settings_page', plugins_url( 'img/vk-logo.png', __FILE__ ), '99.026345' );
}


// add the admin settings and such
add_action('admin_init', 'evc_admin_init');
function evc_admin_init(){
  global $evc_options_page;
  evc_activate();
  //$options = get_option('evc_options');

  // Isotope DEPRECATED because:
  // https://make.wordpress.org/plugins/2012/12/20/gpl-and-the-repository/
  //wp_enqueue_script('jquery.isotope', plugins_url('js/jquery.isotope.min.js' , __FILE__), array('jquery', 'jquery-masonry'));

  //wp_enqueue_script('evc', plugins_url('js/evc.js' , __FILE__), array('jquery', 'jquery-masonry', 'sticky-kit'), '1.0', true);
  //wp_enqueue_script('bootstrap', plugins_url('js/bootstrap.min.js' , __FILE__), array('jquery'), '2.2.2', true);
  //wp_enqueue_script('tinysort', plugins_url('js/jquery.tinysort.js' , __FILE__), array('jquery'), true);

}

function evc_admin_enqueue_scripts($hook) {
  if ( 'evc_page_evc-stats' != $hook )
    return;

  wp_enqueue_script('evc', plugins_url('js/evc.js' , __FILE__), array('jquery', 'jquery-masonry', 'sticky-kit'), '1.0', true);
  wp_enqueue_script('bootstrap', plugins_url('js/bootstrap.min.js' , __FILE__), array('jquery'), '2.2.2', true);
  wp_enqueue_script('tinysort', plugins_url('js/jquery.tinysort.js' , __FILE__), array('jquery'), true);
}
add_action( 'admin_enqueue_scripts', 'evc_admin_enqueue_scripts' );


class EVC_Walker_Checklist extends Walker {
  var $tree_type = 'category';
  var $db_fields = array ('parent' => 'parent', 'id' => 'term_id'); //TODO: decouple this

  function start_lvl(&$output, $depth = 0, $args = array()) {
    $indent = str_repeat("\t", $depth);
    $output .= "$indent<ul class='children'>\n";
  }

  function end_lvl(&$output, $depth = 0, $args = array()) {
    $indent = str_repeat("\t", $depth);
    $output .= "$indent</ul>\n";
  }

  function start_el(&$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
    extract($args);
    if ( empty($taxonomy) )
      $taxonomy = 'category';
    $_name = apply_filters('wpsapi_checklist_name', '');
    /*
    if ( $taxonomy == 'category' ) {
      $name = 'evc_options[post_category]';
      //$name = $_name . '[post_category]';
    }
    else {
      $name = 'evc_options[tax_input]['.$taxonomy.']';
      //$name = $_name . '[tax_input]['.$taxonomy.']';
    }
    */
    $name = $_name ;

    $class = in_array( $object->term_id, $popular_cats ) ? ' class="popular-category"' : '';
    $output .= "\n<li id='{$taxonomy}-{$object->term_id}'$class>" . '<label class="selectit"><input value="' . $object->term_id . '" type="checkbox" name="'.$name.'[]" id="in-'.$taxonomy.'-' . $object->term_id . '"' . checked( in_array( $object->term_id, $selected_cats ), true, false ) . disabled( empty( $args['disabled'] ), false, false ) . ' /> ' . esc_html( apply_filters('the_category', $object->name )) . '</label>';
  }

  function end_el(&$output, $object, $depth = 0, $args = array()) {
    $output .= "</li>\n";
  }
}



add_action('post_submitbox_misc_actions','evc_wall_post_check_box');
function evc_wall_post_check_box() {
  global $post;
  $options = get_option('evc_options');

  $captcha = get_post_meta($post->ID, '_evc_wall_post_captcha', true);
  if (isset($captcha) && !empty($captcha))
    $options['wall_post_flag'] = true;
  else
    $options['wall_post_flag'] = false;
?>
<div class="misc-pub-section">
<p><input type="checkbox" <?php checked($options['wall_post_flag'],true); ?> name="evc_wall_post" /> Опубликовать на стене ВКонтакте / <small>EVC</small></p>

<?php
  if (isset($captcha) && !empty($captcha)) {
?>
<p><span style = "color: #FF0000; border-bottom: 1px solid #FF0000;">Не опубликовано!</span>
<br/><img src = "<?php echo $captcha['img']; ?>" style = "margin:10px 0 3px;" />
<br/><input type="hidden" name="captcha_sid" value="<?php echo $captcha['sid']; ?>"><input type="text" value="" autocomplete="off" size="16" class="form-input-tip" name="captcha_key">
<br/>Введите текст с картинки, чтобы опубликовать запись ВКонтакте.</p>
<?php
  }

  do_action('evc_wall_post_check_box', $post);
?>
</div>
<?php
}

// this function prevents edits to existing posts from auto-posting
add_action( 'transition_post_status', 'evc_publish_auto_check', 10, 3 );
function evc_publish_auto_check( $new, $old, $post ) {
  $options = get_option( 'evc_autopost' );

  $filter = apply_filters( 'evc_autopost_filter', true, $new, $old, $post, $_POST );
  $force  = apply_filters( 'evc_autopost_filter_force', false, $new, $old, $post, $_POST );

  if ( ( $new == 'publish' && $old != 'publish' && $options['autopublish'] && ( isset( $options['post_types'] ) && ! empty( $options['post_types'] ) && in_array( $post->post_type, array_keys( $options['post_types'] ) ) ) && $filter ) ||
       ( isset( $_POST['evc_wall_post'] ) && $_POST['evc_wall_post'] && $new == 'publish' ) ||
       $force ) {

    if ( ! isset( $options['exclude_cats'] ) || empty( $options['exclude_cats'] ) || ! in_category( $options['exclude_cats'], $post ) ) {
      evc_wall_post( $post->ID, $post );
    }
  }
}



function evc_wall_post ($id, $post) {

	//$options = get_option('evc_options');
	$_options = evc_get_all_options( array(
		'evc_vk_api_autopost',
		'evc_autopost'
	) );

  $options = apply_filters('evc_autopost_options', $_options, $post->ID);

	$timeout = empty($options['timeout']) ? 5 : $options['timeout'];
	
	// Post to wall
	$m = array();
	preg_match_all( '/%([\w-]*)%/m', $options['message'], $mt, PREG_PATTERN_ORDER );

	if ( in_array( 'title', $mt[1] ) ) {
		$m['%title%'] = get_the_title( $post->ID );
		$m['%title%'] = strip_tags( $m['%title%'] );
		$m['%title%'] = html_entity_decode( $m['%title%'], ENT_QUOTES, 'UTF-8' );
		$m['%title%'] = htmlspecialchars_decode( $m['%title%'] );
	}

	if ( in_array( 'excerpt', $mt[1] ) ) {
		$m['%excerpt%'] = evc_make_excerpt( $post );
	}

	if ( in_array( 'teaser', $mt[1] ) ) {
		$m['%teaser%'] = evc_make_teaser( $post );
	}

	if ( in_array( 'teaserORexcerpt', $mt[1] ) ) {
		$m['%teaserORexcerpt%'] = evc_make_teaser_or_excerpt( $post );
	}

	if ( in_array( 'link', $mt[1] ) ) {
		$m['%link%'] = apply_filters( 'evc_publish_permalink', null, $post->ID );
	}

  $m = apply_filters('evc_autopost_mask', $m, $mt, $id, $post);

  $message = str_replace( array_keys($m), array_values($m), $options['message'] );

  $message = strip_tags($message);
  $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
  $message = htmlspecialchars_decode($message);

  $message = apply_filters('evc_autopost_message', $message, $id, $post);

  $permalink = $options['format']['add_link'] ? apply_filters('evc_publish_permalink', wp_get_shortlink($post->ID), $post->ID) : '';

  $attach = array();
  $images = evc_upload_photo($id, $post);
  if ($images['i'] && is_array($images['i']) )
    $attach[] = implode(',',$images['i']);
  if (!empty($permalink))
    $attach[] = $permalink;

  $params = array();

  $params = array(
    'access_token' => $options['access_token'],
    //'owner_id' => apply_filters('evc_wall_post_gid', '-' . $options['page_id'], $post),
    // 1: from group name; 0: from username
    'from_group' => isset($options['format']['from_group']) && !empty($options['format']['from_group']) ? 1 : 0,
    // add username to post?
    'signed' => isset($options['format']['signed']) && !empty($options['format']['signed']) ? 1 : 0,
    //'message' => $message,
    // if no attachments - 'message' is available
    //'attachments' => $attachments

  );

  if(!empty($message)) {
    $params ['message']= $message;
  }

  $params['owner_id'] = apply_filters('evc_wall_post_gid', $options['page_id'], $post);

  // CAPTCHA
  if (isset($_POST['captcha_sid']) && isset($_POST['captcha_key']) && !empty($_POST['captcha_sid']) && !empty($_POST['captcha_key']) ) {
    $params['captcha_sid'] = $_POST['captcha_sid'];
    $params['captcha_key'] = trim($_POST['captcha_key']);
  }

  if (!empty($attach))
    $params['attachments'] = implode(',', $attach);

  $params['attachments'] = apply_filters('evc_wall_post_attachments', $params['attachments'], $post);
  //evc_add_log('evc_wall_post. filter: evc_wall_post_attachments ' . $params['attachments'] );//
  $query = http_build_query($params);

  //$data = wp_remote_get(EVC_API_URL.'wall.post?'.$query, array('sslverify' => false));
  $args = array(
    'body' => $params,
    'sslverify' => false,
	  'timeout' => $timeout
  );
  $data = wp_remote_post(EVC_API_URL.'wall.post', $args);

  if (is_wp_error($data)) {
    evc_add_log('evc_wall_post: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());
    return false;
  }

  if (isset($data['response']) && isset($data['response']['code']) && $data['response']['code'] != 200 ){
    evc_add_log('evc_wall_post: RESPONSE ERROR. ' . $data['response']['code'] . ' '. $data['response']['message']);
    return false;
  }


  $resp = json_decode($data['body'],true);

  if ($resp['error']) {

    if (isset($resp['error']['error_code'])) {

      if ($resp['error']['error_code'] == 14) {
        $captcha = array(
          'sid' => $resp['error']['captcha_sid'],
          'img' => $resp['error']['captcha_img']
        );
        update_post_meta($post->ID, '_evc_wall_post_captcha', $captcha);

        if (!isset($options['error_email'])) {
          // Admin Notification
          $from = array('From:"'.get_option('blogname').'" <'.get_option('admin_email').'>');

          $message  = 'Запись ВКонтакте не опубликована' . "\r\n\r\n";
          $message  .= 'Требуется captcha (пройдите по ссылке): ' . admin_url('post.php?post='.$post->ID.'&action=edit') . "\r\n\r\n";
          $message  .= 'ВНИМАНИЕ!'."\r\n".'Это уведомление отправляется только ОДИН раз.'."\r\n".'Пока не введена captcha дальнейшая публикация записей ВКонтакте НЕВОЗМОЖНА.';
          $message  .= "\r\n\r\n".'Ответы на ваши вопросы можно найти тут: .';
          $message  .= "\r\n\r\n".'Спасибо, что выбрали Easy VKontakte Connect (EVC), пожалуй один из лучших плагинов интеграции с ВКонтактом )';

          @wp_mail(get_option('admin_email'), 'EVC: требуется captcha!', $message, $from);

          $options['error_email'] = current_time('timestamp', 1);

          update_option('evc_autopost', $options);
        }
        evc_add_log('evc_wall_post: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);
      }
      else
        evc_add_log('evc_wall_post: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);
    }
    else
      evc_add_log('evc_wall_post: VK Error. ' . $resp['error']);

    return false;
  }
  else {
    delete_post_meta($post->ID, '_evc_wall_post_captcha');
    unset($options['error_email']);
  }

  $options['page_url'] = $_options['page_url'];
  $options['page_id'] = $_options['page_id'];
  $options['page_screen_name'] = $_options['page_screen_name'];
  update_option('evc_autopost', $options);

  // Wall Post with link
  if ($resp['response']['processing'] || $resp['response']['post_id']) {
    update_post_meta($post->ID, '_evc_wall_post', date("Y-m-d H:i:s"));
    update_post_meta($post->ID, '_evc_wall_post_id', $resp['response']['post_id']);

    $vk_item_id = $params['owner_id'] . '_' . $resp['response']['post_id'];
    if (!update_post_meta($post->ID, 'vk_item_id', $vk_item_id ))
      add_post_meta($post->ID, 'vk_item_id', $vk_item_id, true);
  }

  do_action('evc_wall_post', $params, $resp, $post);
  return true;
}

function evc_upload_photo($id, $post) {

  //$options = get_option('evc_options');
  $options = evc_get_all_options(array(
    'evc_vk_api_autopost',
    'evc_autopost'
  ));

  $options = apply_filters('evc_autopost_options', $options, $post->ID);

	$timeout = empty($options['timeout']) ? 5 : $options['timeout'];

  if (!$options['upload_photo_count'])
    return false;
  if ($options['upload_photo_count'] > 5)
    $options['upload_photo_count'] = 5;

  // Find first 5 attached images
  $post_images = get_children( array(
    'post_parent' => $post->ID,
    'post_status' => 'inherit',
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'orderby' => 'menu_order id',
    'order' => 'ASC',
    'numberposts' => $options['upload_photo_count']
  ));

  $post_images = apply_filters('evc_autopost_upload_photo', $post_images, $post);

  // if no attached photo
  if (!$post_images || empty($post_images))
    return false;

  if ( $post_images ) {
    $i = 1;
    foreach($post_images as $image) {
      $att_id = is_object($image) ? $image->ID : $image;
      $path =  get_attached_file($att_id );

      if (version_compare( PHP_VERSION, '5.5', '>=' )){
        $images['file'.$i] = new CURLFile( $path);
      }
      else {
        $images['file'.$i] = '@' . $path;
      }

      $i++;
    }
  }

  $params = array(
    'access_token' => $options['access_token'],
    'gid' => abs($options['page_id']), // Removed minus sign
    //'group_id' => $options['page_id'], // Removed minus sign
    //'v' => '5.26'
  );

  // Get Wall Upload Server
  $query = http_build_query($params);
  $data = wp_remote_get(EVC_API_URL.'photos.getWallUploadServer?'.$query, array(
	  'sslverify' => false,
	  'timeout' => $timeout
  ));

  if (is_wp_error($data)) {
    evc_add_log('photos.getWallUploadServer: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());
    return $data->get_error_message();
  }

  $resp = json_decode($data['body'],true);
  //evc_add_log('photos.getWallUploadServer: $resp. ' . print_r($resp, 1)); //
  if (isset($resp['error'])) {
    if (isset($resp['error']['error_code']))
      evc_add_log('photos.getWallUploadServer: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);
    else
      evc_add_log('photos.getWallUploadServer: VK Error. ' . $resp['error']);
    return false;
  }

  if (!$resp['response']['upload_url'])
    return false;

  // Upload photo to server
  $curl = new Wp_Http_Curl();
  $data = $curl->request( $resp['response']['upload_url'], array(
    'body' => $images,
    //'body' => $photos,
    'method' => 'POST',
	  'timeout' => $timeout
  ));

  if (is_wp_error($data)) {
    evc_add_log('Upload Photos: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());
    return $data->get_error_message();
  }

  $resp = json_decode($data['body'],true);
  //evc_add_log('Upload Photos: $resp. ' . print_r($resp, 1)); //
  if (isset($resp['error'])) {
    if (isset($resp['error']['error_code']))
      evc_add_log('Upload Photos: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);
    else
      evc_add_log('Upload Photos: VK Error. ' . $resp['error']);
    return false;
  }

  if (!$resp['photo'])
    return false;



  // Save Wall Photo
  $params = array();
  $params = array(
    'access_token' => $options['access_token'],
    'gid' => abs($options['page_id']), // Removed minus sign
    //'group_id' => $options['page_id'], // Removed minus sign
    'server' => $resp['server'],
    'photo' => $resp['photo'],
    //'photo' => json_encode($resp['photo']),
    'hash' => $resp['hash'],
    //'v' => '5.26'
  );
  $query = http_build_query($params);
  //$data = wp_remote_get(EVC_API_URL.'photos.saveWallPhoto?'.$query, array('sslverify' => false));
  $args = array(
      'body' => $params,
      'sslverify' => false,
	  'timeout' => $timeout
  );
  $data = wp_remote_post(EVC_API_URL.'photos.saveWallPhoto', $args);

  if (is_wp_error($data)) {
    evc_add_log('photos.saveWallPhoto: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());
    return $data->get_error_message();
  }

  $resp = json_decode($data['body'],true);
  if (isset($resp['error'])) {
    if (isset($resp['error']['error_code']))
      evc_add_log('photos.saveWallPhoto: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);
    else
      evc_add_log('photos.saveWallPhoto: VK Error. ' . $resp['error']);
    return false;
  }

  if (!$resp['response'])
    return false;
  //print__r($resp);

  foreach($resp['response'] as $r)
    $attachments[] = $r['id'];

  return array('i' => $attachments);
}

function evc_make_teaser_or_excerpt( $post ) {

	$out = evc_make_teaser( $post );
	if ( empty( $out ) ) {
		$out = evc_make_excerpt( $post );
	}

	return $out;
}

function evc_make_teaser( $post ) {

	if ( ! empty( $post->post_excerpt ) ) {

		$text = $post->post_excerpt;
	} else if ( preg_match( '/<!--more(.*?)?-->/', $post->post_content, $matches ) ) {

		$content = explode( $matches[0], $post->post_content, 2 );
		$text    = $content[0];
	} else {

		$text = '';
	}

	if(!empty($text)) {
		$text = strip_shortcodes( $text );
		$text = str_replace( ']]>', ']]&gt;', $text );
		$text = wp_strip_all_tags( $text );
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
		$text = htmlspecialchars_decode( $text );
	}

	return $text;
}

// Main Idea from Otto, http://ottopress.com/wordpress-plugins/simple-facebook-connect/
function evc_make_excerpt($post) {
  $options = get_option('evc_autopost');

  if ( !empty($post->post_excerpt) )
    $text = $post->post_excerpt;
  else
    $text = $post->post_content;

  $text = strip_shortcodes( $text );
  // Need to try variants!!!
  /*
  // filter the excerpt or content, but without texturizing
  if ( empty($post->post_excerpt) ) {
    remove_filter( 'the_content', 'wptexturize' );
    remove_filter( 'the_content', 'evc_buttons_insert' );
    $text = apply_filters('the_content', $text);
    add_filter( 'the_content', 'evc_buttons_insert' );
    add_filter( 'the_content', 'wptexturize' );
  } else {
    remove_filter( 'the_excerpt', 'wptexturize' );
    $text = apply_filters('the_excerpt', $text);
    add_filter( 'the_excerpt', 'wptexturize' );
  }
  */
  $text = str_replace(']]>', ']]&gt;', $text);
  $text = wp_strip_all_tags($text);

  $excerpt_more = apply_filters('excerpt_more', '...');
  $excerpt_more = html_entity_decode($excerpt_more, ENT_QUOTES, 'UTF-8');
  $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
  $text = htmlspecialchars_decode($text);

  //$max = min(500, apply_filters('evc_excerpt_length', 500));

  if(!isset($options['excerpt_length']) || empty($options['excerpt_length']) || $options['excerpt_length'] == 0 ) {
    if (isset($options['excerpt_length_strings']) && !empty($options['excerpt_length_strings']) && $options['excerpt_length_strings'] )
      return evc_excerpt_strlen($text);
    else
      return $text;
  }

  //$max = !empty($options['excerpt_length']) ? $options['excerpt_length'] : 20;
  $max = $options['excerpt_length'];

  if ($max < 1) return ''; // nothing to send
  $words = explode(' ', $text);

  if (count($words) >= $max) {
    $words = array_slice($words, 0, $max);
    array_push ($words, $excerpt_more);
    $text = implode(' ', $words);
  }

  $text = evc_excerpt_strlen($text);

  return $text;
}


function evc_excerpt_strlen ($text, $max_strlen = 2000688) {
  $options = get_option('evc_autopost');

  if (isset($options['excerpt_length_strings']) && !empty($options['excerpt_length_strings'])) {
    $max_strlen = $options['excerpt_length_strings'] > $max_strlen ? $max_strlen : $options['excerpt_length_strings'];
  }

  if (strlen($text) >= $max_strlen) {
    $text = substr($text, 0, $max_strlen);
    $words = explode(' ', $text);
    array_pop($words); // strip last word

    $excerpt_more = apply_filters('excerpt_more', '...');
    $excerpt_more = html_entity_decode($excerpt_more, ENT_QUOTES, 'UTF-8');
    array_push ($words, $excerpt_more);

    $text = implode(' ', $words);
  }

  return $text;
}

/*
add_filter( 'evc_excerpt_length', 'theme_evc_excerpt_length' );
function theme_evc_excerpt_length() {
  return  20;
}
*/

// fix shortlink 
add_filter('evc_publish_permalink', 'evc_publish_shortlink_fix', 10, 2);
function evc_publish_shortlink_fix($link, $id) {
  //if (empty($link))
    $link = get_permalink($id);

  return $link;
}


add_action( 'save_post', 'evc_autopost_default_meta' );
function evc_autopost_default_meta($post_id) {

  $wall_post_id = get_post_meta($post_id, '_evc_wall_post_id', true);
  if ( $wall_post_id === 0 || $wall_post_id > 0 )
    return;

  if (!update_post_meta($post_id, '_evc_wall_post_id', 0 ))
    add_post_meta($post_id, '_evc_wall_post_id', 0, true);
}

// Comment if you do not need place shortcode in widget blocks
add_filter('widget_text', 'do_shortcode');