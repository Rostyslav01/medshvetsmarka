<?php
/**
 * Loop Price
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

<?php if ( $price_html = $product->get_price_html() ) : ?>
	<span class="price"><?php echo $before ?> <?php echo $price_html; ?></span>
<?php endif; ?>
