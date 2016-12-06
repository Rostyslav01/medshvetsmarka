<?php

if (!function_exists('evc_get_wpid_by_vkid')):
function evc_get_wpid_by_vkid ($id, $type = 'post') {
	return evc_get_wp_id_by_vk_id ($id, $type );
} 
endif;

function evc_get_wp_id_by_vk_id ($id, $type = 'post') {
  global $wpdb;
  
  $id_s = implode("','", (array)$id);  

  $res = $wpdb->get_results("
    SELECT ".$type."_id, meta_value
    FROM ".$wpdb->prefix.$type."meta
    WHERE meta_key = 'vk_item_id' AND meta_value IN ('$id_s')
  ");    

  if (empty($res))
    return false;
  
  $postfix = '_id';
  foreach($res as $r)
    $out[$r->meta_value] = $r->{$type . $postfix}; 
  
  return $out;
}
if (!function_exists('evc_update_post_metas')):
function evc_update_post_metas ($pm, $post_id) {
	evc_update_post_meta ($pm, $post_id);
} 
endif;
function evc_update_post_meta ($pm, $post_id) {
  if (!isset($pm) || empty($pm) )
    return false;
    
  foreach($pm as $pm_key => $pm_value)
    update_post_meta($post_id, $pm_key, $pm_value);  
}
 

if (!function_exists('evc_add_log')):
function evc_add_log ($event = '') {
  
  $gmt = current_time('timestamp', 1);
  // local time
  $date = gmdate('Y-m-d H:i:s', current_time('timestamp'));
  
  if (false === ($evc_log = get_transient('evc_log')))
    $evc_log = array();

  $out = $date . ' ' . $event;
  
 if (count($evc_log) > 100)
    $evc_log = array_slice($evc_log, -99, 99);  
  
  array_push($evc_log, $out);
  set_transient('evc_log', $evc_log, YEAR_IN_SECONDS);  
}
endif;
if (!function_exists('evc_get_log')):
function evc_get_log ($lines = 50) {
  if (false === ( $logs = get_transient('evc_log')) )
    return 'No logs yet.';
  
  if (is_array($logs)) {
    krsort($logs);    
    $logs = array_slice($logs, 0, $lines);
  }
  
  return print_r($logs,1);
}
endif;
if (!function_exists('evc_the_log')):
function evc_the_log ($lines = 50, $separator = '<br/>') {
  if (false === ( $logs = get_transient('evc_log')) )
    return 'No logs yet.';
  
  if (is_array($logs)) {
    krsort($logs);    
    $logs = array_slice($logs, 0, $lines);
  }
  
  $out = array();
  $i = 0;
  foreach($logs as $log) {
    if ($i%10 == 0)
      $out[] = '';
      
    $out[] = $log;
    $i++;
  }
   
  if (!empty($out))
    $out = implode($separator, $out);
  
  return $out;
}
endif;
/* 
*		USERS 
*/

function evc_add_user( $data ) {

	// Create User
	//$user_login = isset($data['screen_name']) && !empty($data['screen_name']) ? $data['screen_name'] : $data['id'];

	$user_login = $data['id'];
	$user_pass  = wp_generate_password( 8, false );
	$user_id    = wp_create_user( $user_login, $user_pass );
	if ( is_wp_error( $user_id ) ) {
		$out['error'] = 'WP Error. ' . $user_id->get_error_code() . ' ' . $user_id->get_error_message();
		evc_add_log( 'evc_add_user: WP Error. ' . $user_id->get_error_code() . ' ' . $user_id->get_error_message() );

		return false;
	}

	//print $user_id;
	// Update Userdata
	if ( ! function_exists( 'wp_update_user' ) ) {
		require_once( ABSPATH . WPINC . '/registration.php' );
	}

	$udata = array(
		'ID'           => $user_id,
		'user_url'     => 'http://vk.com/id' . $user_login,
		'display_name' => $data['first_name'] . ' ' . $data['last_name'],
		'first_name'   => $data['first_name'],
		'last_name'    => $data['last_name'],
	);

	if ( ! empty( $data['user_email'] ) ) {
		$udata['user_email'] = $data['user_email'];
	}

	$udata   = apply_filters( 'evc_add_user_update', $udata );
	$user_id = wp_update_user( $udata );
	//evc_add_log('Add User '.$user_id . ' ' . print_r($udata,1));
	// Save attachment
	$vk_user_photos = array(
		'photo_medium' => $data['photo_100']
	);
	if ( isset( $data['photo_max_orig'] ) ) {
		$vk_user_photos['photo_big'] = $data['photo_max_orig'];
	}

	foreach ( $vk_user_photos as $key => $value ) {
		add_user_meta( $user_id, 'vk_img', array(
			'img'   => $value,
			'title' => $data['first_name'] . ' ' . $data['last_name'],
			'key'   => $key
		) );
	}

	//update_user_meta($user_id, 'vk_user_id', $data['id']);
	update_user_meta( $user_id, 'vk_item_id', $data['id'] );

	// WooCommerce
	if ( class_exists( 'WooCommerce' ) ) {
		$temp = array();

		if(!empty($data['first_name'])) {
			$temp['shipping_first_name'] = $temp['billing_first_name'] = $data['first_name'];
		}
		if(!empty($data['last_name'])) {
			$temp['shipping_last_name'] = $temp['billing_last_name'] = $data['last_name'];
		}
		if(!empty($data['user_email'])) {
			$temp['billing_email'] = $data['user_email'];
		}

		if(!empty($temp)) {
			foreach ($temp as $key=>$value) {
				add_user_meta( $user_id, $key, $value, true );
			}
		}
	}
	// WooCommerce END

	return $user_id;
}   

add_action ('delete_user', 'evc_delete_user_photo');
function evc_delete_user_photo ($user_id) {
  $meta_keys = array('photo_medium', 'photo_big');
  foreach($meta_keys as $meta_key) {
    $aid = get_user_meta($user_id, $meta_key, true);
    wp_delete_attachment( $aid, true );
  }
  return true;
}

add_filter ('get_avatar', 'evc_get_user_photo', 10, 6);
function evc_get_user_photo($avatar, $id_or_email, $size, $default, $alt, $args) {
  //if (is_admin() && $_POST['action'] != 'evc_theme_get_post')
  //  return $avatar;
    global $pagenow;
	if ($pagenow == 'options-discussion.php')
        return $avatar;

  if(is_numeric($id_or_email))
    $user_id = $id_or_email;
  elseif(is_object($id_or_email)) {    
    if( !empty($id_or_email->user_id) )
      $user_id = $id_or_email->user_id;
    else
      return $avatar;
  }
  else {
    $user = get_user_by('email', $id_or_email);
    if ($user)
      $user_id = $user->ID;
    else
      return $avatar;
  }
  
  if (!isset($user) || !is_object($user))
    $user = get_user_by('id', $user_id);
  
  //$att_id = get_usermeta($user_id, 'photo_medium');
  $att_id = get_user_meta($user_id, 'photo_medium', true);
  //print__r($att_id);
  if (!$att_id)
    return $avatar; 

  $src = wp_get_attachment_image_src( $att_id, 'full' );
      
  $avatar  = '<img alt="'.$user->data->display_name.'" src="'.$src[0].'" class="avatar avatar-'.$size.' photo '.$args['class'] . '" height="'.$size.'" width="'.$size.'" />';

  return $avatar;
}

add_filter ('get_comment', 'evc_comment_author_filter');
function evc_comment_author_filter ($comment) {
  if (empty($comment->comment_author) && !empty($comment->user_id)) {
    $u = get_user_by('id', $comment->user_id);
    $comment->comment_author = $u->data->display_name;
  }
  return $comment;
}

if(!function_exists('pluralize')) :
  function pluralize($count, $singular, $plural = false, $pluralmore = false) {
  $last_digits = substr($count, -1);
  $last_two_digits = substr($count, -2);
    if ($last_two_digits < 10 || $last_two_digits > 20 ) {
      if ($last_digits == 1) return $singular;
      elseif (in_array ($last_digits, array(2, 3, 4)) ) return $plural;
    }
    return $pluralmore;
  }
endif;

/*
*   Images
*/

/*
'post' => array()$post, post_date

'file_name' => $file_name[$k],
'url' => $image_url[$k]
*/
if(!function_exists('evc_fetch_remote_file')) :
function evc_fetch_remote_file($args) {
  if (!empty($args))
    extract($args);

  //$post_date = date('Y-m-d H:i:s');
  $upload = wp_upload_dir();
  $upload = wp_upload_bits( $file_name, 0, '');

  if ( $upload['error'] )
    return new WP_Error( 'upload_dir_error', $upload['error'] );

  $headers = wp_get_http($url, $upload['file']);

  if ( !$headers ) {
    @unlink($upload['file']);
    return new WP_Error( 'import_file_error', __('Remote server did not respond', 'evc') );
  }

  if ( $headers['response'] != '200' ) {
    @unlink($upload['file']);
    return new WP_Error( 'import_file_error', sprintf(__('Remote server says: %1$d %2$s', 'evc'), $headers['response'], get_status_header_desc($headers['response']) ) );
  }
  elseif ( isset($headers['content-length']) && filesize($upload['file']) != $headers['content-length'] ) {
    @unlink($upload['file']);
    return new WP_Error( 'import_file_error', __('Remote file is incorrect size', 'evc') );
  }

  $max_size = (int)get_site_option('fileupload_maxk')*1024;

  // fileupload_maxk for wpmu compatibility 
  $file_size= filesize($upload['file']);

  if ( !empty($max_size) && $file_size > $max_size ) {
    @unlink($upload['file']);
    return new WP_Error( 'import_file_error', sprintf(__('Remote file is %1$d KB but limit is %2$d', 'evc'), $file_size/1024, $max_size/1024) );
  }

  // This check is for wpmu compatibility
  if ( function_exists('get_space_allowed') ) {
    $space_allowed = 1048576 * get_space_allowed();
    $space_used = get_dirsize( BLOGUPLOADDIR );
    $space_left = $space_allowed - $space_used;

    if ( $space_left < 0 ) {
      @unlink($upload['file']);
      return new WP_Error( 'not_enough_diskspace', sprintf(__('You have %1$d KB diskspace used but %2$d allowed.', 'evc'), $space_used/1024, $space_allowed/1024) );
    }
  }

  $upload['content-type'] = $headers['content-type'];
  return $upload;
}
endif;
// array($url, $title, $post_parent)
/*
$a = array(
	'img' => ''
);

*/
if(!function_exists('evc_save_remote_attachment')) :
function evc_save_remote_attachment ($a, $post_parent= null, $title = '', $obj = false) {    
  $options = get_option('evc_comments'); 
  
  // Create Img Filename
  $pi = pathinfo($a['img']);
  $filename = $pi['basename'];
  // print__r($pi);  
  // Create Img
  $params = array(
    //'post_date' => $post_date, 
    'file_name' => $filename,
    'url' => $a['img']
  );
  $img = evc_fetch_remote_file($params);   
  if ( is_wp_error($img)) {
    // print '<p>'. $img->get_error_message() . '</p>';
    return false;
  }
    
  $url = $img['url'];
  $type = $img['content-type'];
  $file = $img['file'];  
  
  $att= array(
    //'post_author' => $options['evc_comments_user_id'],
    'post_status'=>'publish', 
    'ping_status' => 'closed', 
    'guid'=> $url, 
    'post_mime_type'=>$type
  );
  
  if (isset($post_parent) && $obj != 'user') 
    $att['post_parent'] = $post_parent;  

  if (isset($post_parent) && $obj == 'user')
    $att['post_author'] = $post_parent;  
  
  if (isset($a['title'])) 
    $att['post_title'] = $a['title'];
  else
    $att['post_title'] = $title;
  
  if (isset($a['text'])) 
    $att['post_content'] = $a['text'];
    
  if (isset($a['description']))
    $att['post_content'] = $a['description'];
        
  $att = apply_filters('evc_save_remote_attachment', $att);  
  
  $att_ID= wp_insert_attachment($att);

  if ( !$att_ID ) {
    //print "<p>Can not create attachment for $img[file]</p>";
    return false;
  }
  
  if (!function_exists('wp_generate_attachment_metadata'))
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
  $attachment_metadata = wp_generate_attachment_metadata($att_ID, $file);
  
  wp_update_attachment_metadata($att_ID, $attachment_metadata);
  update_attached_file($att_ID, $file);  
  
  // Update Attachment Meta For POsts And Comments
  if (isset($a['type'])) {
    $meta = array(
      //'vk_item_id' => $a['id'],
      'vk_type' => $a['type'],
      'vk_owner_id' => $a['owner_id'],
      'vk_access_key' => $a['access_key'],
    );
  }
  if (isset($a['vk_player']))
    $meta['vk_player'] = $a['vk_player'];
  if (isset($a['duration']))
    $meta['vk_duration'] = $a['duration'];    
  
  if ( $obj != 'user' && isset($a['vk_item_id']) ) 
    $meta['vk_item_id'] = $a['vk_item_id'];
  
  if (isset($meta) && !empty($meta))    
    evc_update_post_metas($meta, $att_ID);     
  
  // Update Attachment Meta For Users
  if ($obj == 'user')
    update_user_meta($post_parent, $a['key'], $att_ID);    
  
  if (isset($a['key']) && $obj != 'user')
    update_post_meta($post_parent, $a['key'], $att_ID);    
  
  do_action('evc_save_remote_attachment_action', $a, $att_ID, $att, $obj);      
    
  return $att_ID;  
}
endif;

add_action( 'evc_save_remote_attachment_action', 'evc_set_post_thumbnail', 10, 4 );
if(!function_exists('evc_set_post_thumbnail')) :
function evc_set_post_thumbnail ($a, $att_id, $att, $obj) {
  $options = apply_filters('evc_set_post_thumbnail_options', array());//get_option('vkwpb_albums'); 
  if (isset($options['post_thumbnail']['on']) && $obj != 'user' && isset($att['post_parent']) && !has_post_thumbnail($att['post_parent'])) {
    set_post_thumbnail( $att['post_parent'], $att_id );
  }
}
endif;
if(!function_exists('evc_get_vk_imgs')) :
function evc_get_vk_imgs ($type = 'post', $limit = 10) {
  global $wpdb;
  
  $l = '';
  if ($limit)
    $l = "LIMIT ".$limit;
  
  $res = $wpdb->get_results("
    SELECT ".$type."_id, meta_value
    FROM ".$wpdb->prefix.$type."meta
    WHERE meta_key = 'vk_img'
    ORDER BY ".$type."_id ASC
    ".$l."
  ");    
  
  //evc_add_log('evc_get_vk_imgs:' . print_r($res,1)); 
  if (empty($res))
    return false;
  
  return $res;
}
endif;

add_action('wp_ajax_evc_refresh_vk_img', 'evc_refresh_vk_img_js');
if(!function_exists('evc_refresh_vk_img_js')) :
function evc_refresh_vk_img_js() {
  
  $r = evc_refresh_vk_img_all();
  
  if (isset($r['error']))
    $out['error'] = 'Error';
  else
    $out = $r;

  print json_encode($out);
  exit;    
}
endif;

if(!function_exists('evc_refresh_vk_img')) :
function evc_refresh_vk_img ($type, $limit = 50) {
  $postfix = '_id';
 
  $vk_imgs = evc_get_vk_imgs($type, $limit);
  //print__r ($vk_imgs);
  if (!$vk_imgs)
    return false;
  
  $i = 0;    
  foreach($vk_imgs as $vk_img) {
    //print__r(maybe_unserialize($vk_img->meta_value));
    $att_id = evc_save_remote_attachment( maybe_unserialize($vk_img->meta_value), $vk_img->{$type . $postfix}, '', $type );
    //print '$att_id = ' . $att_id;
    if ($att_id) {
      call_user_func('delete_' . $type .'_meta', $vk_img->{$type . $postfix}, 'vk_img', maybe_unserialize($vk_img->meta_value));
      $i++;
    }
  }
  return $i;  
}
endif;

if(!function_exists('evc_refresh_vk_img_all')) :
function evc_refresh_vk_img_all () {
  $options = get_option('evc_comments_pro'); 
  
  $ipost = evc_get_vk_imgs('post', 0);
  $iuser = evc_get_vk_imgs('user', 0);
  
  $ipost = !$ipost ? 0 : count($ipost);
  $iuser = !$iuser ? 0 : count($iuser);
  
  $r = 0;  
  $out = array();
  if (!isset($options['img_refresh']) || empty($options['img_refresh']) )
    $options['img_refresh'] = 10;
    
  $options['img_refresh'] = apply_filters('evc_img_refresh', $options['img_refresh']);

  if ($ipost) {
    $r = evc_refresh_vk_img('post', $options['img_refresh']);
    if (!$r) {
      $out['error'] = 'Error';
      $r = 0;
    }
  }
  else
    $ipost = 0;
  
  if ( $iuser && ( ($r && $r < $options['img_refresh']) || !$r ) ) {
    if ( ($r && $r < $options['img_refresh'])  )
      $r += evc_refresh_vk_img('user', $options['img_refresh'] - $r);
    elseif (!$r)
      $r = evc_refresh_vk_img('user', $options['img_refresh']);
    if (!$r) {
      $out['error'] = 'Error';
      $r = 0;
    }      
  }
   
  $out['refresh'] = $r;
  $out['left'] = $ipost + $iuser - $r;
  //$out['left'] = count((array)$ipost) .' ' . count((array)$iuser) . ' ' . $r;
  
  evc_add_log('evc_refresh_vk_img_all: Refresh: '.$out['refresh'].'. Left: ' . $out['left']. '.');
  
  return $out;  
}
endif;
  

function evc_vkapi_get_users ($params) {
  $options = get_option('evc_vk_api_widgets'); 
  
  // http://vk.com/developers.php?oid=-1&p=users.get    
  $default = array(
    'access_token' => $options['site_access_token'],
    //'user_ids' => $vk_user_id, // max: 1000; comma separated
    'fields' => apply_filters('evc_vkapi_get_users_fields', 'screen_name,sex,photo_100,photo_max_orig'),
    //uid,first_name,last_name,nickname,screen_name,sex,bdate,city,country,timezone,photo,photo_medium,photo_big,has_mobile,rate,contacts,education,online,counters  
    //'name_case' => ''
    'v' => '5.21'
  );
  $params = wp_parse_args($params, $default);
  $params = apply_filters('evc_vkapi_get_users', $params);  
  
  $query = http_build_query($params);
  $data = wp_remote_post(EVC_API_URL.'users.get?'.$query);
  
  //evc_add_log('evc_vk_get_users: VK Error. ' . print_r($params,1)); 

  if (is_wp_error($data)) {
    evc_add_log('evc_vkapi_get_users: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());
    return false;
  }
  
  $resp = json_decode($data['body'],true);
  
  if (isset($resp['error'])) {   
    if (isset($resp['error']['error_code']))
      evc_add_log('evc_vkapi_get_users: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']); 
    else
      evc_add_log('evc_vkapi_get_users: VK Error. ' . $resp['error']);           
    return false; 
  }   

  //evc_bridge_add_log(print_r($resp['response'],1));
  evc_add_log('evc_vkapi_get_users: VK API ');
  return $resp['response'];
}

function evc_vkapi_users_get_subscription ($params) {
  $options = get_option('evc_vk_api_widgets'); 
    
  $default = array(
    'access_token' => $options['site_access_token'],
    'user_id' => '',
    'extended' => '',
    'offset' => '',
    'count' => '',
    'fields' => '',
    'v' => '5.21'
  );
  $params = wp_parse_args($params, $default);
  $params = apply_filters('evc_vkapi_users_get_subscription', $params);  
  
  $query = http_build_query($params);
  $data = wp_remote_post(EVC_API_URL.'users.getSubscriptions?'.$query);
  
  //evc_add_log('evc_vk_get_users: VK Error. ' . print_r($params,1)); 

  if (is_wp_error($data)) {
    evc_add_log('evc_vkapi_users_get_subscription: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());
    return false;
  }
  
  $resp = json_decode($data['body'],true);
  
  if (isset($resp['error'])) {   
    if (isset($resp['error']['error_code']))
      evc_add_log('evc_vkapi_users_get_subscription: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']); 
    else
      evc_add_log('evc_vkapi_users_get_subscription: VK Error. ' . $resp['error']);           
    return false; 
  }   

  //evc_bridge_add_log(print_r($resp['response'],1));
  evc_add_log('evc_vkapi_users_get_subscription: VK API ');
  return $resp['response'];
}


function evc_vkapi( $params ) {

	$params['args'] = apply_filters( 'evc_vkapi_' . $params['method_str'], $params['args'] );

	$args = array(
		'body'      => $params['args'],
		'sslverify' => false
	);

	$data = wp_remote_post( EVC_API_URL . $params['method'], $args );

	if ( is_wp_error( $data ) ) {
		evc_add_log( $params['method_str'] . ': WP ERROR. ' . $data->get_error_code() . ' ' . $data->get_error_message() );

		return false;
	}

	if ( isset( $data['response'] ) && isset( $data['response']['code'] ) && $data['response']['code'] != 200 ) {
		evc_add_log( $params['method_str'] . ': RESPONSE ERROR. ' . $data['response']['code'] . ' ' . $data['response']['message'] );

		return false;
	}

	$data['body'] = evc_pro_remove_emoji( $data['body'] );
	$resp         = json_decode( $data['body'], true );

	if ( isset( $resp['error'] ) ) {
		if ( isset( $resp['error']['error_code'] ) ) {
			evc_add_log( $params['method_str'] . ': VK Error. ' . $resp['error']['error_code'] . ' ' . $resp['error']['error_msg'] );
		} else {
			evc_add_log( $params['method_str'] . ': VK Error. ' . $resp['error'] );
		}

		return false;
	}

	evc_add_log( $params['method_str'] . ': VK API ' );

	return $resp['response'];
}


function evc_vkapi_upload($params) {

	$params['args'] = apply_filters( 'evc_vkapi_' . $params['method_str'], $params['args'] );
	print $params['upload_url'];

	// Upload object to server
	$curl = new Wp_Http_Curl();
	$data = $curl->request( $params['upload_url'], array(
		'body' => $params['args'],
		'method' => 'POST',
		'headers' => array('Content-Type' => 'multipart/form-data')
	));

	return  evc_vkapi_handler ($params, $data);
}


function evc_vkapi_handler ($params, $data){

	if ( is_wp_error( $data ) ) {
		evc_add_log( $params['method_str'] . ': WP ERROR. ' . $data->get_error_code() . ' ' . $data->get_error_message() );
		return false;
	}

	if ( isset( $data['response'] ) && isset( $data['response']['code'] ) && $data['response']['code'] != 200 ) {
		evc_add_log( $params['method_str'] . ': RESPONSE ERROR. ' . $data['response']['code'] . ' ' . $data['response']['message'] );
		return false;
	}

	$data['body'] = evc_pro_remove_emoji( $data['body'] );
	$resp         = json_decode( $data['body'], true );

	if ( isset( $resp['error'] ) ) {
		if ( isset( $resp['error']['error_code'] ) ) {
			evc_add_log( $params['method_str'] . ': VK Error. ' . $resp['error']['error_code'] . ' ' . $resp['error']['error_msg'] );
		} else {
			evc_add_log( $params['method_str'] . ': VK Error. ' . $resp['error'] );
		}

		return false;
	}

	//evc_bridge_add_log(print_r($resp,1));
	evc_add_log( $params['method_str'] . ': VK API ' );

	return $resp;
}



function evc_vkapi_groups_is_member ($params) {
  $options = get_option('evc_vk_api_widgets'); 
  
  //http://vk.com/dev/groups.isMember  
  $default = array(
    'access_token' => $options['site_access_token'],
    //'group_id' => '', // id or screen_name
    //'user_id' => '',
    //'user_ids' => '',
    //'extended' => '', // 0 
    'v' => '5.23'
  );
  $params = wp_parse_args($params, $default);

  $res = evc_vkapi(array(
    'args'=>$params,
    'method'=>'groups.isMember',
    'method_str'=>'groups_is_member'
  ));

  return $res;
}

function evc_vkapi_resolve_screen_name ($params) {
	$options = evc_get_all_options( array(
		'evc_vk_api_autopost',
		'evc_vk_api_widgets'
	) );

	if ( ! empty( $options['site_access_token'] ) ) {
		$access_token = $options['site_access_token'];
	} else if ( ! empty( $options['access_token'] ) ) {
		$access_token = $options['site_access_token'];
	} else {
		evc_add_log( 'evc_vkapi_resolve_screen_name: No Access Token passed.' );

		return false;
	}

  //http://vk.com/dev/utils.resolveScreenName  
  $default = array(
    'access_token' => $access_token,
    //'screen_name' => ''
    'v' => '5.21'
  );
  $params = wp_parse_args($params, $default);
  
  $res = evc_vkapi(array(
    'args'=>$params,
    'method'=>'utils.resolveScreenName',
    'method_str'=>'resolve_screen_name'
  ));
  
  return $res;
}

function evc_vkapi_users_search ($params) {
  $options = get_option('evc_vk_api_widgets'); 
  
  //http://vk.com/dev/users.search
  $default = array(
    'access_token' => $options['site_access_token'],
    'v' => '5.21'
  );
  $params = wp_parse_args($params, $default);
  
  $res = evc_vkapi(array(
    'args'=>$params,
    'method'=>'users.search',
    'method_str'=>'users_search'
  ));
  
  return $res;
}

function evc_vkapi_execute ($params) {
  $options = get_option('evc_vk_api_widgets');  
  //http://vk.com/dev/execute
  $default = array(
    'access_token' => $options['site_access_token'],
    'v' => '5.21'
  );
  $params = wp_parse_args($params, $default);
  
  $res = evc_vkapi(array(
    'args'=>$params,
    'method'=>'execute',
    'method_str'=>'execute'
  ));
  
  return $res;
}

function evc_get_vk_id ($url) {
  $id = false;
  $screen_names = array();
  
  if (is_numeric($url))
    $id = $url;
  else {
    $screen_names = get_option('evc_resolve_screen_names');
    if ($screen_names && isset($screen_names[$url]))
      $id = $screen_names[$url];
    else {   
      $urla = explode ('/', $url);
      if (is_array($urla) && !empty($urla)) {
        $screen_name = array_pop($urla);

        preg_match('/^(id|public|club|event)([0-9]+)/', $screen_name, $matches);
        if (isset($matches[1]) && !empty($matches[1]) && isset($matches[2]) && !empty($matches[2])) {
          $id = ($matches[1] != 'id') ? (-1*$matches[2]) : $matches[2];    
        }  
        else{
          $res = evc_vkapi_resolve_screen_name(array(
            'screen_name' => $screen_name
          ));

          if ($res && !empty($res) && isset($res['object_id'])) {
            $id = ($res['type'] != 'user') ? -1 * $res['object_id'] : $res['object_id'];
          }
        }
      }    
    }
  }
  
  do_action('evc_get_vk_id', $url, $id);
  
  if ($id && !isset($screen_names[$url])) {
    $screen_names[$url] = $id;
    update_option('evc_resolve_screen_names', $screen_names);
  }
  
  return $id;
}


function evc_get_vk_object( $url ) {
	$out = '';

	$vk_objects = get_option( 'evc_vk_objects' );
	if ( ! empty( $vk_objects ) && ! empty( $vk_objects[ $url ] ) ) {
		$out = $vk_objects[ $url ];
	} else {
		$urla = explode( '/', $url );
		if ( is_array( $urla ) && ! empty( $urla ) ) {
			$screen_name = array_pop( $urla );

			preg_match( '/^(id|public|club|event)([0-9]+)/', $screen_name, $matches );
			if ( ! empty( $matches[1] ) && ! empty( $matches[2] ) ) {
				$out['id'] = ( $matches[1] != 'id' ) ? ( - 1 * $matches[2] ) : $matches[2];
			} else {
				$out['screen_name'] = $screen_name;

				$res = evc_vkapi_resolve_screen_name( array(
					'screen_name' => $screen_name
				) );

				if ( ! empty( $res ) && ! empty( $res['object_id'] ) ) {
					$out['type'] = $res['type'];
					$out['id']   = ( $res['type'] != 'user' ) ? - 1 * $res['object_id'] : $res['object_id'];
				}
			}
		}
		if ( ! empty( $out['id'] ) ) {
			$vk_objects[$url] = $out;
			update_option( 'evc_vk_objects', $vk_objects );
		}
	}

	do_action( 'evc_get_vk_object', $url, $out );

	return $out;
}

// https://drupal.org/node/2043439
function evc_pro_remove_emoji( $text ) {
	$clean_text = "";

	// Match Emoticons
	$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
	$clean_text     = preg_replace( $regexEmoticons, '', $text );

	// Match Miscellaneous Symbols and Pictographs
	$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
	$clean_text   = preg_replace( $regexSymbols, '', $clean_text );

	// Match Transport And Map Symbols
	$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
	$clean_text     = preg_replace( $regexTransport, '', $clean_text );

	// Match flags (iOS)
	$regexTransport = '/[\x{1F1E0}-\x{1F1FF}]/u';
	$clean_text     = preg_replace( $regexTransport, '', $clean_text );

	$clean_text = preg_replace( '/([0-9|#][\x{20E3}])|[\x{00ae}][\x{FE00}-\x{FEFF}]?|[\x{00a9}][\x{FE00}-\x{FEFF}]?|[\x{203C}][\x{FE00}-\x{FEFF}]?|[\x{2047}][\x{FE00}-\x{FEFF}]?|[\x{2048}][\x{FE00}-\x{FEFF}]?|[\x{2049}][\x{FE00}-\x{FEFF}]?|[\x{3030}][\x{FE00}-\x{FEFF}]?|[\x{303D}][\x{FE00}-\x{FEFF}]?|[\x{2139}][\x{FE00}-\x{FEFF}]?|[\x{2122}][\x{FE00}-\x{FEFF}]?|[\x{3297}][\x{FE00}-\x{FEFF}]?|[\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $clean_text );

	return $clean_text;
}