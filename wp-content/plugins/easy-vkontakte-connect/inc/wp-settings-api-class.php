<?php

/**
 * weDevs Settings API wrapper class
 *
 * @author Tareq Hasan <tareq@weDevs.com>
 * @link http://tareq.weDevs.com Tareq's Planet
 * @example settings-api.php How to use the class
 */
if ( !class_exists( 'WP_Settings_API_Class' ) ):
class WP_Settings_API_Class {

    /**
     * settings sections array
     *
     * @var array
     */
    private $settings_sections = array();

    /**
     * Settings fields array
     *
     * @var array
     */
    private $settings_fields = array();

    /**
     * Singleton instance
     *
     * @var object
     */
    private static $_instance;

    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }

    /**
     * Enqueue scripts and styles
     */
    function admin_enqueue_scripts() {
        wp_enqueue_style('thickbox');
        
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_script( 'thickbox' );
    }

    /**
     * Set settings sections
     *
     * @param array   $sections setting sections array
     */
    function set_sections( $sections ) {
        $this->settings_sections = $sections;

        return $this;
    }

    /**
     * Set option name
     *
     * @param array   $sections setting sections array
     */
    function set_option_name( $option_name ) {
        $this->option_name = $option_name;

        return $this;
    }    
    
    /**
     * Add a single section
     *
     * @param array   $section
     */
    function add_section( $section ) {
        $this->settings_sections[] = $section;

        return $this;
    }

    /**
     * Set settings fields
     *
     * @param array   $fields settings fields array
     */
    function set_fields( $fields ) {
        $this->settings_fields = $fields;

        return $this;
    }

    function add_field( $section, $field ) {
        $defaults = array(
            'name' => '',
            'label' => '',
            'desc' => '',
            'type' => 'text'
        );

        $arg = wp_parse_args( $field, $defaults );
        $this->settings_fields[$section][] = $arg;

        return $this;
    }

    function get_defaults() {
      $out = array();		
			
			foreach($this->settings_sections as $tabs) {
        if (empty($tabs['sections'])) 
          continue;
        foreach($tabs['sections'] as $section) {
					$section_tab[$section['id']] = $tabs['id'];
				}
			}
			
			//print__r($section_tab);
			//return false;
			if (!isset($this->settings_fields) || empty($this->settings_fields))
        return array();			
			
      foreach($this->settings_fields as $section => $fields) {
        foreach($fields as $field) {
					$tab = $section_tab[$section];
          if (isset($field['default'])) {
            if (in_array($field['type'], array('multicheck'))) {
              //print__r($field);
              if (is_array($field['default']))
                $out[$tab][$field['name']] = $field['default'];
              else
                $out[$tab][$field['name']] = array($field['default'] => $field['default']);
              
              //print__r($out);
            }
            else
              $out[$tab][$field['name']] = $field['default'];
          }
        }
      }
      return $out;
    }
    
		 
      
    /**
     * Initialize and registers the settings sections and fileds to WordPress
     *
     * Usually this should be called at `admin_init` hook.
     *
     * This function gets the initiated settings sections and fields. Then
     * registers them to WordPress and ready for use.
     */
    function admin_init() {
			
			// Set Defaults
      $defaults = $this->get_defaults();
			//print__r($defaults);			
			if ($defaults && !empty($defaults)) {
				foreach($defaults as $option => $values) {
					if (false == get_option($option)) {
						update_option($option, $values);
					}
				}
			}
			
      
      foreach($this->settings_sections as $tabs) {
        if (empty($tabs['sections'])) 
          continue;
        foreach($tabs['sections'] as $section) {
          if ( isset($section['desc']) && !empty($section['desc']) ) {
            $section['desc'] = '<p>'.$section['desc'].'</p>';
            $callback = create_function('', 'echo "'.str_replace('"', '\"', $section['desc']).'";');  
            $section_tab[$section['id']] = $tabs['id'];
          }
          else
            $callback = '__return_false';  
          //print__r($this->page);  
          // add_settings_section( $id, $title, $callback, $page );
          add_settings_section( $section['id'], $section['title'], $callback, $tabs['id'] );
        }
        //register_setting( $option_group, $option_name, $sanitize_callback );
				
				register_setting( $tabs['id'], $tabs['id'], array( $this, 'sanitize_options' ) );
      }   
                    
        //register settings fields
        foreach ( $this->settings_fields as $section => $field ) {
            foreach ( $field as $option ) {

                $type = isset( $option['type'] ) ? $option['type'] : 'text';
                $option['label'] = (isset($option['label'])) ? $option['label'] : '';
                $args = array(
                    'id' => $option['name'],
                    'desc' => isset( $option['desc'] ) ? $option['desc'] : '',
                    'name' => $option['name'],
                    'label' => $option['label'],
                    'readonly' => isset( $option['readonly'] ) ? true : false,
                    'section' => $section,
										'tab' => $section_tab[$section],
                    'size' => isset( $option['size'] ) ? $option['size'] : null,
                    'options' => isset( $option['options'] ) ? $option['options'] : '',
                    'default' => isset( $option['default'] ) ? $option['default'] : '',
                    'sortable' => isset( $option['sortable'] ) ? $option['sortable'] : false,
                    'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
                );
                //print__r($this->option_name . '[' . $option['name'] . ']' .'<br/>'. $option['label'].'<br/>'. array( $this, 'callback_' . $type ).'<br/>'. 'evc_bridge'.'<br/>'. $section);
                
                //add_settings_field( $id, $title, $callback, $page, $section, $args );					
								add_settings_field( $section_tab[$section] . '[' . $option['name'] . ']', $option['label'], array( $this, 'callback_' . $type ), $section_tab[$section], $section, $args );
            }
        }

        
    }

    /**
     * Displays a text field for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_text( $args ) {

        $value = esc_attr( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $readonly = $args['readonly'] ? 'readonly' : '';
        //print__r($args);
				
				$html = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" %5$s />', $size, $args['tab'], $args['name'], $value, $readonly );
        $html .= '<span class="spinner" style="display: none; float:none !important; margin: 0 5px !important;" id = "'.$args['tab'].'['.$args['name'].'][spinner]"></span>';
        $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );

        echo $html;
    }

    /**
     * Displays a text field for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_hidden( $args ) {

        $value = esc_attr( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $html = sprintf( '<input type="hidden" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"  />', $size, $args['tab'], $args['name'], $value );

        echo $html;
    }    
    
    /**
     * Displays a checkbox for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_checkbox( $args ) {

        $value = esc_attr( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );

        $html = sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
        $html .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s />', $args['tab'], $args['id'], $value, checked( $value, 'on', false ) );
        $html .= sprintf( '<label for="%1$s[%2$s]"> %3$s</label>', $args['tab'], $args['id'], $args['name'] );
        $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );
        echo $html;
    }

    /**
     * Displays a multicheckbox a settings field
     *
     * @param array   $args settings field args
     */
    function callback_multicheck( $args ) {
        //print__r($this->get_option( $args['name'], $args['section'], $args['default']));
        if (is_string($args['default']))
          $args['default'] = array($args['default'] => $args['default']);
          
        $value =  $this->get_option( $args['name'], $args['tab'], $args['default'] ) ;
        //print__r($value);
        //print__r($args);
        $temp = array();
        foreach ( $args['options'] as $key => $label ) {
          $html = '';  
          $checked = isset( $value[$key] ) ? $value[$key] : '0';
          $html .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s"%4$s />', $args['tab'], $args['name'], $key, checked( $checked, $key, false ) );
          $html .= sprintf( '<label for="%1$s[%2$s][%4$s]"> %3$s</label>', $args['tab'], $args['id'], $label, $key );
          
          if (isset($args['sortable']) && $args['sortable']) {
            $temp[] = '<div class = "item" '.sprintf( 'id="item_%1$s[%2$s][%3$s]"', $args['tab'], $args['name'], $key, checked( $checked, $key, false ) ).'>' . $html . '</div>';
          }
          else
            $temp[] = $html;
        }
        if (isset($args['sortable']) && $args['sortable']) {
          $out = '<div class = "sortable">'.implode("\n", $temp).'</div>';
        }
        else
          $out = implode('<br/>', $temp);
        $out .= sprintf( '<p class="description"> %s</p>', $args['desc'] );

        echo $out;
    }

    /**
     * Displays a multicheckbox a settings field
     *
     * @param array   $args settings field args
     */
    function callback_radio( $args ) {

        $value = esc_attr( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );

        $html = '';
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $args['tab'], $args['id'], $key, checked( $value, $key, false ) );
            $html .= sprintf( '<label for="%1$s[%2$s][%4$s]"> %3$s</label><br/>', $args['tab'], $args['id'], $label, $key );
        }
        $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );

        echo $html;
    }

    /**
     * Displays a selectbox for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_select( $args ) {

        $value = esc_attr( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

        $html = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['tab'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
        }
        $html .= sprintf( '</select>' );
        $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );

        echo $html;
    }

		
    /**
     * Displays a selectbox for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_select_category( $args ) {
        
      $value = esc_attr( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );
      $defaults = array(
        'hide_empty' => 0,
        'hide_if_empty' => false,
        'orderby' => 'name',
        'hierarchical' => true,
        'show_option_none' => __('None'),
        'echo' => 0,
        'name' => $args['tab'] .'['.$args['name'] . ']',
        'id' => $args['id'],
        'selected' => $value // category id
      );
      //if (!empty($args['options']))
        $cat_args = wp_parse_args((array)$args['options'], $defaults);
      //else
      //  $cat_args = $defaults;
        
      $out = wp_dropdown_categories($cat_args);      

      $out .= sprintf( '<p class="description">%s</p>', $args['desc'] );

      echo $out;
    }    

    /**
     * Displays a selectbox for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_select_category_checklist( $args ) {
      global $wc_name;  
			
      $value =  $this->get_option( $args['name'], $args['tab'], $args['default'] ) ;
      //print__r($value);
			$wc_name = $args['tab'] . '['.$args['name'] . ']';
			
			add_filter('wpsapi_checklist_name', array($this, 'select_category_checklist_name'));

			$defaults = array(
				'selected_cats'=>$value, 
				'walker' => new EVC_Walker_Checklist(),
				'checked_ontop' => false
      );
      
			echo '<div class = "categorydiv"><div class = "tabs-panel" style = "height:auto; max-height:200px;"><ul id="categorychecklist" class="list:category categorychecklist form-no-clear">';
	
			wp_terms_checklist( 0, $defaults );
			
            remove_filter('wpsapi_checklist_name', 'select_category_checklist_name');
      
			echo '</ul></div></div>';
			
      echo sprintf( '<p class="description">%s</p>', $args['desc'] );
    }  

		function select_category_checklist_name ($name) {
			global $wc_name;
			if (isset($wc_name)) {
				return $wc_name;
			}
			return $name;
		}
		
    /**
     * Displays a textarea for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_textarea( $args ) {

        $value =  $this->get_option( $args['name'], $args['tab'], $args['default'] );  
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

        $html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]">%4$s</textarea>', $size, $args['tab'], $args['id'], $value );
        $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );

        echo $html;
    }

    /**
     * Displays a textarea for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_html( $args ) {
        echo $args['desc'];
    }

    /**
     * Displays a rich text textarea for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_wysiwyg( $args ) {

        $value = wpautop( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : '500px';

        echo '<div style="width: ' . $size . ';">';

        wp_editor( $value, $args['tab'] . '[' . $args['name'] . ']', array( 'teeny' => true, 'textarea_rows' => 10 ) );

        echo '</div>';

        echo sprintf( '<p class="description">%s</p>', $args['desc'] );
    }

    /**
     * Displays a file upload field for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_file( $args ) {

        $value = esc_attr( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
        $id = $args['tab'] . '[' . $args['id'] . ']';
        $js_id = $args['section']  . '\\\\[' . $args['id'] . '\\\\]';
        $html = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['tab'], $args['id'], $value );
        $html .= '<input type="button" class="button wpsf-browse" id="'. $id .'_button" value="Browse" />
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $("#'. $js_id .'_button").click(function() {
                tb_show("", "media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true");
                window.original_send_to_editor = window.send_to_editor;
                window.send_to_editor = function(html) {
                    var url = $(html).attr(\'href\');
                    if ( !url ) {
                        url = $(html).attr(\'src\');
                    };
                    $("#'. $js_id .'").val(url);
                    tb_remove();
                    window.send_to_editor = window.original_send_to_editor;
                };
                return false;
            });
        });
        </script>';
        $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );

        echo $html;
    }

    /**
     * Displays a password field for a settings field
     *
     * @param array   $args settings field args
     */
    function callback_password( $args ) {

        $value = esc_attr( $this->get_option( $args['name'], $args['tab'], $args['default'] ) );
        $size = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

        $html = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['tab'], $args['id'], $value );
        $html .= sprintf( '<p class="description">%s</p>', $args['desc'] );

        echo $html;
    }

    /**
     * Sanitize callback for Settings API
     */
    function sanitize_options( $options ) {
        foreach( $options as $option_slug => $option_value ) {
            $sanitize_callback = $this->get_sanitize_callback( $option_slug );

            // If callback is set, call it
            if ( $sanitize_callback ) {
                $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
                continue;
            }

            // Treat everything that's not an array as a string
            if ( !is_array( $option_value ) ) {
                //$options[ $option_slug ] = sanitize_text_field( $option_value );
                
                continue;
            }
        }
        return $options;
    }

    /**
     * Get sanitization callback for given option slug
     *
     * @param string $slug option slug
     *
     * @return mixed string or bool false
     */
    function get_sanitize_callback( $slug = '' ) {
        if ( empty( $slug ) )
            return false;
        // Iterate over registered fields and see if we can find proper callback
        foreach( $this->settings_fields as $section => $options ) {
            foreach ( $options as $option ) {
                if ( $option['name'] != $slug )
                    continue;
                // Return the callback name
                return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
            }
        }
        return false;
    }

    /**
     * Get the value of a settings field
     *
     * @param string  $option  settings field name
     * @param string  $tab the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */
    function get_option( $option, $tab, $default = '' ) {

        //$options = get_option( $this->option_name );
				$options = get_option( $tab );
        //print__r($option);
				//print__r($this->option_name);
        //print__r($options);
        if (isset($options) && $options) {
					if ( isset( $options[$option] ) ) {
            //print__r($options[$option]);
						return $options[$option];
					}
				}
				else
					return $default;
    }

    /**
     * Show navigations as tab
     *
     * Shows all the settings section labels as tab
     */
    function show_navigation() {
        $html = '<h2 class="nav-tab-wrapper">';

        foreach ( $this->settings_sections as $tab ) {
            $html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
        }

        $html .= '</h2>';

        echo $html;
    }

    /**
     * Show the section settings forms
     *
     * This function displays every sections in a different form
     */
    function show_forms() {
        ?>
        <div class="wrap">
                <?php foreach ( $this->settings_sections as $form ) { ?>
                    <div id="<?php echo $form['id']; ?>" class="group">
                        <form method="post" action="options.php">

                            <?php do_action( 'wsa_form_top_' . $form['id'], $form ); ?>
                            <?php settings_fields( $form['id'] ); ?>
                            <?php do_settings_sections( $form['id'] ); ?>
                            <?php do_action( 'wsa_form_bottom_' . $form['id'], $form ); ?>
                            <?php
                              if (!isset($form['submit_button']) || $form['submit_button'] !== false){ 
                            ?>
                            <div style="padding-left: 10px">
                                <?php submit_button(); ?>
                            </div>
                            <?php
                              }
                            ?>
                        </form>
                    </div>
                <?php } ?>
        </div>
        <?php
        $this->script();
    }

    /**
     * Tabbable JavaScript codes
     *
     * This code uses localstorage for displaying active tabs
     */
    function script() {
        ?>
        <script>
            jQuery(document).ready(function($) {
              var page = '<?php echo (isset($_GET['page']) && !empty($_GET['page'])) ? $_GET['page'] : false; ?>';
              // Switches option sections
                $('.group').hide();
                var activetab = '';
                if (typeof(localStorage) != 'undefined' ) {
                    activetab = localStorage.getItem(page);
                    //console.log(page + '/' + activetab); 
                    //console.log (window.location.hash);
                    if (window.location.hash)
                      activetab = window.location.hash;
                }
                if (activetab != '' && $(activetab).length ) {
                    $(activetab).fadeIn();
                } else {
                    $('.group:first').fadeIn();
                }
                $('.group .collapsed').each(function(){
                    $(this).find('input:checked').parent().parent().parent().nextAll().each(
                    function(){
                        if ($(this).hasClass('last')) {
                            $(this).removeClass('hidden');
                            return false;
                        }
                        $(this).filter('.hidden').removeClass('hidden');
                    });
                });
             
                if (activetab != '' && $(activetab + '-tab').length ) {
                    $(activetab + '-tab').addClass('nav-tab-active');
                }            
                else {
                    $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                }
                $('.nav-tab-wrapper a').click(function(evt) {
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active').blur();
                    var clicked_group = $(this).attr('href');
                    if (typeof(localStorage) != 'undefined' ) {               
                      localStorage.setItem(page, $(this).attr('href'));                     
                    }
                    $('.group').hide();
                    $(clicked_group).fadeIn();
                    evt.preventDefault();
                    
                });
            });
        </script>
        <?php
    }

}
endif;
