<?php

add_action('admin_init', 'evc_lock_settings_defaults');
function evc_lock_settings_defaults() {
  $options = get_option('evc_lock');
  
  if ($options) {
    
    $options['subscribe_widget_mode'] = (!isset($options['subscribe_widget_mode']) || empty($options['subscribe_widget_mode'])) ? 0 : $options['subscribe_widget_mode'];
    
    $options['subscribe_widget_soft'] = (!isset($options['subscribe_widget_soft']) || empty($options['subscribe_widget_soft'])) ? 0 : $options['subscribe_widget_soft'];
    
    $options['vk_lock_text'] = !isset($options['vk_lock_text']) ? 'Чтобы увидеть скрытое содержимое, нужно' : $options['vk_lock_text'];
    
    update_option('evc_lock', $options);
  }
}

add_shortcode('vk_lock', 'evc_lock_shortcode');
function evc_lock_shortcode ($atts = array(), $content = '') {
  global $post, $user_ID;
  
  $options = get_option('evc_lock');  
  if (!$options || !isset($options['vk_lock_url']))    
    $options['vk_lock_url'] = null;
    
  $url = apply_filters('evc_lock_shortcode', $options['vk_lock_url'], $atts, $content);

  if (!isset($url) || empty($url))        
    return '';  

  $unlock = evc_lock_unlock($url);
  if (!is_numeric($unlock)) {   
    return $unlock;
  }
  $cookie = '<script type="text/javascript">vkUnLock.push('.$unlock.');</script>';   
  return $content . $cookie;
}

function evc_lock_unlock ($url) {
  global $user_ID, $post; 
  
  $options = get_option('evc_lock');   
  if (!$options || !isset($options['vk_lock_text']) )    
    $options['vk_lock_text'] = 'Чтобы увидеть скрытое содержимое, нужно';
    
  $id = evc_get_vk_id($url); 
  if (!$id)
    return 'Cant get vk item id'; // Cant get vk item id
       
  if (isset($_COOKIE['vkUnLock' . $id]) && $_COOKIE['vkUnLock' . $id] == $id)
    return $id;
      
  if (!is_user_logged_in())  // Need Enter via VK and Subscribe
    return '<p>'.$options['vk_lock_text'].' <a href= "'.evc_auth_login_url().'" title = "Войти на сайт через ВКонтакте">войти на сайт через ВКонтакте</a>.</p>';
  
  if(false ==($vk_user_id = get_user_meta($user_ID, 'vk_item_id', true))) // Need Enter via VK
    return '<p>'.$options['vk_lock_text'].' <a href= "'.wp_logout_url(get_permalink($post->ID)).'" title = "Выйти из своей учетной записи">выйти</a> и <a href= "'.evc_auth_login_url().'" title = "Войти на сайт через ВКонтакте">войти на сайт через ВКонтакте</a>.</p>'; 
  
  if (!isset($_COOKIE['vkUnLock' . $id]) || $_COOKIE['vkUnLock' . $id] != $id){
    //$id = ($id < 0) ? -1 * $id : $id;
    $is_member = evc_vkapi_groups_is_member(array(
      'group_id' => (($id < 0) ? -1 * $id : $id), // !!! Now only groups
      'user_id' => $vk_user_id
    )); 
    
    if (!$is_member) // Need Subscribe
      return '<p>'.$options['vk_lock_text'].' ' . evc_widget_subscribe (null, array(), $id, 'evc-vk-lock') . '</p>'; 
    
    //setcookie("vkUnLock", $id, time() + DAY_IN_SECONDS); 
  } 
  
  return $id;
}

add_shortcode( 'vk_subscribe', 'evc_vk_subscribe_shortcode' );
function evc_vk_subscribe_shortcode( $atts = array(), $content = '' ) {
	if ( ! empty( $atts ) ) {
		extract( $atts );
	}

	$out = '';
	if ( isset( $url ) && ! empty( $url ) ) {
		$id = evc_get_vk_id( $url );
		if ( ! $id || empty( $id ) ) {
			return $out;
		}
	}
	else
		return $out;

	return evc_widget_subscribe (null, array(), $id, 'evc-vk-subscribe-widget');
}

function evc_lock_add_meta_box() {
  $screens = array( 'post', 'page' );
  
  foreach ( $screens as $screen ) {
    add_meta_box(
      'evc_vk_lock',
      __( 'Социальный замок ВКонтакте', 'evc' ),
      'evc_lock_meta_box_callback',
      $screen
    );
  }
}
add_action( 'add_meta_boxes', 'evc_lock_add_meta_box' );

function evc_lock_meta_box_callback( $post ) {
  $is_pro = evc_is_pro();
  
  if ($is_pro) {
    $value = get_post_meta( $post->ID, 'evc_vk_lock', true );
    $readonly = '';
     $t1 = '<p>Если вы хотите <strong>закрыть только часть записи</strong>, используйте шорткод, например:
    <br/><code>[vk_lock url="http://vk.com/ukrayaru"]Текст, который необходимо закрыть[/vk_lock]</code>.</p>
    <p>Чтобы <strong>закрыть всю запись целиком</strong>, можно воспользоваться приведенной ниже формой.
    </p>';  
    $t2 = '';  
  }
  else {
    $options = get_option('evc_lock');  
    if (!$options || !isset($options['vk_lock_url']))    
      $options['vk_lock_url'] = ''; 
    
    $value = $options['vk_lock_url'];
     
     $readonly = 'readonly';
     $t1 = '<p>Если вы хотите <strong>закрыть только часть записи</strong>, используйте шорткод, например:
    <br/><code>[vk_lock]Текст, который необходимо закрыть[/vk_lock]</code>.</p>
    <p>В <a href = "javascript:void(0);" class = "get-evc-pro">PRO версии</a> можно закрыть разные записи или разные части одной записи на <b>замки с разными ключами</b>. Чтобы увидеть одну часть записи пользователь должен будет подписаться на одну группу, а чтобы увидеть другую - потребуется подписка на иную группу. Для этого используется параметр <code>url</code> в шоркоде, например:
    <br/><code>[vk_lock url="http://vk.com/ukrayaru"]Текст, который необходимо закрыть[/vk_lock]</code>
    <br/><br/>Чтобы <strong>закрыть всю запись целиком</strong>, в <a href = "javascript:void(0);" class = "get-evc-pro">PRO версии</a> можно воспользоваться приведенной ниже формой.
    </p>';
    $t2 = '<small>Доступно в <a href = "javascript:void(0);" class = "get-evc-pro">PRO версии</a>.</small><br/>';
  }
  wp_nonce_field( 'evc_lock_meta_box', 'evc_lock_meta_box_nonce' );
  
  
  echo $t1;
  echo '<p>';
  echo '<label for="evc_lock_field">Урл группы ВКонтакте:';
  echo '</label> ';
  echo '<input type="text" class="code" id="evc_lock_field" name="evc_vk_lock" value="' . esc_attr( $value ) . '" style = "width:99%;" '.$readonly.'/><br/>';
  echo $t2;
  echo  'Посетитель сможет увидеть контент записи, только если он подписан на указанную группу.
  <br/>Если вы <strong>не хотите</strong> закрывать запись на социальный замок, оставьте поле пустым.';
  echo '</p>';
  
}

add_action('evc_vk_async_init', 'evc_widget_subscribe_async_init');
function evc_widget_subscribe_async_init() {
  ?>
  //console.log(VKWidgetsSubscribe);
  // Widget Subscribe
    if (typeof VKWidgetsSubscribe !== 'undefined' ) {

      var subscribeCookieExpires = 1; // !!!
      
      for (index = 0; index < VKWidgetsSubscribe.length; ++index) {
        VK.Widgets.Subscribe(
          VKWidgetsSubscribe[index].element_id, 
          VKWidgetsSubscribe[index].options, 
          VKWidgetsSubscribe[index].owner_id
        );
      }
      
      VK.Observer.subscribe('widgets.subscribed', function(n) {
        
        vkwidget = jQuery("#vkwidget" + n).parent();              
        vkwidgetID = jQuery(vkwidget).attr('id');
        
        if (jQuery(vkwidget).hasClass('evc-vk-lock')) {
          console.log('Fire');
          //vkwidgetID = jQuery("#vkwidget" + n).parent().attr('id');
          subscribeObj = jQuery.grep(VKWidgetsSubscribe, function(e){ return e.element_id == vkwidgetID; });          
          if (subscribeObj.length != 0) {
            o = subscribeObj[0].owner_id;
            if (jQuery.cookie('vkUnLock' + o) == 'undefined' || !jQuery.cookie('vkUnLock' + o) || jQuery.cookie('vkUnLock' + o) !=  subscribeObj[0].owner_id ) {
              jQuery.cookie('vkUnLock' + o, subscribeObj[0].owner_id, { expires: subscribeCookieExpires, path: '/' });
            }
            location.reload();
          }
        }
      });

      VK.Observer.subscribe('widgets.unsubscribed', function(n) {
              
        vkwidget = jQuery("#vkwidget" + n).parent();              
        vkwidgetID = jQuery(vkwidget).attr('id');
        
        if (jQuery(vkwidget).hasClass('evc-vk-lock')) { 
        console.log('Fire');      
          subscribeObj = jQuery.grep(VKWidgetsSubscribe, function(e){ return e.element_id == vkwidgetID;});
          if (subscribeObj.length != 0) {
            o = subscribeObj[0].owner_id;
            if (jQuery.cookie('vkUnLock' + o) != 'undefined') {
              jQuery.removeCookie('vkUnLock' + o);
            }
          }
        }
      });      

   
    }
<?php
}

function evc_widget_subscribe ($element_id = null, $args = array(), $owner_id = null, $class = "") {  
  global $post;
  $options = get_option('evc_lock');  
  
  if (!isset($element_id))
    $element_id = 'vk-widget-subscribe' . $owner_id;
    
  $o['mode'] = $options['subscribe_widget_mode']; // 0 - Кнопка, 1 - Лёгкая кнопка, 2 - Ссылка.
  $o['soft'] = $options['subscribe_widget_soft']; // 0 - Отображать автора и кнопку, 1 - Отображать только кнопку. 
  
  $o = wp_parse_args($args, $o);
  $o = evc_vk_widget_data_encode($o);
  
  $out = '
<script type="text/javascript">
  
  VKWidgetsSubscribe.push ({
    element_id: "'.$element_id.'",
    options: '.$o .', 
    owner_id: '. $owner_id .'
  });  
  
</script>';

  $out .= '<div class = "vk_widget_subscribe '.$class.'" id = "'.$element_id.'"></div>';
  
  return $out;
}  


function evc_lock_admin_init() {
  global $evc_lock;
  
  $evc_lock = new WP_Settings_API_Class;
  
  $is_pro = evc_is_pro();
    
  if ($is_pro) {
     $t1 = '<br/><br/>Если вы хотите <strong>закрыть только часть записи</strong>, при редактировании используйте шорткод, например:
    <br/><code>[vk_lock url="http://vk.com/ukrayaru"]Текст, который необходимо закрыть[/vk_lock]</code>.
    <br/><br/>Чтобы <strong>закрыть всю запись</strong>, используйте специальную форму на странице редактирования записи.';  
  }
  else {
     $t1 = '<br/><br/>Если вы хотите <strong>закрыть только часть записи</strong>, при редактировании используйте шорткод, например:
    <br/><code>[vk_lock]Текст, который необходимо закрыть[/vk_lock]</code>.
    <br/><br/>В <a href = "javascript:void(0);" class = "get-evc-pro">PRO версии</a> можно закрыть разные записи или разные части одной записи на <b>замки с разными ключами</b>. Чтобы увидеть одну часть записи пользователь должен будет подписаться на одну группу, а чтобы увидеть другую - потребуется подписка на иную группу. Для этого используется параметр <code>url</code> в шоркоде, например:
    <br/><code>[vk_lock url="http://vk.com/ukrayaru"]Текст, который необходимо закрыть[/vk_lock]</code>
    <br/><br/>Чтобы <strong>закрыть всю запись целиком</strong>, в <a href = "javascript:void(0);" class = "get-evc-pro">PRO версии</a> можно воспользоваться специальной формой на странице редактирования записи.';
  }  
  
  $tabs = array(
    'evc_lock' => array(
      'id' => 'evc_lock',
      'name' => 'evc_lock',
      'title' => __( 'Замок', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(       
        'evc_lock_section' => array(
          'id' => 'evc_lock_section',
          'name' => 'evc_lock_section',
          'title' => __( 'Социальный замок', 'evc' ),
          'desc' => __( 'Позволяет скрыть запись ото всех, и показать лишь тем, кто вступил в указанную группу. Запись будет доступна пользователю, пока он состоит в группе, и станет недоступна, если он из нее выйдет.' . $t1, 'evc' ),          
        )
      )
    )
  );
  $tabs = apply_filters('evc_lock_tabs', $tabs, $tabs); 
  
  $fields = array(
    'evc_lock_section' => array(   
      array(
        'name' => 'vk_lock_url',
        'label' => __( 'Ссылка на страницу', 'evc' ),
        'desc' => __( 'Урл страницы или группы, на которую должен подписаться пользователь, чтобы увидеть скрытую запись.
        <br/>Например: <code>http://vk.com/ukrayaru</code>.', 'evc' ),
        'type' => 'text'    
      ),
      array(
        'name' => 'vk_lock_text',
        'label' => __( 'Текст замка', 'evc' ),
        'desc' => __( 'Текст, который увидит пользователь вместо скрытого материала.', 'evc' ),
        'type' => 'text',
        'default' => 'Чтобы увидеть скрытое содержимое, нужно'    
      ),      
      array(
        'name' => 'subscribe_widget_mode',
        'label' => __( 'Вид кнопки', 'evc' ),
        'desc' => __( 'Как будет выглядеть кнопка <em>Подписаться</em> на группу или пользователя.', 'evc' ),
        'type' => 'radio',
        'default' => '0',
        'options' => array(
          '0' => 'Кнопка',
          '1' => 'Легкая кнопка',
          '2' => 'Ссылка',
        )
      ),
      array(
        'name' => 'subscribe_widget_soft',
        'label' => __( 'Формат кнопки', 'evc' ),
        'desc' => __( 'Отображать только кнопку или кнопку и фото автора (или аватар группы).', 'evc' ),
        'type' => 'radio',
        'default' => '0',
        'options' => array(
          '0' => 'Отображать автора (или название сообщества) и кнопку',
          '1' => 'Отображать только кнопку'
        )
      )  
    )
  );
  $fields = apply_filters('evc_lock_fields', $fields, $fields);
  
 //set sections and fields
 $evc_lock->set_option_name( 'evc_options' );
 $evc_lock->set_sections( $tabs );
 $evc_lock->set_fields( $fields );

 //initialize them
 $evc_lock->admin_init();
}
add_action( 'admin_init', 'evc_lock_admin_init' );


// Register the plugin page
function evc_lock_admin_menu() {
  global $evc_lock_settings_page; 
  
  $evc_lock_settings_page = add_submenu_page( 'evc', 'Социальный замок ВКонтакте', 'Социальный замок', 'activate_plugins', 'evc-lock', 'evc_lock_settings_page' );
}
add_action( 'admin_menu', 'evc_lock_admin_menu', 25 );

// Display the plugin settings options page
function evc_lock_settings_page() {
  global $evc_lock;
  $options = get_option('evc_vk_api_widgets');
  
  echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"><br /></div>';
    echo '<h2>Социальный замок ВКонтакте</h2>';
    
    if (!isset($options['site_access_token']) || empty($options['site_access_token'])) {
      echo '<div class="error"><p>Необходимо настроить API ВКонтакте. Откройте вкладку "<a href="'.admin_url('admin.php?page=evc#evc_vk_api_widgets').'">Для виджетов</a>".</p></div>';
    }
        
    echo '<div id = "col-container">';  
      echo '<div id = "col-right" class = "evc">';
        echo '<div class = "evc-box">';
        evc_ad();
        echo '</div>';
      echo '</div>';
      echo '<div id = "col-left" class = "evc">';
        settings_errors();
        $evc_lock->show_navigation();
        $evc_lock->show_forms();
      echo '</div>';
    echo '</div>';    

  echo '</div>';
}