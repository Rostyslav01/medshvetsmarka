<?php
/**
 * Single Product Price, including microdata for SEO
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $post;
global $product;
$before = get_post_meta($post->ID, 'before_price', true);
?>
<div itemprop="offers" itemscope itemtype="http://schema.org/Offer">

	<p class="price"><?php echo $before ?> <?php echo $product->get_price_html(); ?></p>
	<?php   $currentLang1 = qtrans_getLanguage();?> 
		<?php if ($currentLang1 == 'ru'):?>
								<div id="zakaz"><a href="http://medshvetsmarka.com.ua/ua/shop/beydzhi/sozdat-beydzh/">Создать и заказать свой бейдж</a></div>
		<?php endif; ?>

		<?php if ($currentLang1 == 'ua'):?>
								<div id="zakaz"><a href="http://medshvetsmarka.com.ua/ua/shop/beydzhi/sozdat-beydzh/">Створити та замовити свій бейдж</a></div>
		<?php endif; ?>

	<meta itemprop="price" content="<?php echo $product->get_price(); ?>" />
	<meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
	<link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />

</div>