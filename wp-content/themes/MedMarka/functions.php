<?php
add_action( 'woocommerce_after_subcategory_title', 'custom_add_product_description', 20);
function custom_add_product_description ($category) {
$cat_id        =    $category->term_id;
$prod_term    =    get_term($cat_id,'product_cat');
$description=    $prod_term->description;

}
/*Обновление плагинов и вордпрес*/
remove_action( 'wp_version_check', 'wp_version_check' );
remove_action( 'admin_init', '_maybe_update_core' );
add_filter( 'pre_transient_update_core', create_function( '$a', "return null;" ) ); 
remove_action( 'load-plugins.php', 'wp_update_plugins' );
remove_action( 'load-update.php', 'wp_update_plugins' );
remove_action( 'admin_init', '_maybe_update_plugins' );
remove_action( 'wp_update_plugins', 'wp_update_plugins' );
add_filter( 'pre_transient_update_plugins', create_function( '$a', "return null;" ) ); 
/*Обновление плагинов и вордпрес*/

/*Создание макета для бейджей*/
function wc_get_template_part_my($template, $slug, $name) {
    // имя нового файла шаблона
    $my_template = "beidj-content-single-product.php";
    // if ($slug == 'content' && $name == 'product') // если вызывается шаблон content-product.php
    if ($slug == 'content' && $name == 'product' && is_page(2)) // + если страница с ID = 2
        // файл шаблона в ВашаТема/content-product2.php или ВашаТема/woocommerce/content-product2.php
        $template = locate_template( array( $my_template, WC()->template_path() . $my_template));
    return $template;
}
add_filter('wc_get_template_part', 'wc_get_template_part_my',10,3);

function ua_sizemen_function() {
     return '<div class="table_size"><table class="size">
<tbody>
<tr>
<td colspan="9">
<div align="center">Класифікація типових фігур чоловічих</div></td>
</tr>
<tr class="razmer1">
<td rowspan="2">Розмір</td>
<td>44</td>
<td>46</td>
<td>48</td>
<td>50</td>
<td>52</td>
<td>54</td>
<td>56</td>
<td>58</td>
</tr>
<tr class="razmer2">
<td>S</td>
<td>M</td>
<td>L</td>
<td>XL</td>
<td>XXL</td>
<td>3XL</td>
<td>4XL</td>
<td>5XL</td>
</tr>
<tr class="grud">
<td>Обхват грудей</td>
<td>88</td>
<td>92</td>
<td>96</td>
<td>100</td>
<td>104</td>
<td>108</td>
<td>112</td>
<td>116</td>
</tr>
<tr class="tall">
<td>Обхват талії</td>
<td>76</td>
<td>80</td>
<td>84</td>
<td>88</td>
<td>92</td>
<td>96</td>
<td>100</td>
<td>104</td>
</tr>
</tbody>
</table></div> 
<a class="link_t" href="http://medshvetsmarka.com.ua/zakazat-onlajn/opredilenie-razmera/"> Як правильно визначити розмір?</a> <br>';
}
add_shortcode('size_men_ua', 'ua_sizemen_function');
function ru_sizemen_function() {
     return '<div class="table_size"><table class="size">
<tbody>
<tr>
<td colspan="9">
<div align="center">Классификация типовых фигур мужчин</div></td>
</tr>
<tr class="razmer1">
<td rowspan="2">Размер</td>
<td>44</td>
<td>46</td>
<td>48</td>
<td>50</td>
<td>52</td>
<td>54</td>
<td>56</td>
<td>58</td>
</tr>
<tr class="razmer2">
<td>S</td>
<td>M</td>
<td>L</td>
<td>XL</td>
<td>XXL</td>
<td>3XL</td>
<td>4XL</td>
<td>5XL</td>
</tr>
<tr class="grud">
<td>Обхват груди</td>
<td>88</td>
<td>92</td>
<td>96</td>
<td>100</td>
<td>104</td>
<td>108</td>
<td>112</td>
<td>116</td>
</tr>
<tr class="tall">
<td>Обхват талии</td>
<td>76</td>
<td>80</td>
<td>84</td>
<td>88</td>
<td>92</td>
<td>96</td>
<td>100</td>
<td>104</td>
</tr>
</tbody>
</table></div> <a class="link_t" href="http://medshvetsmarka.com.ua/zakazat-onlajn/opredilenie-razmera/">Как правильно определить размер?</a> <br>';
}
add_shortcode('size_men_ru', 'ru_sizemen_function');
function ru_size_wmen_function() {
     return '<div class="table_size"><table class="size">
<tbody>
<tr>
<td colspan="9">
<div align="center">Класcификация типовых фигур женщин</div></td>
</tr>
<tr class="razmer1">
<td rowspan="2">Размер</td>
<td>40</td>
<td>42</td>
<td>44</td>
<td>46</td>
<td>48</td>
<td>50</td>
<td>52</td>
<td>54</td>
</tr>
<tr class="razmer2">
<td>2XS</td>
<td>XS</td>
<td>S</td>
<td>M</td>
<td>L</td>
<td>XL</td>
<td>2XL</td>
<td>3XL</td>
</tr>
<tr class="grud">
<td>Обхват груди</td>
<td>80</td>
<td>84</td>
<td>88</td>
<td>92</td>
<td>96</td>
<td>100</td>
<td>104</td>
<td>108</td>
</tr>
<tr class="tall">
<td>Обхват бедер</td>
<td>88</td>
<td>92</td>
<td>96</td>
<td>100</td>
<td>104</td>
<td>108</td>
<td>112</td>
<td>116</td>
</tr>
</tbody>
</table>
</div><a class="link_t" href="http://medshvetsmarka.com.ua/zakazat-onlajn/opredilenie-razmera/"> Как правильно определить размер?</a><br>';}
add_shortcode('size_wmen_ru', 'ru_size_wmen_function');

function ua_size_wmen_function() {
     return '<div class="table_size"><table class="size">
<tbody>
<tr>
<td colspan="9">
<div align="center">Класифікація типових фігур жінок</div></td>
</tr>
<tr class="razmer1">
<td rowspan="2">Розмір</td>
<td>40</td>
<td>42</td>
<td>44</td>
<td>46</td>
<td>48</td>
<td>50</td>
<td>52</td>
<td>54</td>
</tr>
<tr class="razmer2">
<td>2XS</td>
<td>XS</td>
<td>S</td>
<td>M</td>
<td>L</td>
<td>XL</td>
<td>2XL</td>
<td>3XL</td>
</tr>
<tr class="grud">
<td>Обхват грудей</td>
<td>80</td>
<td>84</td>
<td>88</td>
<td>92</td>
<td>96</td>
<td>100</td>
<td>104</td>
<td>108</td>
</tr>
<tr class="tall">
<td>Обхват бедер</td>
<td>88</td>
<td>92</td>
<td>96</td>
<td>100</td>
<td>104</td>
<td>108</td>
<td>112</td>
<td>116</td>
</tr>
</tbody>
</table>
</div>
<a class="link_t" href="http://medshvetsmarka.com.ua/zakazat-onlajn/opredilenie-razmera/"> Як правильно визначити розмір?</a><br>';}
add_shortcode('size_wmen_ua', 'ua_size_wmen_function');
/*Произвольные поля*/
add_action('add_meta_boxes', 'AddExtraFields', 1);
function AddExtraFields() {
	add_meta_box( 'before_price', 'Дополнительные поля на странице товара', 'extra_fields_box_func', 'product', 'normal', 'high'  );
}
function extra_fields_box_func( $post ){
	?>
	<p><label>Что отображать перед ценой товара: <input type="text" name="extra[before_price]" value="<?php echo get_post_meta($post->ID, 'before_price', 1); ?>" style="width:50%" /> </label></p>
	<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
	<?php
}

// включаем обновление полей при сохранении
add_action('save_post', 'my_extra_fields_update', 0);

/* Сохраняем данные, при сохранении поста */
function my_extra_fields_update( $post_id ){
	if ( !wp_verify_nonce($_POST['extra_fields_nonce'], __FILE__) ) return false; // проверка
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return false; // выходим если это автосохранение
	if ( !current_user_can('edit_post', $post_id) ) return false; // выходим если юзер не имеет право редактировать запись

	if( !isset($_POST['extra']) ) return false; // выходим если данных нет

	// Все ОК! Теперь, нужно сохранить/удалить данные
	$_POST['extra'] = array_map('trim', $_POST['extra']); // чистим все данные от пробелов по краям
	foreach( $_POST['extra'] as $key=>$value ){
		if( empty($value) ){
			delete_post_meta($post_id, $key); // удаляем поле если значение пустое
			continue;
		}

		update_post_meta($post_id, $key, $value); // add_post_meta() работает автоматически
	}
	return $post_id;
}


?>
