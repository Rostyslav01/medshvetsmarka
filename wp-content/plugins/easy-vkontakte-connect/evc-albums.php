<?php

function evc_vk_albums_vk_photos_get_all( $params ) {
	$options = get_option( 'evc_vk_api_autopost' );

	// https://vk.com/dev/photos.getAll
	$defaults = array(
		//'owner_id'          =>,
		'extended'          => 1,
		'photo_sizes'       => 0,
		'no_service_albums' => 1,
		'need_hidden'       => 0,
		'skip_hidden'       => 1,
		'offset'            => 0,
		//'count'             => 20, // 200
		'access_token'      => $options['access_token'],
		'v'                 => '5.41'
	);

	$args = wp_parse_args( $params, $defaults );

	$res = evc_vkapi( array(
		'args'       => $args,
		'method'     => 'photos.getAll',
		'method_str' => 'evc_vk_albums_photos_get_all'
	) );

	//evc_add_log( 'evc_vk_albums_photos_get_all: ' .print_r($res,1) );//

	return $res;
}


function evc_vk_albums_vk_photos_get( $params ) {
	$options = get_option( 'evc_vk_api_autopost' );

	// https://vk.com/dev/photos.get
	$defaults = array(
		//'owner_id'          =>'',
		//'album_id'     => '',// wall , profile, saved
		//'photo_ids'    => 0,
		'rev'          => 1, // сортировка (1 — антихронологическая, 0 — хронологическая)
		'extended'     => 1,
		//'feed_type'    => '',
		//'feed'         => '',
		'photo_sizes'  => 0,
		'offset'       => 0,
		//'count'        => 20, // 1000
		'access_token' => $options['access_token'],
		'v'            => '5.41'
	);


	$args = wp_parse_args( $params, $defaults );

	$res = evc_vkapi( array(
		'args'       => $args,
		'method'     => 'photos.get',
		'method_str' => 'evc_vk_albums_photos_get'
	) );

	//evc_add_log( 'evc_vk_albums_photos_get: ' .print_r($res,1) );//

	return $res;
}


function evc_vk_albums_parse_url( $url ) {
	$re = "/(album([\\-]{0,1}\\d+)_(\\d+))|(albums([\\-]{0,1}\\d+))/";
	preg_match( $re, $url, $matches );

	$out = array();
	if ( ! empty( $matches[1] ) ) {
		$out['owner_id'] = $matches[2];
		$out['album_id'] = $matches[3];
	}
	if ( ! empty( $matches[4] ) ) {
		$out['owner_id'] = $matches[5];
	}

	return $out;
}


function evc_vk_albums_ug_shortcode_images( $images, $meta_id, $type, $atts ) {

	if ( empty( $atts['vk_url'] ) ) {
		return $images;
	}

	$images = evc_vk_albums_ug_shortcode_handler( $atts );

	return $images;
}

add_filter( 'ug_shortcode_images', 'evc_vk_albums_ug_shortcode_images', 10, 4 );


function evc_vk_albums_ug_shortcode_handler( $atts ) {

	foreach ( $atts as $key => $value ) {
		$key         = str_replace( 'vk_', '', $key );
		$out[ $key ] = $value;
	}

	$url = evc_vk_albums_parse_url( $out['url'] );
	if ( empty( $url['owner_id'] ) ) {
		return false;
	}

	$out['owner_id'] = $url['owner_id'];
	if ( ! empty( $url['album_id'] ) ) {
		$out['album_id'] = $url['album_id'];
	}

	if ( ! empty( $out['orderby'] ) && $out['orderby'] == 'date' && empty( $out['album_id'] ) ) {
		$out['rev'] = $out['order'] == 'asc' ? 0 : 1;
	}

	$out = apply_filters( 'evc_vk_albums_get_photos_params', $out, $atts );

	$array = array(
		'owner_id',
		'album_id',
		'photo_ids',
		'rev',
		'extended',
		'feed_type',
		'feed',
		'photo_sizes',
		'offset',
		'count',
		'no_service_albums',
		'need_hidden',
		'skip_hidden'
	);
	foreach ( $out as $k => $v ) {
		if ( in_array( $k, $array ) ) {
			$args[ $k ] = $v;
		}
	}

	$hash  = 'evc_vk_album_' . md5( serialize( $args ) );
	$cache = get_transient( $hash );
	if ( ! empty( $cache )  ) {
		$photos = $cache;
	} else {
		if ( empty( $args['album_id'] ) ) {
			$photos = evc_vk_albums_vk_photos_get_all( $args );
		} else {
			$photos = evc_vk_albums_vk_photos_get( $args );
		}
	}
	if ( empty( $cache ) ) {
		set_transient( $hash, $photos, 15 * MINUTE_IN_SECONDS );
	}

	$photos = apply_filters( 'evc_vk_albums_get_photos', $photos, $args, $out, $atts );

	return $photos;
}


function evc_vk_albums_the_content( $content ) {
	global $post;
	$is_pro = evc_is_pro();

	$evc_vk_album = get_post_meta( $post->ID, 'evc_vk_album', true );

	$pattern = get_shortcode_regex();
	preg_match_all( '/' . $pattern . '/s', $post->post_content, $matches );

	$is_vk_album_shortcode = false;
	if ( ! empty( $matches[0] ) ) {
		foreach ( $matches[0] as $str ) {
			if ( strpos( $str, ' vk_' ) !== false ) {
				$is_vk_album_shortcode = true;

				if ( ! $is_pro ) {
					$content = str_replace( $str, '', $content );
				}
			}
		}
	}

	$insert_in = false;
	if ( ! empty( $evc_vk_album['insert_in'] ) ) {
		foreach ( $evc_vk_album['insert_in'] as $key => $value ) {

			if ( call_user_func( 'is_' . $key ) ) {
				$insert_in = true;
				break;
			}
		}
	}

	if ( ( ! is_array( $matches ) || ! in_array( 'collage_gallery', $matches[2] ) )
	     && $evc_vk_album
	     && $insert_in
	     && ! $is_vk_album_shortcode
	) {
		// Build shortcode from post_meta
		$evc_vk_album_shortcode_str = evc_vk_albums_shorcode( $evc_vk_album );

		if ( ! empty( $evc_vk_album_shortcode_str ) ) {
			// TO DO
			// If excerpt_length less than text count in post part of vk albums may include in excerpt. To correct make excerpt_length smaller or text bigger. Filter 'excerpt_length'
			$evc_vk_album_shortcode = do_shortcode( $evc_vk_album_shortcode_str );
			$content .= $evc_vk_album_shortcode;
		}
	}

	return $content;
}

add_filter( 'the_content', 'evc_vk_albums_the_content' );
add_filter( 'the_excerpt', 'evc_vk_albums_the_content' );

function evc_vk_albums_shorcode( $data ) {
	$out = '';

	if ( ! empty( $data ) && ! empty( $data['url'] ) ) {

		if ( ! empty( $data['insert_in'] ) ) {
			unset( $data['insert_in'] );
		}

		foreach ( $data as $key => $value ) {
			if ( ! in_array( $key, array( 'shortcode', 'ug' ) ) ) {
				if ( is_array( $value ) ) {
					$value = implode( ',', $value );
				}

				$out[] = 'vk_' . $key . '="' . $value . '"';
			}
		}

		if ( ! empty( $data['ug'] ) ) {
			$out[] = 'ug=\'{' . $data['ug'] . '}\'';
		}

		$out = '[collage_gallery ' . implode( ' ', $out ) . ']';
	}

	return $out;
}


function evc_vk_albums_get_photos_res_handler( $photos, $args, $out, $atts ) {
	$out = array();

	if(!empty($photos['items'])) {
		foreach ( $photos['items'] as $item ) {
			$obj = new stdClass();

			$obj->ID        = $item['id'];
			$obj->permalink = 'https://vk.com/photo' . $item['owner_id'] . '_' . $item['id'];
			$obj->title     = '';

			$caption = '<div class="ug-social-data">';
			$caption .= '<div title="Мне нравится"><span class="dashicons dashicons-thumbs-up"></span> ' . $item['likes']['count'] . '</div>';

			if ( empty( $item['comments']['count'] ) ) {
				$item['comments']['count'] = 0;
			}
			$caption .= '<div title="Комментарии"><span class="dashicons dashicons-testimonial"></span> ' . $item['comments']['count'] . '</div>';

			$caption .= '<div class="ug-spacer"></div>';

			$caption .= '
			<div>
				<a href="' . $obj->permalink . '" style="text-decoration:none;" title="Открыть фото ВКонтакте" target ="_blank"><span class="dashicons dashicons-external"></span></a>
			</div>
		';
			$caption .= '</div>';
			$caption .= empty( $item['text'] ) ? '' : '<div>' . nl2br( $item['text'] ) . '</div>';

			$obj->post_content = $caption;

			$thumb_width = 604;
			$thumb_src   = evc_vk_albums_get_nearest_size( $item, $thumb_width );
			$height      = ceil( $thumb_width * $item['height'] / $item['width'] );
			$obj->size   = array( $thumb_src, $thumb_width, $height );
			$obj->src    = $obj->size;

			$big_width = 807;
			$big_src   = evc_vk_albums_get_nearest_size( $item, $big_width );
			$obj->guid = $big_src;

			$out[] = $obj;
		}
	}

	return $out;
}

add_filter( 'evc_vk_albums_get_photos', 'evc_vk_albums_get_photos_res_handler', 90, 4 );


function evc_vk_albums_get_nearest_size( $item, $near ) {
	$size = array();
	foreach ( $item as $key => $value ) {

		if ( strpos( $key, 'photo_' ) !== false ) {
			$k = str_replace( 'photo_', '', $key );

			$size[ $k ] = $value;

		}
	}

	ksort( $size );

	foreach ( $size as $key => $value ) {
		if ( $key <= $near ) {
			$nearest_key = $key;
		} else {
			break;
		}
	}

	return $size[ $nearest_key ];
}


add_action( 'evc_meta_box_action', 'evc_vk_albums_evc_meta_box_action' );
function evc_vk_albums_evc_meta_box_action( $custom ) {
	$is_pro = evc_is_pro();

	$disabled = $readonly = $blocked = '';
	if ( ! $is_pro ) {
		$disabled = 'disabled';
		$readonly = 'readonly="readonly"';
		$blocked  = '<br/><small>Доступно в <a href = "javascript:void(0);" class = "get-evc-pro">PRO версии</a>.</small>';
	}

	$defaults = array(
		'url'       => '',
		'count'     => 10,
		'orderby'   => 'date',
		'order'     => 'desc',
		'ug'        => '"margins":2, "lineHeight": 2, "maxRowHeight": 125',
		'insert_in' => array(
			'front_page' => false,
			'single'     => false,
			'tax'        => false
		)

	);

	if ( empty( $custom['evc_vk_album'][0] ) ) {
		$evc_vk_album = array(
			/*
			'show'      => array(
				'likes'    => 1,
				'comments' => 1,
				'url'      => 1,
				'text'     => 1
			),
			*/
			'insert_in' => array(
				'front_page' => 'on',
				'single'     => 'on',
				'tax'        => 'on'
			)
		);
	} else {
		$evc_vk_album = maybe_unserialize( $custom['evc_vk_album'][0] );
	}

	$evc_vk_album = wp_parse_args( $evc_vk_album, $defaults );

	?>
	<p><b>Добавить фото из альбома ВКонтакте</b></p>

	<?php
	$is_collage_gallery = evc_is_collage_gallery();

	if ( ! $is_collage_gallery ) {
		?>
		<p style=" color: #a00;">
			<b>ВНИМАНИЕ!!!</b> Для отображения фото из галерей ВКонтакте нужно установить плагин Collage Gallery.</p>
		<p>
			<a class="thickbox button button-primary" href="<?php echo site_url( 'wp-admin/plugin-install.php?tab=plugin-information&plugin=collage-gallery&TB_iframe=true&width=772&height=507' ); ?> ">Установить сейчас</a>
		</p>
		<?php
	}
	?>

	<p>
		<label for="evc_vk_album_url">Урл альбома ВКонтакте:</label>
		<input type="text" class="code" id="evc_vk_album_url" name="evc_vk_album[url]" value="<?php echo esc_attr( $evc_vk_album['url'] ); ?>" style="width:99%;"/>
		<br/>Урл альбома ВК из которого нужно показать фото. Например,
		<br/> <code>https://vk.com/albums-XXXXXXXX</code>, или
		<br><code>https://vk.com/album-XXXXXXXX_YYYYYYYY</code>,
		<br>где XXXXXXXX и YYYYYYYY - цифры.
	</p>

	<p>
		<label for="evc_vk_album_count">Показать фото:</label>
		<input type="text" class="code" id="evc_vk_album_count" name="evc_vk_album[count]" value="<?php echo $evc_vk_album['count']; ?>" style="width:99%;"/>
		<br/>Сколько фото из альбома показать.
	</p>

	<p>
		<label>Сортировать по:</label>

		<br/><input type="radio" value="likes" id="evc-vk-album-orderby-likes" name="evc_vk_album[orderby]" <?php echo checked( $evc_vk_album['orderby'], 'likes', false ) . ' ' . $disabled; ?> >
		<label class="selectit" for="evc-vk-album-orderby-likes">Мне нравится</label>
		<br/><input type="radio" value="comments" id="evc-vk-album-orderby-comments" name="evc_vk_album[orderby]" <?php echo checked( $evc_vk_album['orderby'], 'comments', false ) . ' ' . $disabled; ?> >
		<label class="selectit" for="evc-vk-album-orderby-comments">Комментариям</label>
		<br/><input type="radio" value="date" id="evc-vk-album-orderby-date" name="evc_vk_album[orderby]" <?php echo checked( $evc_vk_album['orderby'], 'date', false ) . ' ' . $disabled; ?> >
		<label class="selectit" for="evc-vk-album-orderby-date">Дате</label>
		<?php echo $blocked; ?>
	</p>

	<p>
		<label>Направление сортировки:</label>

		<br/><input type="radio" value="asc" id="evc-vk-album-order_asc" name="evc_vk_album[order]" <?php echo checked( $evc_vk_album['order'], 'asc', false ) . ' ' . $disabled; ?> >
		<label class="selectit" for="evc-vk-album-order_asc">По возрастанию</label>
		<br/><input type="radio" value="desc" id="evc-vk-album-order-desc" name="evc_vk_album[order]" <?php echo checked( $evc_vk_album['order'], 'desc', false ) . ' ' . $disabled; ?> >
		<label class="selectit" for="evc-vk-album-order-desc">По убыванию</label>
		<?php echo $blocked; ?>
	</p>

	<p>
		<label>Показывать на:</label>
		<br/><label><input type="checkbox" name="evc_vk_album[insert_in][front_page]" <?php echo checked( $evc_vk_album['insert_in']['front_page'], 'on', false ); ?> >главной странице,
			<small>is_front_page()</small>
		</label>
		<br/><label><input type="checkbox" name="evc_vk_album[insert_in][single]" <?php echo checked( $evc_vk_album['insert_in']['single'], 'on', false ); ?> >страницах записей,
			<small>is_single()</small>
		</label>
		<br/><label><input type="checkbox" name="evc_vk_album[insert_in][tax]" <?php echo checked( $evc_vk_album['insert_in']['tax'], 'on', false ); ?> >на страницах таксономии,
			<small>is_tax()</small>
		</label>
		<br/>Вы можете отметить типы страниц на которых будут показаны фото.
	</p>

	<?php
	/*
	<p>
		<label>Показать на фото:</label>
		<br/><label><input type="checkbox" name="evc_vk_album[show][likes]" <?php echo checked( $evc_vk_album['show']['likes'], true, false ) . ' ' . $disabled; ?> ><span class="dashicons dashicons-thumbs-up"></span> Количество "Мне нравится"</label>
		<br/><label><input type="checkbox" name="evc_vk_album[show][comments]" <?php echo checked( $evc_vk_album['show']['comments'], true, false ) . ' ' . $disabled; ?> ><span class="dashicons dashicons-testimonial"></span> Количество комментариев</label>
		<br/><label><input type="checkbox" name="evc_vk_album[show][url]" <?php echo checked( $evc_vk_album['show']['url'], true, false ) . ' ' . $disabled; ?> ><span class="dashicons dashicons-external"></span> Ссылку на фото ВКонтакте</label>
		<br/><label><input type="checkbox" name="evc_vk_album[show][text]" <?php echo checked( $evc_vk_album['show']['text'], true, false ) . ' ' . $disabled; ?> > Текст к фото</label>
		<?php echo $blocked; ?>
	</p>
	*/
	?>

	<p>
		<label for="evc_vk_album_count">Параметры коллажа:</label>
		<input type="text" class="code" id="evc_vk_album_ug" name="evc_vk_album[ug]" value="<?php echo esc_html( $evc_vk_album['ug'] ); ?>" style="width:99%;" <?php echo $readonly; ?> />
		<?php echo $blocked; ?>
		<br/>Можно изменить параметры коллажа. По умолчанию используются те, которые заданы в настройках плагина Collage Gallery.

	</p>

	<p>
		<label for="evc-vk-album-shortcode">Шорткод для вставки в статью:</label>
		<br><textarea cols="30" rows="2" id="evc-vk-album-shortcode" style="width:99%;" <?php echo $readonly; ?>></textarea>
		<?php echo $blocked; ?>
		<br/>Разместите этот шорткод в том месте в статье, где хотите отобразить фото.

	</p>
	<?php

}


function evc_vk_albums_evc_save_meta_box_action( $post_id ) {

	// Make sure that it is set.
	if ( ! isset( $_POST['evc_vk_album'] ) ) {
		return;
	}

	// Update the meta field in the database.
	if ( ! update_post_meta( $post_id, 'evc_vk_album', $_POST['evc_vk_album'] ) ) {
		add_post_meta( $post_id, 'evc_vk_album', $_POST['evc_vk_album'], true );
	}
}

add_action( 'evc_save_meta_box_action', 'evc_vk_albums_evc_save_meta_box_action' );


function evc_is_collage_gallery() {
	if ( ! function_exists( 'ug_shortcode' ) ) {
		return false;
	} else {
		return true;
	}
}