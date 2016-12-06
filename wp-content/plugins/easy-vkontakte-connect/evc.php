<?php
/*
Plugin Name: Easy VKontakte Connect
Plugin URI: http://ukraya.ru/tag/easy-vkontakte-connect/
Description: Автопубликация записей с фото на стене ВКонтакте, социальный замок, опросы VK, анализ групп, кнопки, виджеты... 
Version: 2.2.1
Author: Aleksej Solovjov
Author URI: http://ukraya.ru
License: GPL2
*/

/*  Copyright 2012    (email :  )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function evc_version() {
	return '2.2.1';
}

add_action( 'admin_init', 'evc_activation_date' );
function evc_activation_date() {
	$date = get_option( 'evc_activation_date' );

	if ( ! $date ) {
		$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		add_option( 'evc_activation_date', $date );
	}

	return $date;
}

// prevent parsing errors on PHP 4 or old WP installs
if ( ! version_compare( PHP_VERSION, '5', '<' ) && version_compare( $wp_version, '3.2.999', '>' ) ) {
	include 'evc-base.php';
} else {
	add_action( 'admin_notices', create_function( '', "echo '<div class=\"error\"><p>" . __( 'Для работы плагина Easy Vkontakte Connect необходимы PHP 5 и WordPress 3.3. Пожалуйста обновите Wordpress или отключите плагин EVC.', 'evc' ) . "</p></div>';" ) );
}