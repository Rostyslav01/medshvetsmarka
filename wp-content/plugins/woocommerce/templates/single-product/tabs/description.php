<?php
/**
 * Description tab
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post;

$heading = esc_html( apply_filters( 'woocommerce_product_description_heading', __( 'Product Description', 'woocommerce' ) ) );

?>

<?php if ( $heading ): ?>
  <h2><?php echo $heading; ?></h2>
<?php endif; ?>

<?php the_content(); ?>

<div class="a_dostavka">
<?php   $currentLang1 = qtrans_getLanguage();?> 
<?php if ($currentLang1 == 'ru'):?>
<a href="http://medshvetsmarka.com.ua/zakazat-onlajn/oplata-i-dostavka/">Доставка осуществляется по всей территории Украины</a>
<?php endif; ?>

<?php if ($currentLang1 == 'ua'):?>
<a href="http://medshvetsmarka.com.ua/zakazat-onlajn/oplata-i-dostavka/">Доставка здійснюється по всій території України</a>
<?php endif; ?>

</div>
