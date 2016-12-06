<?php

if (!defined('EVC_API'))
  define('EVC_API','http://ukraya.ru/evc-api');
  
// Register the plugin page
function evc_polls_admin_menu() {
  global $evc_polls_page; 
   
  $evc_polls_page = add_submenu_page( 'edit.php?post_type=evc_poll', 'Все опросы ВКонтакте', 'Все опросы VK', 'activate_plugins', 'evc-polls', 'evc_poll_list_polls' );
  
  add_action("load-$evc_polls_page", 'evc_poll_screen_options');
  add_action( 'admin_print_styles-' . $evc_polls_page, 'evc_stats_styles' );
  add_action( 'admin_footer-'. $evc_polls_page, 'evc_polls_page_js' );
}
add_action( 'admin_menu', 'evc_polls_admin_menu', 25 );

function evc_polls_page_js () {
?>
<script type="text/javascript" >
  jQuery(document).ready(function($) {

    $(document).on( 'mouseenter', 'tr[id*="poll-"]', function (e) {    
      e.preventDefault();
      var pollID = $(this).data('poll-id');
      openTimeout = setTimeout(function() {
        //console.log($(this));       
        $('#evc-poll-widget').html('<div id = "evc_poll-'+ pollID+ '"></div><script type="text/javascript">VK.Widgets.Poll("evc_poll-'+pollID+'", {},'+ pollID+');<\/script>');
      }, 500 ); 
      
    }); 
    $(document).on( 'mouseleave', '[id*="poll-"]', function (e) {    
      e.preventDefault();
      clearTimeout(openTimeout);
    }); 
    
  }); // jQuery End
</script>  
<?php
}

function evc_poll_cpt() {
   $labels = array(
    'name'               => __( 'Опросы VK', 'evc' ),
    'singular_name'      => __( 'Опрос VK', 'evc' ),
    'menu_name'          => __( 'EVC Опросы VK', 'evc' ),
    'name_admin_bar'     => __( 'EVC Опросы VK', 'evc' ),
    'add_new'            => __( 'Новый опрос', 'evc' ),
    'add_new_item'       => __( 'Добавить новый опрос', 'evc' ),
    'new_item'           => __( 'Новый опрос', 'evc' ),
    'edit_item'          => __( 'Редактировать опрос', 'evc' ),
    'view_item'          => __( 'Смотреть опрос', 'evc' ),
    'all_items'          => __( 'Мои опросы', 'evc' ),
    'search_items'       => __( 'Найти опросы', 'evc' ),
    'parent_item_colon'  => __( 'Родительский опрос:', 'evc' ),
    'not_found'          => __( 'Опросов не найдено.', 'evc' ),
    'not_found_in_trash' => __( 'В корзине опросов не найдено.', 'evc' ),
  );   
  register_post_type('evc_poll', array(
    'labels' => $labels,
    'public' => true,
    'show_ui' => true, 
    '_builtin' => false, 
    '_edit_link' => 'post.php?post=%d',
    'capability_type' => 'post',
    'hierarchical' => false,
    'rewrite' => array("slug" => "evc_poll"), 
    'query_var' => "evc_poll",
    //'taxonomies' => array('category', 'post_tag'),
    'supports' => array(''), 
    'menu_position' => 99,
    'menu_icon' => plugins_url( 'img/vk-logo.png', __FILE__ )
  )); 
  
  add_filter("manage_evc_poll_posts_columns", 'evc_poll_edit_columns');
  add_action("manage_evc_poll_posts_custom_column", 'evc_poll_columns', 10, 2);
    
  // Insert post hook
  add_action("wp_insert_post", 'evc_poll_wp_insert_post', 10, 2);  
  add_action("save_post", 'evc_poll_update_post');  
  
  add_action("admin_init", 'evc_poll_cpt_admin_init');
  add_action("template_redirect", 'evc_poll_template_redirect');
      
}
add_action( 'init', 'evc_poll_cpt' );


function evc_poll_update_post($post_id) {
  if (!isset($_POST['post_type']) || $_POST['post_type'] != 'evc_poll' || !isset($_POST['evc_poll']) )
    return;
  
  remove_action('save_post', 'evc_poll_update_post');
  remove_action("wp_insert_post", 'evc_poll_wp_insert_post', 10, 2);
  wp_update_post(array(
    'ID' => $post_id,
    'post_title' => $_POST['evc_poll']['question']
  )); 
  add_action('save_post', 'evc_poll_update_post');   
  add_action("wp_insert_post", 'evc_poll_wp_insert_post', 10, 2);
}


add_filter('post_row_actions', 'evc_poll_post_row_actions', 10, 2);
function evc_poll_post_row_actions ($actions, $post) {
  if ($post->post_type == 'evc_poll') {
    $meta = get_post_meta($post->ID, 'evc_poll', true);
    $out = array();
    
    if (isset($actions['edit'])) {
      $out['edit'] = $actions['edit'];
      $out['trash'] = $actions['trash'];
    }    
    if (isset($actions['delete'])) {
      $out['delete'] = $actions['delete'];
      $out['untrash'] = $actions['untrash'];
    }
    $actions = $out;
  }
  return $actions;
}


add_filter('bulk_actions-edit-evc_poll', 'evc_poll_bulk_actions' );
function evc_poll_bulk_actions ($actions) {
  //print__r($actions);
  unset($actions['edit']);
  return $actions;
}

function evc_poll_edit_columns ($columns) {
  $columns = array(
    "cb" => "<input type=\"checkbox\" />",
    'title' => 'Вопрос опроса',
    'evc_poll_shortcode' => 'Код опроса',
  );
    
  return $columns;  
}

function evc_poll_columns($column, $post_id) {
  global $post;

  $meta = get_post_meta($post->ID, 'evc_poll', true);
  if(!$meta )
    $meta['response'] = get_post_meta($post->ID, 'poll_response', true);
  
  $meta['response'] = maybe_unserialize($meta['response']);
  switch ($column){
    case "title":
      echo $post->post_title;
    break;    
    case "evc_poll_shortcode":
      echo '[vk_poll id="' . $meta['response']['id'] . '"]';
    break;
  }
}

function evc_poll_wp_insert_post($post_id, $post = null) {
  if ($post->post_type == "evc_poll") {
      
    // Loop through the POST data
    $meta_fields = array("evc_poll");
    foreach ($meta_fields as $key) {
      
      $value = @$_POST[$key];
      if (empty($value)) {
        delete_post_meta($post_id, $key);
        continue;
      }
      
      if ($key == 'evc_poll') {
        if (isset($value['response']) && !empty($value['response']))
          $value['response'] = evc_poll_edit_poll($value);        
        else
          $value['response'] = evc_poll_add_poll($value);        
      }

	    if ( ! update_post_meta( $post_id, $key, $value ) ) {
		    // Or add the meta data
		    add_post_meta( $post_id, $key, $value, true );
	    }
	    if ( ! empty( $value['response']['id'] ) ) {
		    $metas['evc_poll_id'] = $value['response']['id'];
	    }
	    if ( ! empty( $value['response']['owner_id'] ) ) {
		    $metas['evc_poll_owner_id'] = $value['response']['owner_id'];
	    }

	    if ( ! empty( $metas ) ) {
		    evc_add_or_update_post_metas( $metas, $post_id );
	    }
    }
  }
}

// Template selection
function evc_poll_template_redirect() {
  global $wp;
  if (isset($wp->query_vars["post_type"]) && $wp->query_vars["post_type"] == "evc_poll") {
    die();
  }
}

function evc_poll_cpt_admin_init () {
  add_meta_box("evc_poll", "Опрос ВКонтакте", 'evc_poll_meta_option', "evc_poll", "advanced", "high");
  add_meta_box("evc_ad", "Обратите внимание!", 'evc_poll_ad', "evc_poll", "side");
}

function evc_poll_ad () {
  evc_ad();
}

function evc_poll_meta_option() {
  global $post;

  $options = get_option('evc_vk_api_autopost');    
  
  $defaults = array(
    'is_anonymous' => '0',
    'share' => '1',
    'response' => '',
    'question' => '',
    'answers' => ''
  );
  $custom = get_post_custom($post->ID);
  //print__r($custom);
  if (isset($custom['evc_poll'])) {
    $custom = maybe_unserialize($custom['evc_poll'][0]);
    $args = wp_parse_args($custom, $defaults);  
  }
  else
    $args = $defaults;
  
  if (isset($args['response']) && !empty($args['response']))  
    $args['response'] = maybe_unserialize($args['response']); // !!!
    

?>

<table class="form-table">
  <tbody>
    <?php if (!isset($options['access_token']) || empty($options['access_token'])) { ?>
    <tr>
      <th scope="row"></th>
      <td>
      <?php 
        echo '<div class="error"><p>Необходимо настроить API ВКонтакте. Откройте вкладку "<a href="'.admin_url('admin.php?page=evc').'">Для автопостинга</a>".</p></div>';
      ?>
      </td>
    </tr>  
    <?php } ?>      
    <?php if (isset($args['response']['id'])) { ?>
    <tr>
      <th scope="row">Виджет</th>
      <td>
      <?php      
      echo'<div id = "evc_poll-'.$args['response']['id'].'"></div>
      <script type="text/javascript">  
        VK.Widgets.Poll("evc_poll-'.$args['response']['id'].'", {},'.$args['response']['id'].');
      </script>';      
      ?>
      </td>
    </tr>  
    <tr>
      <th scope="row">Шорткод</th>
      <td><p><code>[vk_poll id="<?php echo $args['response']['id']; ?>"]</code></p>
        <p class="description">Добавьте этот шорткод в запись, чтобы разместить опрос.</p>
      </td>
    </tr>
    <?php } ?>
    <tr>
      <th scope="row">Вопрос</th>
      <td>
        <input type="hidden" value='<?php echo maybe_serialize($args['response']); ?>' name="evc_poll[response]" id="evc_poll[response]">
        <input type="text" value="<?php echo $args['question']; ?>" name="evc_poll[question]" id="evc_poll[question]" class="regular-text">
        <p class="description">Текст вопроса.</p>
      </td>
    </tr>
    <tr>
      <th scope="row">Ответы</th>
      <td>
        <textarea name="evc_poll[answers]" id="evc_poll[answers]" class="regular-text" cols="55" rows="5"><?php echo $args['answers']; ?></textarea>
        <p class="description">Варианты ответов.
        <br/>Каждый вариант ответа <strong>с новой строки.</strong>.
        <br/><strong>Недопустимы:</strong> кавычки двойные и одинарные ("').</p>
      </td>
    </tr>
    <tr>
      <th scope="row">Приватность</th>
      <td>
        <input type="radio" value="1" name="evc_poll[is_anonymous]" id="evc_poll[is_anonymous][1]" class="radio" <?php echo checked( $args['is_anonymous'], '1', false ); ?>>
        <label for="evc_poll[is_anonymous][1]">Анонимный опрос <small>/ список проголосовавших недоступен</small>
        </label>
        <br>
        <input type="radio" value="0" name="evc_poll[is_anonymous]" id="evc_poll[is_anonymous][0]" class="radio" <?php echo checked( $args['is_anonymous'], '0', false ); ?>>
        <label for="evc_poll[is_anonymous][0]">Публичный опрос <small>/ список проголосовавших доступен</small>
        </label>
        <br>
        <p class="description">Будет ли доступен список проголосовавших в опросе.</p>
      </td>
    </tr>
    <tr>
      <th scope="row">Поделиться</th>
      <td>
        <select id="evc_poll[share]" name="evc_poll[share]" class="regular">
          <option value="0" <?php echo selected( $args['share'], '0', false ); ?>>Нет</option>
          <option value="1" <?php echo selected( $args['share'], '1', false ); ?>>Да</option>
        </select>
        <p class="description">Опрос смогут увидеть <strong>сотни</strong> пользователей плагина EVC.
        <br/>Вы сожете получить сотни голосов, благодаря чему опрос будет выглядеть более привлекательно.
        <br/><a href = "<?php echo admin_url('edit.php?post_type=evc_poll&page=evc-polls'); ?>">Все опросы ВКонтакте</a>.</p>
      </td>
    </tr>
  </tbody>
</table>
<?php
}


function evc_poll_updated_messages( $messages ) {
  global $post, $post_ID;
  $messages['evc_poll'] = array(
    0 => '', 
    1 => sprintf( __('Poll updated.' ) ),
    2 => __('Poll options updated.'),
    3 => __('Poll options updated.'),
    4 => __('Poll updated.'),
    5 => isset($_GET['revision']) ? sprintf( __('Poll restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Poll published.') ),
    7 => __('Poll saved.'),
    8 => sprintf( __('Poll up submitted.') ),
    9 => sprintf( __('Poll scheduled for: <strong>%1$s</strong>.'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ) ),
    10 => sprintf( __('Poll draft updated.' ) ),
  );
  return $messages;
}
add_filter( 'post_updated_messages', 'evc_poll_updated_messages' );


function evc_poll_vk_polls_create($params = array()) {
  $options = evc_get_all_options(array(
    'evc_vk_api_autopost'
  ));
  if (!isset($options['access_token']) || empty($options['access_token']))  
    return false;
    
  $default = array(
    'access_token' => $options['access_token'],
    'is_anonymous' => '0',
    'v' => '5.21'
  );
  $params = wp_parse_args($params, $default);
  $params = apply_filters('evc_poll_vk_polls_create', $params);     
  //print__r($params);
  //return false;
  
  $query = http_build_query($params);
  
  // VK API REQUEST
  $data = wp_remote_post(EVC_API_URL.'polls.create?'.$query, array('sslverify' => false) ); 
  
  if (is_wp_error($data)) {
    evc_add_log('evc_poll_vk_polls_create: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());        
    return false;
  }

  $resp = json_decode($data['body'],true);
  
  if (isset($resp['error'])) {   
    if (isset($resp['error']['error_code'])) {
      if ($resp['error']['error_code'] == 17 && isset($resp['error']['redirect_uri']) )
        evc_add_log('evc_poll_vk_polls_create: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg'] . ' Redirect uri: <a href= "'. $resp['error']['redirect_uri'].'" target = "_blank">'. $resp['error']['redirect_uri'].'</a>');           
      else      
        evc_add_log('evc_poll_vk_polls_create: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);           
    }
    else
      evc_add_log('evc_poll_vk_polls_create: VK Error. ' . $resp['error']);           
    return false; 
  }   
  
  evc_add_log('evc_poll_vk_polls_create: VK API');
  
  return $resp['response'];  
}

function evc_poll_add_poll($data) {
  
  if (empty($data['answers']))
    return false;
  
  if (false === ($answers = evc_poll_answers_helper($data['answers'])))
    return false;
      
  $answers = '["'.implode('","', $answers) . '"]';
  $out = evc_poll_vk_polls_create(array(
    'question' => trim($data['question']),
    'add_answers' => $answers
  ));
  
  if ($out) {
    evc_poll_share($out, $data);
  }
  
  return $out;
}

function evc_poll_answers_helper ($str) {
  if(empty($str))
    return false;
    
  $answers = explode("\n", $str);  
  if (count($answers) > 10)
    $answers = array_slice($answers, 0, 10);
  
  foreach($answers as $answer) {
    $answer = trim($answer);
    if (!empty($answer))
      $out[] = $answer;
  }

  return $out;
}

function evc_poll_edit_poll($data) {
  $data['response'] = maybe_unserialize(stripslashes($data['response']));
  
  if (false === ($answers = evc_poll_answers_helper($data['answers'])))
    return false;
  
  // Edit Question
  $question = null;
  if ($data['response']['question'] != $data['question'])
    $question = $data['question'];
  
  // Edit Answers
  $edit_answers = '';
  $i = 0;
  foreach($data['response']['answers'] as $answer_arr) {
    if(isset($answers[$i]) && $answer_arr['text'] != $answers[$i])
      $edit_answers[] = '"' . $answer_arr['id'] . '":"' . trim($answers[$i]) . '"';   
    $i++;
  }
  if (!empty($edit_answers))
    $edit_answers = '{' . implode(',', $edit_answers) . '}';
  
  $delta = count($answers) - count($data['response']['answers']);

  // Add Answers
  $add_answers = '';
  if ($delta > 0) {
    $add_answers_raw_arr = array_slice($answers, -1 * $delta);

    foreach($add_answers_raw_arr as $add_answers_raw)
      $add_answers[] = '"' . $add_answers_raw . '"';
  }
  if (!empty($add_answers))
    $add_answers = '[' . implode(',', $add_answers) . ']';
  
  
  // Delete Answers
  $delete_answers = '';
  if ($delta < 0) {
    $delete_answers_arr_arr = array_slice($data['response']['answers'], -1, -1 * $delta);  
    foreach($delete_answers_arr_arr as $delete_answers_arr)
      $delete_answers[] = $delete_answers_arr['id'];
  }
  if (!empty($delete_answers))
    $delete_answers = '[' . implode(',', $delete_answers) . ']';

  if (!empty($question) || !empty($edit_answers) || !empty($add_answers) || !empty($delete_answers)) {   
    $res = evc_poll_vk_polls_edit(
      array(
        'owner_id' => (int)$data['response']['owner_id'],
        'poll_id' => $data['response']['id'],
      ) + 
      compact('question','add_answers','edit_answers','delete_answers')
    );    
    if ($res == 1) {
      $out = evc_poll_vk_polls_get_by_id(array(
        'owner_id' => $data['response']['owner_id'],
        'poll_id' => $data['response']['id'],
      ));
      if ($out) {
        evc_poll_share($out, $data);
        return maybe_serialize($out);
      }
    }
  }
  return maybe_serialize($data['response']);
}

function evc_poll_vk_polls_edit($params = array()) {
  $options = evc_get_all_options(array(
    'evc_vk_api_autopost'
  ));
  if (!isset($options['access_token']) || empty($options['access_token']))  
    return false;
    
  $default = array(
    'access_token' => $options['access_token'],
    'v' => '5.21'
  );
  $params = wp_parse_args($params, $default);
  $params = apply_filters('evc_poll_vk_polls_edit', $params);     
  //print__r($params);
  //return false;
  
  $query = http_build_query($params);
  
  // VK API REQUEST
  $data = wp_remote_post(EVC_API_URL.'polls.edit?'.$query, array('sslverify' => false) ); 
  
  if (is_wp_error($data)) {
    evc_add_log('evc_poll_vk_polls_edit: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());        
    return false;
  }

  $resp = json_decode($data['body'],true);
  
  if (isset($resp['error'])) {   
    if (isset($resp['error']['error_code'])) {
      evc_add_log('evc_poll_vk_polls_edit: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);           
    }
    else
      evc_add_log('evc_poll_vk_polls_edit: VK Error. ' . $resp['error']);           
    return false; 
  }   
  
  evc_add_log('evc_poll_vk_polls_edit: VK API');
  
  return $resp['response'];  
}

function evc_poll_vk_polls_get_by_id($params = array()) {
  $options = evc_get_all_options(array(
    'evc_vk_api_autopost'
  ));
  if (!isset($options['access_token']) || empty($options['access_token']))  
    return false;
    
  $default = array(
    'access_token' => $options['access_token'],
    'v' => '5.21'
  );
  $params = wp_parse_args($params, $default);
  $params = apply_filters('evc_poll_vk_polls_get_by_id', $params);     
  //print__r($params);
  //return false;
  
  $query = http_build_query($params);
  
  // VK API REQUEST
  $data = wp_remote_post(EVC_API_URL.'polls.getById?'.$query, array('sslverify' => false) ); 
  
  if (is_wp_error($data)) {
    evc_add_log('evc_poll_vk_polls_get_by_id: WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());        
    return false;
  }

  $resp = json_decode($data['body'],true);
  
  if (isset($resp['error'])) {   
    if (isset($resp['error']['error_code'])) {
      evc_add_log('evc_poll_vk_polls_get_by_id: VK Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);           
    }
    else
      evc_add_log('evc_poll_vk_polls_get_by_id: VK Error. ' . $resp['error']);           
    return false; 
  }   
  
  evc_add_log('evc_poll_vk_polls_get_by_id: VK API');
  
  return $resp['response'];  
}

add_action('evc_vk_async_init', 'evc_poll_vk_async_init');
function evc_poll_vk_async_init() {
  ?>
  //console.log(VKWidgetsPolls);
  // Polls
    if (typeof VKWidgetsPolls !== 'undefined' ) {
      //console.log(VKWidgetsPolls);
      for (index = 0; index < VKWidgetsPolls.length; ++index) {
        VK.Widgets.Poll(
          VKWidgetsPolls[index].element_id, 
          VKWidgetsPolls[index].options, 
          VKWidgetsPolls[index].poll_id
        );
      }
    <?php  
      do_action('evc_comments_vk_async_init');
    ?>    
    }
<?php
}

function evc_poll_vk_widget ($poll_id, $args = array(), $element_id = null) {  
  global $post;
  $options = get_option('evc_poll');  
  
  if (!isset($element_id))
    $element_id = 'evc-poll-' . $poll_id;
  $o = array();
  $o = wp_parse_args($args, $o);
  $o = evc_vk_widget_data_encode($o);
  
  $out = '
<script type="text/javascript">
  VKWidgetsPolls.push ({
    element_id: "'.$element_id.'",
    options: '.$o .',
    poll_id: "'.$poll_id .'"';
  $out .= '
  });    
</script>';

    $out .= '<div class = "vk_widget_polls" id = "'.$element_id.'"></div>  
  ';
  
  return $out;
}

add_shortcode('vk_poll', 'evc_poll_shortcode');
function evc_poll_shortcode ($atts = array(), $content = '') {
  global $post;
    
  if (!empty($atts))
    extract ($atts);

  if (!isset($id) || empty($id))        
    return $content;
  
  $out = evc_poll_vk_widget($id);
  
  return $out;
}


/**
 * VK.Widgets.Polls Class
 */
class VK_Widget_Polls extends WP_Widget {

  function __construct() {
    $widget_ops = array('classname' => 'vk_poll', 'description' => __( 'Виджет выводит опросы ВКонтакте.') );
    parent::__construct('vk_poll', __('VK Опросы'), $widget_ops);
  }

  function widget( $args, $instance ) {
    extract( $args );
    $title = !empty($instance['title']) ? $instance['title'] : false;
    
    if ($instance['from'] == 'my') {
    $params = array(
      'post_type' => 'evc_poll',
      'posts_per_page' => $instance['count']
    );  
    
    $out = get_transient('evc_poll_widget-' . $this->id);
    if (!isset($out) || !$out || empty($out)) {
      
      $res = get_posts ($params);    
      if (!$res || empty($res))
        $out = 'Опросов не найдено.';
      else {
        foreach($res as $r) {
          $meta = array();
          $meta = get_post_meta($r->ID, 'evc_poll', true);
          if (!empty($meta)) {
            $meta['response'] = maybe_unserialize($meta['response']);
            $polls[] = evc_poll_vk_widget($meta['response']['id']);
          }
        }
      }
      if (isset($polls) && !empty($polls))
        $out = implode("\n", $polls);
      
      set_transient('evc_poll_widget-' . $this->id, $out, HOUR_IN_SECONDS);
    }
    }
 

    if ($instance['from'] == 'all') {
    
      $from_all_args = array(
        'limit' => $instance['count']
      );
     
      $out = get_transient('evc_poll_widget_all-' . $this->id);
      if (!isset($out) || !$out || empty($out)) {
        
        $res = evc_poll_get_polls( $from_all_args );;    
        if (!$res || empty($res) || !isset($res['items']))
          $out = 'Опросов не найдено.';
        else {
          foreach($res['items'] as $r) {
            $polls[] = evc_poll_vk_widget($r['id']);
          }
        }
        if (isset($polls) && !empty($polls))
          $out = implode("\n", $polls);
        
        set_transient('evc_poll_widget_all-' . $this->id, $out, 24 * HOUR_IN_SECONDS);
      }
    } 
   
    //print__r($data);
    echo $before_widget;
    if ( $title)
      echo $before_title . $title . $after_title;
    echo $out;
    echo $after_widget;
  }

  function update( $new_instance, $old_instance ) {
    //$instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['count'] = empty($new_instance['count']) ? 5 : $new_instance['count'];     
    
    if (false !== ($cache = get_transient('evc_poll_widget-'. $this->id)) && $new_instance['from'] == 'my')
      delete_transient('evc_poll_widget-'. $this->id);

    if (false !== ($all_cache = get_transient('evc_poll_widget_all-'. $this->id)) && $new_instance['from'] == 'all')
      delete_transient('evc_poll_widget_all-'. $this->id);
    
    $instance = wp_parse_args($instance, $new_instance);
    return $instance;
  }

  function form( $instance ) {
    //Defaults
    $instance = wp_parse_args( (array) $instance, array( 
      'title' => '', 
      'count' => '5', 
      'from' => 'my'
    ) );
    $title = esc_attr( $instance['title'] );

  ?> 
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Заголовок виджета:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>  
    
    <p>
      <label for="<?php echo $this->get_field_id('from'); ?>"><?php _e( 'Показать:' ); ?></label>
      <select name="<?php echo $this->get_field_name('from'); ?>" id="<?php echo $this->get_field_id('from'); ?>" class="widefat">
        <option value="my"<?php selected( $instance['from'], 'my' ); ?>><?php _e('Только мои опросы'); ?></option>
        <option value="all"<?php selected( $instance['from'], 'all' ); ?>><?php _e('Все опросы'); ?></option>
      </select>
    </p>    

    <p>
      <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Показать опросов:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $instance['count']; ?>" />
      <small>Сколько опросов показать.</small>
    </p>                    

    <div style = "border-width:1px 1px 1px 4px; border-color:#DDDDDD #DDDDDD #DDDDDD #2EA2CC; border-style: solid; background-color: #F7FCFE; padding: 1px 12px; margin-bottom:13px;" ><p style = "margin: 0.5em 0 !important; padding: 2px !important; "><a href = "http://ukraya.ru/192/easy-vk-connect-1-3" target = "_blank">Руководство</a> и <a href = "http://ukraya.ru/196/easy-vkontakte-connect-1-3-support" target = "_blank">помощь</a> по настройке виджета.</p></div>

<?php
  }

}
function evc_poll_widgets_init() {
  register_widget('VK_Widget_Polls');
}
add_action('widgets_init', 'evc_poll_widgets_init');  


add_action('admin_head', 'evc_poll_head' );
function evc_poll_head () {
  $options = get_option('evc_vk_api_widgets');
  if (isset($options['site_app_id']) && !empty($options['site_app_id']))
    echo '<meta property="vk:app_id" content="'.trim($options['site_app_id']).'" />';
}    

function evc_poll_share ($res, $meta) {
  if (!isset($meta['share']) || !$meta['share'])
    return false;
  
  $res = maybe_serialize($res);
  //$res = addslashes($res);
  
  $args = array(
    'method' => 'share_poll',
    'response' => $res
  );
  $response = evc_api_post ($args);  
  return $response;
}

function evc_poll_get_polls ($params = array()) {

  $args = array(
    'method' => 'get_polls'
  );
  $args = wp_parse_args($params, $args);  
  $res = evc_api_post ($args);  
  
  $out = false;
  if (!empty($res) && is_array($res) && isset($res['items'])) {
    foreach($res['items'] as $r) {
      $out['items'][] = maybe_unserialize($r);
    }
    $out['count'] = $res['count'];
  }
  
  return $out;   
}

function evc_api_post ($args) {
    
  $params = array(   
    'body' => $args,
    'user-agent' => 'EVCAPI/' . evc_version() . '; ' . site_url(),
    'host' => get_bloginfo('url')
    );

  $data = wp_remote_post(EVC_API, $params, array('sslverify' => false) );  
  
  if (is_wp_error($data)) {
    evc_add_log('EVC API '.$args['method'].': WP ERROR. ' . $data->get_error_code() . ' '. $data->get_error_message());
    return false;
  }
  
  $resp = json_decode($data['body'],true);
  
  if (isset($resp['error'])) {   
    if (isset($resp['error']['error_code']))
      evc_add_log('EVC API '.$args['method'].': Error. ' . $resp['error']['error_code'] . ' '. $resp['error']['error_msg']);          
    return false; 
  }  

  if (isset($resp['response']) && !empty($resp['response']))
    return $resp['response'];
  else
    return false;   
}

/* WP_LIST_TABLE */
if( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EVC_Polls_List_Table extends WP_List_Table {
  
  function __construct(){
    global $status, $page;

    parent::__construct( array(
      'singular' => __( 'poll', 'wpgu' ), //singular name of the listed records
      'plural' => __( 'polls', 'wpgu' ), //plural name of the listed records
      'ajax' => false //does this table support ajax?
    ) );

    add_action( 'admin_head', array( &$this, 'admin_header' ) );

  }

  function admin_header() {
    $page = ( isset($_GET['page'] ) ) ? esc_attr( $_GET['page'] ) : false;
    if( 'evc-polls' != $page )
      return;
    
    echo '<style type="text/css">';
    echo '</style>';
  }

  function no_items() {
    _e( 'Опросов не найдено.', 'wpgu' );
  }

  function has_items() {
    return $this->items;
  }  
  
  function column_default( $item, $column_name ) {
    switch( $column_name ) {
      case 'icon':
      case 'title':
        return $item[ $column_name ];
      default:
        return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
    }
  }

  function get_columns(){
    $columns = array(
      'title' => __( 'Вопрос опроса', 'wpgu' ),
      'code' => __( 'Код опроса', 'wpgu' ),      
      'date' => __( 'Добавлен', 'wpgu' ),                  
    );
    return $columns;
  }

  function column_title($item){
    return sprintf('%1$s', $item['question']);
  }


  function prepare_items() {

    $this->_column_headers = $this->get_column_info();
   
    $per_page = $this->get_items_per_page( 'polls_per_page' );
    $current_page = $this->get_pagenum();
    
    $args = array(
      'limit' => $per_page,
      'offset' => ($current_page - 1)*$per_page
    );
    if (isset($_GET['s']) && !empty($_GET['s']) )
      $args['s'] = trim($_GET['s']);
    
    $polls = evc_poll_get_polls( $args );

    //print__r($args);    
    //print__r($polls);
    $this->items = $polls['items'];
    
    $total_items = $polls['count'];

    $this->set_pagination_args( array(
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page' => $per_page //WE have to determine how many items to show on a page
    ) );
    
  }

 
  function display_rows() {
    $polls = $this->items;

    foreach($polls as $g) {
      
      $alt = ( isset($alt) && 'alternate' == $alt ) ? '' : 'alternate'; 
      echo '<tr id="poll-'.$g['id'].'" class="'. $alt . '" valign="top" data-poll-id = "'.$g['id'].'">';  
      
      list( $columns, $hidden ) = $this->get_column_info();

      foreach ( $columns as $column_name => $column_display_name ) {
        $class = "class='$column_name column-$column_name'";

        $style = '';
        if ( in_array( $column_name, $hidden ) )
          $style = ' style="display:none;"';

        $attributes = $class . $style;
        $out = array();
        $actions = array();
        switch ( $column_name ) {          

          case 'title':         
            echo '
            <td '.$attributes.'>'.$g['question'];
            
            echo '</td>';

          break;  
          
          case 'code':               
            //echo '<td '.$attributes.'>[evc_poll id="'.$g['id'].'"]</td>';
            echo '<td '.$attributes.'>[vk_poll id="'.$g['id'].'"]</td>';
          break;                      
          
          case 'date':          
            $added = strtotime($g['created']);
            echo '<td '.$attributes.'>'. human_time_diff(date('U', $g['created'])) .'</td>';
          break;                             
                    
                
        } // switch $column_name      
      
      
      } // foreach $columns
      
      echo '</tr>';
    } // foreach 
    
  }  
    
  function get_items (){
    return $this->items['polls'];
  }

} //class

function evc_poll_screen_options() {
  global $evc_poll_list_table;
  $option = 'per_page';
  $args = array(
         'label' => 'Polls',
         'default' => 20,
         'option' => 'polls_per_page'
         );
  add_screen_option( $option, $args );
  
  if (!empty($_REQUEST['_wp_http_referer'])) {
    wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI']) ) );
    exit();
  }
  
  $evc_poll_list_table = new EVC_Polls_List_Table();
}

add_filter('set-screen-option', 'evc_poll_filter_screen_options', 10, 3);
function evc_poll_filter_screen_options($status, $option, $value) {
  if ( 'polls_per_page' == $option ) return $value;
  return $value;
}

function evc_poll_list_polls() {
  global $evc_poll_list_table;
  // http://wpengineer.com/2426/wp_list_table-a-step-by-step-guide/
  // http://codex.wordpress.org/Class_Reference/WP_List_Table
  // http://wp.smashingmagazine.com/2011/11/03/native-admin-tables-wordpress/

  $pagenum = $evc_poll_list_table->get_pagenum();
  $evc_poll_list_table->prepare_items();
  
  echo '<div class="wrap">';
  echo '<h2>' . __( 'Все опросы ВКонтакте', 'evc' ) . '</h2>';

  echo '<br class="clear">';    
  echo '
  <div id="col-container">
    <div id="col-right">
      <div class="col-wrap">
      <form id="posts-filter" method="get" action="" >
        <input type="hidden" name="page" value="evc-polls">
        <input type="hidden" name="post_type" value="evc_poll">
  ';
        
    $evc_poll_list_table->display();
  echo '
      </form>
      </div> 
  </div> 
    <div id="col-left">
      <div class="col-wrap"> 
        <h3>Виджет опроса</h3>
        <div id = "evc-poll-widget"><p>Наведите курсор на название опроса, чтобы появился виджет.</p></div>
      </div> 
  </div>
  </div> 
  ';
  echo '</div>'; // wrap
}

add_filter( 'evc_admin_pointers-dashboard', 'evc_register_pointer' );
add_filter( 'evc_admin_pointers-plugins', 'evc_register_pointer' );
add_filter( 'evc_admin_pointers-post', 'evc_register_pointer' );
function evc_register_pointer( $p ) {
  $p['xyzh14101'] = array(
    'target' => '#menu-posts-evc_poll',
    'options' => array(
      'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
        __( 'EVC Опросы ВКонтакте' ,'evc'),
        __( 'Теперь вы можете <strong>создать</strong> опрос ВКонтакте, <strong>разместить</strong> его на своем сайте и <strong>поделиться</strong> с другими!','evc')
      ),
      'position' => array( 'edge' => 'left', 'align' => 'right' )
    )
  );
  $p['evc_lock'] = array(
    'target' => '#toplevel_page_evc',
    'options' => array(
      'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
        __( 'Социальный замок' ,'evc'),
        __( 'Новая возможность: закройте часть поста на замок. Чтобы увидеть закрытое, посетитель должен подписаться на группу ВКонтакте.','evc')
      ),
      'position' => array( 'edge' => 'left', 'align' => 'right' )
    )
  );  
  
  $p['evc_autopost_online_stats'] = array(
    'target' => '#toplevel_page_evc',
    'options' => array(
      'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
        __( 'Уникальная статистика' ,'evc'),
        __( 'Больше всего внимания привлекают записи, опубликованные в группе в момент, когда большинство подписчиков находятся онлайн. Плагин позволяет собрать такую <a href = "'.admin_url('admin.php?page=evc-autopost').'">статистику</a> и рассчитать наиучшее время для публикаций.
        <br/><a href = "'.admin_url('admin.php?page=evc-autopost').'">Перейти</a>  к статистике.','evc')
      ),
      'position' => array( 'edge' => 'left', 'align' => 'right' )
    )
  );  
  
  $p['evc_widget_buttons'] = array(
    'target' => '#toplevel_page_evc',
    'options' => array(
      'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
        __( 'Кнопки "Поделиться"' ,'evc'),
        __( 'Шок: 7 сетей, интерактивный настройщик, 4 темы для кнопок и множество вариантов отображения.
        <br/>Открыть <a href = "'.admin_url('admin.php?page=evc-widgets#evc_widget_buttons').'">настройки для кнопок</a>.','evc')
      ),
      'position' => array( 'edge' => 'left', 'align' => 'right' )
    )
  );    

  $p['evc_sidebar_getaway'] = array(
    'target' => '#toplevel_page_evc',
    'options' => array(
      'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
        __( 'Сайдбары останавливают уходящих посетителей!' ,'evc'),
        __( 'Сайдбары могут появляться, когда посетитель пытается покинуть ваш сайт. Это позволяет задержать их на странице и повысить конверсию на 17-23%.
        <br/>В <a href = "'.admin_url('admin.php?page=evc-sidebar#evc_sidebar_overlay').'">настройках</a> для всплывающего или выезжающего сайдбаров установите в опции <em>Появляется: Уход</em>.','evc')
      ),
      'position' => array( 'edge' => 'left', 'align' => 'right' )
    )
  );

  $p['evc_widget_comments_notify'] = array(
      'target' => '#toplevel_page_evc',
      'options' => array(
          'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
              __( 'Комментарии не пропадают!' ,'evc'),
              __( 'Оповещения о комментариях, оставленных через виджет комментариев ВК, теперь приходят на почту.
        <br/>А на вкладке "<a href = "'.admin_url('admin.php?page=evc-comments#evc_comments_mod').'">Обзор комментариев</a>" отображаются все комментарии из виджета.','evc')
          ),
          'position' => array( 'edge' => 'left', 'align' => 'right' )
      )
  );

  if (current_time('timestamp', 1) < strtotime( '2015-01-03 23:59:59' ) ) {
    $p['evc_new_year'] = array(
      'target' => '#toplevel_page_evc',
      'options' => array(
        'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
          __( '-30%: Новогодние скидки на все плагины!' ,'evc'),
          __( 'Только 5 дней вы можете сделать <a href = "http://ukraya.ru/162/vk-wp-bridge">сайт из группы ВКонтакте</a> в один клик, автоматически <a href = "http://ukraya.ru/421/evc-pro">размещать все записи с сайта в группе ВК</a> в прайм-тайм и создать <a href = "http://ukraya.ru/316/vk-wp-video-pro">киносайт из видеоальбомов ВКонтакте</a> на <b>30%</b> дешевле, чем обычно.','evc')
        ),
        'position' => array( 'edge' => 'left', 'align' => 'right' )
      )
    );    
  }
  
  return $p;
}

add_action( 'admin_enqueue_scripts', 'evc_pointer_load', 1000 );
function evc_pointer_load( $hook_suffix ) {
  if ( get_bloginfo( 'version' ) < '3.3' )
    return;
 
  $screen = get_current_screen();
  $screen_id = $screen->id;
 
  $pointers = apply_filters( 'evc_admin_pointers-' . $screen_id, array() );
 
  if ( ! $pointers || ! is_array( $pointers ) )
    return;
 
  $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
  $valid_pointers =array();
 
  foreach ( $pointers as $pointer_id => $pointer ) {
 
    if ( in_array( $pointer_id, $dismissed ) || empty( $pointer )  || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) )
      continue;
 
    $pointer['pointer_id'] = $pointer_id;
 
    $valid_pointers['pointers'][] =  $pointer;
  }
 
  if ( empty( $valid_pointers ) )
    return;
 
  wp_enqueue_style( 'wp-pointer' );
  wp_enqueue_script( 'evc-pointer', plugins_url( 'js/evc-pointer.js', __FILE__ ), array( 'wp-pointer' ));
  wp_localize_script( 'evc-pointer', 'evcPointer', $valid_pointers );
}


function evc_poll_wall_post_attachments_filter( $attachments, $post ) {

	$pattern = get_shortcode_regex();
	preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches );
	if ( ! empty( $matches[3] ) ) {

		$atts = shortcode_parse_atts( $matches[3][0] );
		if ( ! empty( $atts['id'] ) ) {

			$owner_id = evc_poll_get_poll_owner_id( $atts['id'] );
			if ( $owner_id ) {
				$attachments .= ',poll' . $owner_id . '_' . $atts['id'];
			}
		}
	}

	return $attachments;
}

add_filter( 'evc_wall_post_attachments', 'evc_poll_wall_post_attachments_filter', 10, 2 );


function evc_poll_get_poll_owner_id( $poll_id ) {
	global $wpdb;

	$res = $wpdb->get_var( "
	    SELECT post_id
	    FROM " . $wpdb->prefix . "postmeta
	    WHERE meta_key = 'evc_poll_id' AND meta_value = $poll_id
    " );
	//print__r($res);
	//$wpdb->show_errors();
	//$wpdb->print_error();
	if ( ! empty( $res ) ) {
		$owner_id = get_post_meta( $res, 'evc_poll_owner_id', true );
		if ( ! empty( $owner_id ) ) {
			return $owner_id;
		}
	}

	return false;
}

if(!function_exists('evc_add_or_update_post_metas')) {
	function evc_add_or_update_post_metas( $metas, $post_id ) {
		foreach ( (array) $metas as $key => $value ) {
			if ( ! update_post_meta( $post_id, $key, $value ) ) {
				add_post_meta( $post_id, $key, $value, true );
			}
		}
	}
}