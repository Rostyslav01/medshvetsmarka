<?php
/**
 * Checkout Payment Section
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php if ( ! is_ajax() ) : ?>
	<?php do_action( 'woocommerce_review_order_before_payment' ); ?>
<?php endif; ?>

<div id="payment" class="woocommerce-checkout-payment">
	<?php if ( WC()->cart->needs_payment() ) : ?>
	<ul class="payment_methods methods">
		<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
				}
			} else {
				if ( ! WC()->customer->get_country() ) {
					$no_gateways_message = __( 'Please fill in your details above to see available payment methods.', 'woocommerce' );
				} else {
					$no_gateways_message = __( 'Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' );
				}

				echo '<li>' . apply_filters( 'woocommerce_no_available_payment_methods_message', $no_gateways_message ) . '</li>';
			}
		?>
	</ul>
	<?php endif; ?>

	<div class="form-row place-order">

		<noscript><?php _e( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ); ?><br/><input type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>" /></noscript>

		<?php wp_nonce_field( 'woocommerce-process_checkout' ); ?>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
		<?php echo apply_filters( 'woocommerce_order_button_html', '<input type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '" />' ); ?>
		<div class="clear"></div>
		
		<div id="license_ag">
		
			<?php
			require_once './wp-content/plugins/qtranslate-x/qtranslate_compatibility.php';
			$currentLang2 = qtrans_getLanguage();?> 
				<?php if ($currentLang2 == 'ru'):?>
								<div id="sog"><p style="cursor: pointer;" onclick="document.getElementById('sog_all').style.display='block'; document.getElementById('sog_font').style.display='block';">Подтверждая заказ, я принимаю условия пользовательского соглашения.</p></div>
								<div id="sog_font" style="cursor: pointer;" onclick="document.getElementById('sog_all').style.display='none'; document.getElementById('sog_font').style.display='none';"></div>
								<div id="sog_all">
								<p id="close_sog" style="cursor: pointer;" onclick="document.getElementById('sog_all').style.display='none'; document.getElementById('sog_font').style.display='none';">[X]</p>
								<h2>Соглашение об обработке персональных данных</h2>
													<p>Данное соглашение об обработке персональных данных разработано в соответствии с законодательством Украины. Все лица заполнившие сведения, составляющие персональные данные на данном сайте, а также разместившие иную информацию обозначенными действиями подтверждают свое согласие на обработку персональных данных и их передачу оператору.</p>

													<p>Под персональными данными Гражданина понимается информация: имя, фамилия, телефон, Email-адрес.</p>

													<P>Гражданин, принимая настоящее Соглашение, <strong><i>выражает свое полное согласие на обработку его персональных данных, получение рассылки материалов рекламного и/или информативного характера от ТМ "Швецька Марка".</i></strong></p>

													<p>Гражданин гарантирует: информация, им предоставленная, является полной, точной и достоверной; при предоставлении информации не нарушается действующее законодательство Украины, законные права и интересы третьих лиц; вся предоставленная информация заполнена Гражданином в отношении себя лично.</p>
								</div>
				<?php endif; ?>

				<?php if ($currentLang2 == 'ua'):?>
								<div id="sog"><p style="cursor: pointer;" onclick="document.getElementById('sog_all').style.display='block'; document.getElementById('sog_font').style.display='block';">Підтверджуючи замовлення, я приймаю умови користувальницької угоди.</p></div>
								<div id="sog_font" style="cursor: pointer;" onclick="document.getElementById('sog_all').style.display='none'; document.getElementById('sog_font').style.display='none';"></div>
								<div id="sog_all">
								<p id="close_sog" style="cursor: pointer;" onclick="document.getElementById('sog_all').style.display='none'; document.getElementById('sog_font').style.display='none';">[X]</p>
								<h2>Угода про обробку персональних даних </h2>
													<p>Дана угода про обробку персональних даних розроблено відповідно до законодавства України. Всі особи заповнили відомості, що становлять персональні дані на даному сайті, а також розмістивши іншу інформацію визначеними діями підтверджують свою згоду на обробку персональних даних та їх передачу оператору.</p>

													<p>Під персональними даними Громадянина розуміється інформація: ім'я, прізвище, телефон, Email-адрес.</p>

													<P>Громадянин, приймаючи цю Угоду, <strong><i>висловлює свою повну згоду на обробку його персональних даних, отримання розсилки матеріалів рекламного та / або інформативного характеру від ТМ "швецька Марка".</i></strong></p>

													<p>Громадянин гарантує: інформація, їм представлена, є повною, точною і достовірною; при наданні інформації не порушується чинне законодавство України, законні права та інтереси третіх осіб; вся надана інформація заповнена Громадянином щодо себе особисто.</p>
								</div>
				<?php endif; ?>		
		</div>

		<?php if ( wc_get_page_id( 'terms' ) > 0 && apply_filters( 'woocommerce_checkout_show_terms', true ) ) : ?>
			<p class="form-row terms">
				<label for="terms" class="checkbox"><?php printf( __( 'I&rsquo;ve read and accept the <a href="%s" target="_blank">terms &amp; conditions</a>', 'woocommerce' ), esc_url( wc_get_page_permalink( 'terms' ) ) ); ?></label>
				<input type="checkbox" class="input-checkbox" name="terms" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); ?> id="terms" />
			</p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

	</div>

	<div class="clear"></div>
</div>

<?php if ( ! is_ajax() ) : ?>
	<?php do_action( 'woocommerce_review_order_after_payment' ); ?>
<?php endif; ?>
