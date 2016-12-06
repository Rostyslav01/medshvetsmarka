<?php

add_action('wp_footer', 'evc_comments_footer_scripts', 20); 
function evc_comments_footer_scripts(){
	global $post;
  $options = get_option('evc_comments');
  
  $comment_widget_layout = get_post_meta ($post->ID, 'comment_widget_layout', true);
  if ($comment_widget_layout)
    $options['comment_widget_layout'] = $comment_widget_layout;
   
  ?>
  <script type="text/javascript">
  /* <![CDATA[ */
	jQuery(document).ready(function($) {
    
    if ( typeof VKWidgetsComments !== 'undefined' && VKWidgetsComments.length && evc_post_id ) {
		  if ($('#vk-widget-' + evc_post_id).length) {
        <?php 
        if ($options['comment_widget_layout'] == 'instead' && isset($options['comment_widget_respond']) && !empty($options['comment_widget_respond'])) { 
        ?>
        if ( $('<?php echo $options['comment_widget_respond'];?>').length ) {          
          $('<?php echo $options['comment_widget_respond'];?> form').hide();
		      $('<?php echo $options['comment_widget_respond'];?>').append($('#vk-widget-' + evc_post_id ));
        }
        <?php 
        }
        if (isset($options['comment_widget_comments']) && !empty($options['comment_widget_comments'])) {         
          if ($options['comment_widget_layout'] == 'before') {
            
          ?>
            $('<?php echo $options['comment_widget_comments'];?>').prepend($('#vk-widget-' + evc_post_id ));          
          <?php            
          }
          if ($options['comment_widget_layout'] == 'after') {
          ?>
            $('<?php echo $options['comment_widget_comments'];?>').append($('#vk-widget-' + evc_post_id ));                    
          <?php  
          }
        }
        ?>
      }
		
		  <?php
        if (isset($options['comment_widget_hide_wp_comments']) && $options['comment_widget_hide_wp_comments']) {
      ?>
      cClose = false;
      if ($( "<?php echo $options['comment_widget_comments_list'];?>" ).length) {
        $( "<?php echo $options['comment_widget_comments_list'];?>" ).wrap('<div class = "evc-comments-wrap"></div>');
		  
        docViewHeight = $(window).height();
        $(document).scroll(function () {
          var docViewTop = $(window).scrollTop();
          var elemTop = $('.evc-comments-wrap').offset().top;
          //var elemBottom = elemTop + $('.evc-comments-wrap').height();         
          if ( elemTop * 3 / 4 <= docViewTop && !cClose ) {
            cClose = true;  
            $( ".evc-comments-wrap" ).animate({ "height": 0}, 800 );
            //console.log(elemTop + ' >= ' + docViewHeight+ '+'+ docViewTop);
          }
        });
      }
      <?php
        }
      ?>
    }
 		
  // Rresponsive VK Comments Widget Width
  <?php
    if ($options['comment_widget_width'] == 0 ) {
  ?>      
    if ($('.vk_widget_comments').length) {
      
      responsiveVkWidget();
      $(window).on('resize', function() {
        responsiveVkWidget();
      });
    }
  <?php    
    }
  ?>
    
    function responsiveVkWidget () {
      var vkParentWidth = parseInt( $('.vk_widget_comments').parent().width() );
      
      $('.vk_widget_comments, .vk_widget_comments iframe').css({
        width: vkParentWidth
      });
      $('.vk_widget_comments, .vk_widget_comments iframe').attr('width', vkParentWidth);
    }
  
  // END Rresponsive VK Comments Widget Width 
    
    
	}); // End jQuery 
   
  /* ]]> */
  </script><?php
}

add_action('comment_form_before','evc_comments_add_widget');
function evc_comments_add_widget () {
	global $post;
  $options = get_option('evc_comments');  
	
  $comment_widget_insert = get_post_meta ($post->ID, 'comment_widget_insert', true);
  if ($comment_widget_insert)
    $options['comment_widget_insert'] = $comment_widget_insert;
      
  if (isset($options['comment_widget_insert']) && $options['comment_widget_insert'] == 'auto') {
	  if ( (isset($options['comment_widget_for']) && $options['comment_widget_for'] == 'unregistered' && !is_user_logged_in()) || 
    (isset($options['comment_widget_for']) && $options['comment_widget_for'] == 'all') || 
    !isset($options['comment_widget_for']) ) {
	    echo evc_vk_widget_comments ('vk-widget-' . $post->ID);
    }
  }
}
	
function evc_vk_widget_comments ($element_id = null, $args = array(), $page_id = null) {  
	global $post;
  $options = get_option('evc_comments');	
	
  if (!isset($element_id))
    $element_id = 'vk-widget-' . $post->ID;
    
	$o['width'] = $options['comment_widget_width'];
	$o['height'] = $options['comment_widget_height'];
	$o['limit'] = $options['comment_widget_limit'];
	
	if (!isset($options['comment_widget_attach']) || empty($options['comment_widget_attach']) || isset($options['comment_widget_attach']['none']) )
		$o['attach'] = 'false';
	else {
		foreach($options['comment_widget_attach'] as $attach)
			$o['attach'][] = $attach;
		
		$o['attach'] = implode(',', $o['attach']);
	}
	
	$o['norealtime'] = $options['comment_widget_norealtime'];
	$o['autoPublish'] = $options['comment_widget_autopublish'];
  
  if( isset($options['comment_widget_compability']['vkontakte_api']) && !empty($options['comment_widget_compability']['vkontakte_api']) )
    $o['pageUrl'] = get_permalink();  
  
  $o = wp_parse_args($args, $o);
	$o = evc_vk_widget_data_encode($o);
	
  $out = '
<script type="text/javascript">
  VKWidgetsComments.push ({
    element_id: "'.$element_id.'",
    options: '.$o;
  
  if (isset($page_id))
    $out .= ',page_id: '.$page_id;
  elseif ( (isset($options['comment_widget_page_id']) && $options['comment_widget_page_id']) || (isset($options['comment_widget_compability']['vkontakte_api']) && !empty($options['comment_widget_compability']['vkontakte_api'])) )
    $out .= ',page_id: '.$post->ID;
  
  $out .= '
  });    
</script>';

		$out .= '<div class = "vk_widget_comments" id = "'.$element_id.'"></div>	
  ';
  
  return $out;
}	
	
	
function evc_comments_admin_init() {
  global $evc_comments;
  $evc_comments_author = get_option('evc_comments_author');
    
  $evc_comments = new WP_Settings_API_Class;
  
  $tabs = array(
    'evc_comments' => array(
      'id' => 'evc_comments',
      'name' => 'evc_comments',
      'title' => __( 'Комментарии', 'evc' ),
      'desc' => __( '', 'evc' ),
      'sections' => array(
        'evc_comments_section' => array(
          'id' => 'evc_comments_section',
          'name' => 'evc_comments_section',
          'title' => __( 'Настройки виджета комментариев ВКонтакте', 'evc' ),
          'desc' => __( 'Основные настройки для виджета комментариев.', 'evc' ),          
        ),
        'evc_comments_show' => array(
          'id' => 'evc_comments_show',
          'name' => 'evc_comments_show',
          'title' => __( 'Отображение виджета комментариев ВКонтакте', 'evc' ),
          'desc' => __( 'Основные настройки отображения виджета комментариев.', 'evc' ),          
        ),        
        'evc_comments_dev' => array(
          'id' => 'evc_comments_dev',
          'name' => 'evc_comments_dev',
          'title' => __( 'Служебные настройки', 'evc' ),
          'desc' => __( 'Меняйте только, если понимаете что делаете.', 'evc' ),          
        )               
      )
    ), 
    'evc_comments_pro' =>  array(
      'id' => 'evc_comments_pro',
      'name' => 'evc_comments_pro',
      'title' => __( 'Расширенная версия', 'evc' ),
      'desc' => __( 'Расширенная версия', 'evc' ),
      'submit_button' => false,
      'sections' => array(
        'evc_comments_pro_section' => array(
          'id' => 'evc_comments_pro_section',
          'name' => 'evc_comments_pro_section',
          'title' => __( 'VK SEO комментарии', 'evc' ),
          'desc' => __( '<p><b>Тонны бесплатного уникального контента</b> на ваш сайт! 
<br/><b>Толпы посетителей</b> по низкочастотным запросам! 
<br/>Заставьте поисковые системы <b>индексировать</b> комментарии, оставленные через <em>виджет комментариев ВКонтакте</em>!</p> 
<p>'.get_submit_button('Установить сейчас', 'secondary', 'get_vk_seo_comments', false).'</p>
<p>Модуль <em>VK SEO комментарии</em> <b>импортирует комментарии</b>, оставленные через <em>виджет комментариев ВКонтакте</em> на ваш сайт. Они превращаются в обычные комментарии, которые оставляют зарегистрированные пользователи.</p>
<p>При этом импортируются:
<ol><li><b>Имя</b> и <b>Фамилия</b> пользователя (оставившего комментарий),</li>
<li><b>Аватар</b> пользователя,</li>
<li><b>Текст</b> комментария,</li>
<li><b>Ветки</b> комментариев.</li></ol></p>
<p>Если на вашем сайте уже был установлен <em>виджет комментариев ВКонтакте</em>, <b>плагин сам импортирует</b> ранее оставленные комментарии.</p>
<p><b>Профессиональная техническая поддержка бесплатно</b> поможет решить любую проблему по работе плагина.</p>
<p><b>Зарабатывайте</b> с нами. Попробуйте плагин сами и предложите его своим друзьям или клиентам. Первого мая, в День Труда мы запускаем нашу партнерскую программу. Все, кто приобретет плагин до этой даты, получат повышенные партнерские отчисления.</p>
<p>'.get_submit_button('Установить сейчас', 'primary', 'get_vk_seo_comments2', false).'</p>', 'evc' ),         
        )
      )
    ),
    'evc_comments_mod' => array(
      'id' => 'evc_comments_mod',
      'name' => 'evc_comments_mod',
      'title' => __( 'Обзор комментариев', 'evc' ),
      'desc' => __( '', 'evc' ),
      'submit_button' => false,
      'sections' => array(       
        'evc_comments_mod_section' => array(
          'id' => 'evc_comments_mod_section',
          'name' => 'evc_comments_mod_section',
          'title' => __( 'Обзор комментариев из Виджета комментариев ВК', 'evc' ),
          'desc' => '<p>Здесь отображены все комментарии, оставленные на сайте через Виджет комментариев ВКонтакте.</p>
          <div id = "vk-comments" class = "vk-widget-comments-browse"></div>
          <script type="text/javascript">  
            VK.Widgets.CommentsBrowse("vk-comments", {width: 600, height: 1000, limit: 10, mini: 1});
          </script>',           
        )
      )
    )         
  );  
  $tabs = apply_filters('evc_comments_admin_tabs', $tabs);
  
  $fields = array(
   'evc_comments_section' => array(					
      array(
        'name' => 'comment_widget_width',
        'label' => __( 'Ширина блока', 'evc' ),
        'desc' => __( 'Ширина блока комментариев, в px.
        <br/>Поставьте <code>0</code>, чтобы ширина выставлялась автоматически (респонсивно; под ширину родительского контейнера). 
				<br/>Например: <code>300</code>.', 'evc' ),
				'type' => 'text',
				'default' => 0
      ),                 
      array(
        'name' => 'comment_widget_height',
        'label' => __( 'Высота блока', 'evc' ),
        'desc' => __( 'Высота блока комментариев, в px (больше 500). 
				<br/>Если <code>0</code> - не ограничена.
				<br/>Например: <code>500</code>.', 'evc' ),
				'type' => 'text',
				'default' => 0
      ),           
      array(
        'name' => 'comment_widget_limit',
        'label' => __( 'Число комментариев', 'evc' ),
        'desc' => __( 'Количество комментариев на странице: от 5 до 100. 
				<br/>Например: <code>10</code>.', 'evc' ),
				'type' => 'text',
				'default' => 10
      ),           
      array(
        'name' => 'comment_widget_attach',
        'label' => __( 'Прикрепления', 'evc' ),
        'desc' => __( 'Разрешить или запретить прикрепления к комментариям.', 'evc' ),
        'type' => 'multicheck',
        'options' => array(
          'none' => '<b>Запретить все</b> прикрепления.',
          'all' => '<b>Разрешить все</b> прикрепления.',
					'graffiti' => '<small>Разрешить граффити.</small>',
					'photo' => '<small>Разрешить изображения.</small>',
					'audio' => '<small>Разрешить аудио.</small>',
					'video' => '<small>Разрешить видео.</small>',
					'link' => '<small>Разрешить ссылки.</small>',
        ),
        'default' => array(
          'none' => 'none'
        )				
      ),   
      array(
        'name' => 'comment_widget_norealtime',
        'label' => __( 'Обновление', 'evc' ),
        'desc' => __( 'Обновление ленты комментариев в реальном времени.', 'evc' ),
        'type' => 'radio',
				'default' => '0',
        'options' => array(
          '0' => 'Включено',
          '1' => 'Отключено',
        )
      ), 			
      array(
        'name' => 'comment_widget_autopublish',
        'label' => __( 'Публиковать в статус', 'evc' ),
        'desc' => __( 'Автоматическая публикация комментария в статус пользователя.', 'evc' ),
        'type' => 'radio',
        'default' => '0',
        'options' => array(
          '1' => 'Включено',
          '0' => 'Отключено',
        )
      )
   ),
   'evc_comments_show' => array(       
      array(
        'name' => 'comment_widget_insert',
        'label' => __( 'Размещать виджет', 'evc' ),
        'desc' => __( 'Автоматически или вручную размещать виджет комментариев на странице сайта.', 'evc' ),
        'type' => 'radio',
        'default' => 'auto',
        'options' => array(
          'auto' => 'Автоматически',
          'manual' => 'Вручную',
        )
      ),
      array(
        'name' => 'comment_widget_layout',
        'label' => __( 'Поместить виджет', 'evc' ),
        'desc' => __( 'В каком месте на странице поместить виджет комментариев ВКонтакте.', 'evc' ),
        'type' => 'radio',
        'default' => 'instead',
        'options' => array(
          'instead' => '<b>Вместо</b> стандартной формы комментариев',
          'before' => '<b>До</b> блока комментариев',
          'after' => '<b>После</b> блока комментариев',
        )
      ),
      array(
        'name' => 'comment_widget_for',
        'label' => __( 'Показывать виджет', 'evc' ),
        'desc' => __( 'Кому показывать виджет комментариев ВКонтакте.', 'evc' ),
        'type' => 'radio',
        'default' => 'all',
        'options' => array(
          'all' => 'Всем посетителям',
          'unregistered' => 'Только <b>незарегистрированным</b> посетителям',
        )
      ),
      array(
        'name' => 'comment_widget_hide_wp_comments',
        'label' => __( 'Скрывать комментарии', 'evc' ),
        'desc' => __( 'Скрывать вордпресс комментарии от посетителей.', 'evc' ),
        'type' => 'radio',
        'default' => '1',
        'options' => array(
          '1' => 'Да',
          '0' => 'Нет',
        )
      )
   ),
   'evc_comments_dev' => array(        
      array(
        'name' => 'comment_widget_page_id',
        'label' => __( 'Page ID', 'evc' ),
        'desc' => __( 'Использовать в том случае, если у одной и той же статьи может быть несколько адресов.', 'evc' ),
        'type' => 'radio',
        'default' => '0',
        'options' => array(
          '1' => 'Использовать',
          '0' => 'Не использовать',
        )
      ),      
      array(
        'name' => 'comment_widget_respond',
        'label' => __( '', 'evc' ),
        'desc' => __( 'Родительский CSS контейнер для формы "Написать комментарий".', 'evc' ),
        'type' => 'text',
        'default' => '#respond'
      ), 
      array(
        'name' => 'comment_widget_comments_list',
        'label' => __( '', 'evc' ),
        'desc' => __( 'CSS контейнер для списка комментариев.', 'evc' ),
        'type' => 'text',
        'default' => '#comments .comment-list'
      ), 
      array(
        'name' => 'comment_widget_comments',
        'label' => __( '', 'evc' ),
        'desc' => __( 'CSS контейнер для блока комментариев.', 'evc' ),
        'type' => 'text',
        'default' => '#comments'
      ),
      array(
        'name' => 'comment_widget_compability',
        'desc' => __( 'Поставьте галочку, чтобы отобразить в виджете комментарии, оставленные через другой плагин.', 'evc' ),
        'type' => 'multicheck',
        'options' => array(
          'vkontakte_api' => 'Миграция с Vkontakte API',
        )
      ),                                        				                
    )    
  );
  $fields = apply_filters('evc_comments_admin_fields', $fields);
  
  $evc_comments->set_sections( $tabs );
  $evc_comments->set_fields( $fields );

  //initialize them
  $evc_comments->admin_init();
}
add_action( 'admin_init', 'evc_comments_admin_init' );


// Register the plugin page
function evc_comments_admin_menu() {
  global $evc_comments_page; 
   
  $evc_comments_page = add_submenu_page( 'evc', 'Виджет комментариев ВКонтакте', 'Комментарии', 'activate_plugins', 'evc-comments', 'evc_comments_page' );
  add_action( 'admin_footer-'. $evc_comments_page, 'evc_comments_settings_page_js' );
}
add_action( 'admin_menu', 'evc_comments_admin_menu', 25 );

function evc_comments_settings_page_js() {
?>
<script type="text/javascript" >
  jQuery(document).ready(function($) {

    
    if ($('.vk-widget-comments-browse').length) {
      
      responsiveVkWidgetBrowse();
      $(window).on('resize', function() {
        responsiveVkWidgetBrowse();
      });
    }
    
    function responsiveVkWidgetBrowse () {
      var vkParentWidth = parseInt( $('.vk-widget-comments-browse').parent().width() );
      
      $('.vk-widget-comments-browse, .vk-widget-comments-browse iframe').css({
        width: vkParentWidth
      });
      $('.vk-widget-comments-browse, .vk-widget-comments-browse iframe').attr('width', vkParentWidth);
    }
         
  
  }); // jQuery End
</script>
<?php    
}

add_action('evc_vk_async_init', 'evc_comments_vk_async_init');
function evc_comments_vk_async_init() {
  ?>
  //console.log(VKWidgetsComments);
  // COMMENTS
    if (typeof VKWidgetsComments !== 'undefined' ) {
      //console.log(VKWidgetsComments);
      for (index = 0; index < VKWidgetsComments.length; ++index) {
        VK.Widgets.Comments(
          VKWidgetsComments[index].element_id, 
          VKWidgetsComments[index].options, 
          VKWidgetsComments[index].page_id
        );
      }
    <?php  
      do_action('evc_comments_vk_async_init');
    ?>    
    }
<?php
}


// Display the plugin settings options page
function evc_comments_page() {
  global $evc_comments;
	$options = evc_get_all_options(array(
		'evc_vk_api_widgets',
		'evc_comments'
	));	

  echo '<div class="wrap">';
    echo '<div id="icon-options-general" class="icon32"><br /></div>';
    echo '<h2>Виджет комментариев ВКонтакте</h2>';
    
    if (!isset($options['site_access_token']) || empty($options['site_access_token'])) {
      echo '<div class="error"><p>Необходимо настроить API ВКонтакте. Откройте вкладку "<a href="'.admin_url('admin.php?page=evc').'">Для виджетов</a>".</p></div>';
    }
 
		echo '<div id = "col-container">';  
      echo '<div id = "col-right" class = "evc">';
				echo '<div class = "evc-box">';
				evc_ad();
				echo '</div>';
			echo '</div>';
      echo '<div id = "col-left" class = "evc">';
        settings_errors();
        $evc_comments->show_navigation();
        $evc_comments->show_forms();
      echo '</div>';
    echo '</div>';	
		
    
  echo '</div>';
}


function evc_add_meta_box() {
  $screens = array( 'post', 'page' );
  
  foreach ( $screens as $screen ) {
    add_meta_box(
      'evc_meta_box',
      __( 'Easy VK Connect', 'evc' ),
      'evc_meta_box_callback',
      $screen
    );
  }
}
add_action( 'add_meta_boxes', 'evc_add_meta_box' );

function evc_meta_box_callback( $post ) {
  global $post; 
  
  $is_pro = evc_is_pro();
  $custom = get_post_custom($post->ID);

  wp_nonce_field( 'evc_meta_box', 'evc_meta_box_nonce' );
  
  do_action('evc_meta_box_action', $custom);
}

add_action('evc_meta_box_action', 'evc_meta_box_comments_widget');
function evc_meta_box_comments_widget($custom) {
  global $post;
	$is_pro = evc_is_pro();

  $options = evc_get_all_options( array(
      'evc_comments'
  ) );

  if ( isset( $custom['comment_widget_insert'] ) ) {
    $evc_comments = $custom['comment_widget_insert'][0];
  } else {
    $evc_comments = $options['comment_widget_insert'];
  }

  echo '<p>';
  echo '<b>Виджет комментариев ВКонтакте</b>';
  echo '<br/><input type="radio" value="auto" id="evc-comments-auto" name="comment_widget_insert"' . checked( $evc_comments, 'auto', false ) . ' >
  <label class="selectit" for="evc-comments-auto">Включить</label>';

  echo '<br/><input type="radio" value="manual" id="evc-comments-manual" name="comment_widget_insert"' . checked( $evc_comments, 'manual', false ) . ' >
  <label class="selectit" for="evc-comments-manual">Отключить</label>';
  echo '<br/>Вы можете включить или отключить виджет комментариев ВКонтакте для данной страницы.';
  echo '</p>';

  if ( isset( $custom['vk_item_id'][0] ) ) {

    echo '<p>';
    echo '<b>Обновить комментарии из группы</b>';
    if ( ! $is_pro ) {
      echo '<br/><small>Доступно в <a href = "javascript:void(0);" class = "get-evc-pro">PRO версии</a>.</small>';
    }
    echo '<br/>Нажмите кнопку, чтобы немедленно обновить комментарии, оставленные к данной записи в группе ВКонтакте.';
    echo '<br><a class="button '.(!$is_pro ? 'disabled' : '').'" id ="evc_pro_refresh_vk_comments" data-post-id = ' . $post->ID . '>Обновить</a><span
id="evc_pro_refresh_vk_comments_spinner"
style="display:none; float:none !important; margin: 0 5px !important;" class="spinner"></span>';
    echo '</p>';
  }


}

function evc_save_meta_box_data( $post_id ) {

  // Check if our nonce is set.
  if ( ! isset( $_POST['evc_meta_box_nonce'] ) )
    return;

  // Verify that the nonce is valid.
  if ( ! wp_verify_nonce( $_POST['evc_meta_box_nonce'], 'evc_meta_box' ) )
    return;

  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;

  // Check the user's permissions.
  if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
      return;
  } 
  else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
      return;
  }
 
  do_action('evc_save_meta_box_action', $post_id);
  

}
add_action( 'save_post', 'evc_save_meta_box_data' );

add_action( 'evc_save_meta_box_action', 'evc_save_meta_box_comments_widget' );
function evc_save_meta_box_comments_widget($post_id) {
  
  // Make sure that it is set.
  if ( ! isset( $_POST['comment_widget_insert'] ) ) 
    return;

  // Update the meta field in the database.
  if (!update_post_meta($post_id, 'comment_widget_insert', $_POST['comment_widget_insert'] ))
    add_post_meta($post_id, 'comment_widget_insert', $_POST['comment_widget_insert'], true);   
  
}