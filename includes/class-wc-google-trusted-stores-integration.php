<?php

/**
 * Google Analytics Integration
 *
 * Allows tracking code to be inserted into store pages.
 *
 * @class       WC_Google_Trusted_Stores
 * @extends     WC_Integration
 */
class WC_Google_Trusted_Stores extends WC_Integration {


	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {
		$this->id                 = 'google_trusted_stores';
		$this->method_title       = __( 'Google Trusted Stores', 'wc_google_trusted_stores' );
		$this->method_description = __( 'Google Trusted Stores is a free service offered by Google that adds a badge to your online store allowing you to reach new customers and improve sales.', 'wc_google_trusted_stores' );

		$this->languages = $this->get_languages();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables
		$this->gts_id                               = $this->get_option( 'gts_id' );
		$this->gts_locale                           = $this->get_option( 'gts_locale' );
		$this->gts_ship_time                        = $this->get_option( 'gts_ship_time' );
		$this->gts_delivery_time                    = $this->get_option( 'gts_delivery_time' );
		$this->gts_non_us                           = $this->get_option( 'gts_non_us' );
		$this->gts_google_shopping_account_enable   = $this->get_option( 'gts_google_shopping_account_enable' );
		$this->gts_google_shopping_account_id       = $this->get_option( 'gts_google_shopping_account_id' );
		$this->gts_google_shopping_account_country  = $this->get_option( 'gts_google_shopping_account_country' );
		$this->gts_google_shopping_account_language = $this->get_option( 'gts_google_shopping_account_language' );


		// Actions
		add_action( 'woocommerce_update_options_integration_google_trusted_stores', array( $this, 'process_admin_options' ) );

		// Google Trusted Stores Badge Code
		add_action( 'wp_footer', array( $this, 'badge_code' ) );

		// Order Confirmation Module Code
		add_action( 'woocommerce_thankyou', array( $this, 'confirmation_code' ) );
	}


	/**
	 * Initialise Settings Form Fields
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'title_general' => array (
				'title'       => __( 'General Options', 'wc_google_trusted_stores' ),
				'type'        => 'title',
				'description' => __( 'The following options are required to show the Google Trusted Stores Badge', 'wc_google_trusted_stores' ),
				'id'          => 'wc_gts_general_options',
			),

			'gts_id' => array(
				'title'       => __( 'Google Trusted Stores ID', 'wc_google_trusted_stores' ),
				'description' => __( 'Log into your Google Trusted Stores account to find your ID. e.g. <code>000000</code>', 'wc_google_trusted_stores' ),
				'type'        => 'text',
				'default'     => '',
			),

			'gts_locale' => array(
				'title'       => __( 'Locale', 'wc_google_trusted_stores' ),
				'description' => sprintf( __( 'Set the main locale of your site. The locale should be in the format of <code>%s</code>', 'wc_google_trusted_stores' ), esc_html( '<language>_<country>' ) ),
				'type'        => 'text',
				'default'     => defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US',
			),

			'gts_ship_time' => array(
				'title'       => __( 'Estimate Ship Time (weekdays)', 'wc_google_trusted_stores' ),
				'description' => __( 'Set the estimated ship time in weekdays', 'wc_google_trusted_stores' ),
				'type'        => 'text',
				'default'     => '1',
			),

			'gts_delivery_time' => array(
				'title'       => __( 'Estimate Delivery Time (weekdays)', 'wc_google_trusted_stores' ),
				'description' => __( 'Set the estimated delivery time in weekdays from the ship date', 'wc_google_trusted_stores' ),
				'type'        => 'text',
				'default'     => '7',
			),

			'gts_non_us' => array(
				'title'   => __( 'Non-US', 'wc_google_trusted_stores' ),
				'label'   => __( 'Use the non-US Google Trusted Stores JavaScript', 'wc_google_trusted_stores' ),
				'type'    => 'checkbox',
				'default' => 'yes',
			),

			'title_google_shopping' => array (
				'title'       => __( 'Google Shopping Options', 'wc_google_trusted_stores' ),
				'type'        => 'title',
				'description' => __( 'The following options are recommended if you submit product feeds for Google Shopping.<br>Provide these fields only if you submit feeds for Google Shopping.', 'wc_google_trusted_stores' ),
			),

			'gts_google_shopping_account_enable' => array(
				'title'           => __( 'Google Shopping', 'wc_google_trusted_stores' ),
				'label'           => __( 'Enable Google Shopping', 'wc_google_trusted_stores' ),
				'type'            => 'checkbox',
				'default'         => 'no',
				'show_if_checked' => 'option',
			),

			'gts_google_shopping_account_id' => array(
				'title'           => __( 'Google Shopping Account ID', 'wc_google_trusted_stores' ),
				'description'     => __( 'Account ID from Google Shopping. This value should match the account ID you use to submit your product data feed you submit to Google Shopping.<br>Provide this field only if you submit feeds for Google Shopping.', 'wc_google_trusted_stores' ),
				'type'            => 'text',
				'default'         => '',
				'show_if_checked' => 'yes',
			),

			'gts_google_shopping_account_country' => array(
				'title'           => __( 'Google Shopping Account Country', 'wc_google_trusted_stores' ),
				'description'     => __( 'Account country from Google Shopping. This value should match the account country you use to submit your product data feed to Google Shopping.', 'wc_google_trusted_stores' ),
				'type'            => 'select',
				'class'           => 'chosen_select',
				'default'         => WC()->countries->get_base_country(),
				'options'         => WC()->countries->countries,
				'show_if_checked' => 'yes',
			),

			'gts_google_shopping_account_language' => array(
				'title'           => __( 'Language', 'wc_google_trusted_stores' ),
				'description'     => __( 'Account language from Google Shopping. This value should match the account language you use to submit your product data feed to Google Shopping.', 'wc_google_trusted_stores' ),
				'type'            => 'select',
				'class'           => 'chosen_select',
				'default'         => defined( 'WPLANG' ) && WPLANG ? substr( WPLANG, 0, 2 ) : 'en',
				'options'         => $this->languages,
				'show_if_checked' => 'yes',
			),

			'gts_html' => array(
				'type' => 'gts',
			)

		);

	} // End init_form_fields()


	public function generate_gts_html() {
		ob_start();
		?>
		<script type="text/javascript">
			jQuery( window ).load( function() {
				jQuery( '#woocommerce_google_trusted_stores_gts_google_shopping_account_enable' ).change( function(){
					if ( jQuery( this ).is( ':checked' ) ) {
						jQuery( '#woocommerce_google_trusted_stores_gts_google_shopping_account_id' ).closest( 'tr' ).show();
						jQuery( '#woocommerce_google_trusted_stores_gts_google_shopping_account_country' ).closest( 'tr' ).show();
						jQuery( '#woocommerce_google_trusted_stores_gts_google_shopping_account_language' ).closest( 'tr' ).show();
					} else {
						jQuery( '#woocommerce_google_trusted_stores_gts_google_shopping_account_id' ).closest( 'tr' ).hide();
						jQuery( '#woocommerce_google_trusted_stores_gts_google_shopping_account_country' ).closest( 'tr' ).hide();
						jQuery( '#woocommerce_google_trusted_stores_gts_google_shopping_account_language' ).closest( 'tr' ).hide();
					}
				}).change();
			});
		</script>
		<?php
		return ob_get_clean();
	}


	/**
	 * Google Trusted Stores badge code
	 */
	public function badge_code() {

		if ( ! $this->gts_id ) {
			return;
		}

		$code = 'var gts = gts || [];
					gts.push(["id", "' . esc_js( $this->gts_id ) . '"]);
					gts.push(["locale", "' . esc_js( $this->gts_locale ) . '"]);';

		if ( is_product() ) {

			global $product;
			$code .= 'gts.push(["google_base_offer_id", "' . esc_js( $product->id ) . '"]);';
		}

		if ( $this->gts_google_shopping_account_enable === 'yes' && $this->gts_google_shopping_account_id !== '' ) {

			$code .= '
				gts.push(["google_base_subaccount_id", "' . esc_js( $this->gts_google_shopping_account_id ) . '"]);
				gts.push(["google_base_country", "' . esc_js( $this->gts_google_shopping_account_country ) . '"]);
				gts.push(["google_base_language", "' . esc_js( $this->gts_google_shopping_account_language ) . '"]);
			';
		}

		$src = 'yes' === $this->gts_non_us ? 'www.googlecommerce.com/trustedstores/api/js' : 'www.googlecommerce.com/trustedstores/gtmp_compiled.js';

		$code .= '
				(function() {
					var scheme = (("https:" == document.location.protocol) ? "https://" : "http://");
					var gts = document.createElement("script");
					gts.type = "text/javascript";
					gts.async = true;
					gts.src = scheme + "' . $src . '";
					var s = document.getElementsByTagName("script")[0];
					s.parentNode.insertBefore(gts, s);
				})();
			';

		echo '<!-- BEGIN: Google Trusted Stores --><script type="text/javascript">' . $code . '</script><!-- END: Google Trusted Stores -->';
	}


	/**
	 * Google Trusted Stores confirmation code
	 *
	 * @param mixed $order_id
	 */
	public function confirmation_code( $order_id ) {

		if ( 1 === get_post_meta( $order_id, '_wc_gts_tracked', true ) ) {
			return;
		}

		if ( ! $this->gts_id  ) {
			return;
		}

		// Get the order
		$order = wc_get_order( $order_id );

		// Get order items
		$items = $order->get_items();

		$ship_date = date( 'Y-m-d', strtotime( $this->gts_ship_time . ' weekdays', strtotime( $order->order_date ) ) );
		$ship_date = apply_filters( 'wc_google_trusted_stores_order_ship_date', $ship_date, $order_id );

		$delivery_date = date( 'Y-m-d', strtotime( $this->gts_delivery_time . ' weekdays', strtotime( $ship_date ) ) );
		$delivery_date = apply_filters( 'wc_google_trusted_stores_order_delivery_date', $delivery_date, $order_id );

		$has_backorder = false;

		foreach ( $items as $item ) {

			$product = $order->get_product_from_item( $item );

			if ( $product && $product->exists() && $product->is_on_backorder() ) {
				$has_backorder = true;
			}
		}

		$has_backorder = apply_filters( 'wc_google_trusted_stores_order_has_backorder', $has_backorder, $order_id );

		$code = '
			<!-- start order and merchant information -->
			<span id="gts-o-id">' . esc_html( $order->get_order_number() ) . '</span>
			<span id="gts-o-domain">' . str_replace( array( 'http://', 'https://' ), '', home_url() ) . '</span>
			<span id="gts-o-email">' . esc_html( $order->billing_email ) . '</span>
			<span id="gts-o-country">' . esc_html( $order->shipping_country ) . '</span>
			<span id="gts-o-currency">' . esc_html( $order->get_order_currency() ) . '</span>
			<span id="gts-o-total">' . esc_html( $order->get_total() ) . '</span>
			<span id="gts-o-discounts">' . esc_html( $order->get_total_discount() ) . '</span>
			<span id="gts-o-shipping-total">' . esc_html( $order->get_total_shipping() ) . '</span>
			<span id="gts-o-tax-total">' . esc_html( $order->get_total_tax() ) . '</span>
			<span id="gts-o-est-ship-date">' . esc_html( $ship_date ) . '</span>
			<span id="gts-o-est-delivery-date">' . esc_html( $delivery_date ) . '</span>
			<span id="gts-o-has-preorder">' . ( $has_backorder ? 'Y' : 'N' ) . '</span>
			<span id="gts-o-has-digital">' . ( $order->has_downloadable_item() ? 'Y' : 'N' ) . '</span>
			<!-- end order and merchant information -->
		';

		$google_shopping_acct_code = '';

		if ( $this->gts_google_shopping_account_enable === 'yes' && $this->gts_google_shopping_account_id !== '' ) {
			$google_shopping_acct_code = '
				<span class="gts-i-prodsearch-store-id">' . esc_html( $this->gts_google_shopping_account_id ) . '</span>
				<span class="gts-i-prodsearch-country">' . esc_html( $this->gts_google_shopping_account_country ) . '</span>
				<span class="gts-i-prodsearch-language">' . esc_html( $this->gts_google_shopping_account_language ) . '</span>
			';
		}

		foreach ( $items as $item ) {

			$code .= '
				<span class="gts-item">
					<span class="gts-i-name">' . esc_html( $item['name'] ) . '</span>
					<span class="gts-i-price">' . esc_html( $order->get_item_subtotal( $item ) ) . '</span>
					<span class="gts-i-quantity">' . esc_html( $item['qty'] ) . '</span>
					<span class="gts-i-prodsearch-id">' . esc_html( $item['product_id'] ) . '</span>' .
					$google_shopping_acct_code .
				'</span>
			';
		}

		echo '<!-- START Google Trusted Stores Order -->
			<div id="gts-order" style="display:none;" translate="no">';
		echo $code;
		echo '</div>
			<!-- END Google Trusted Stores Order -->';

		update_post_meta( $order_id, '_wc_gts_tracked', 1 );
	}


	/**
	 * Get languages array
	 *
	 * @return array associative array of languages
	 */
	public function get_languages() {

		return apply_filters( 'wc_google_trusted_stores_languages', array(
			'en' => __( 'English', 'wc_google_trusted_stores' ),
			'aa' => __( 'Afar', 'wc_google_trusted_stores' ),
			'ab' => __( 'Abkhazian', 'wc_google_trusted_stores' ),
			'af' => __( 'Afrikaans', 'wc_google_trusted_stores' ),
			'am' => __( 'Amharic', 'wc_google_trusted_stores' ),
			'ar' => __( 'Arabic', 'wc_google_trusted_stores' ),
			'as' => __( 'Assamese', 'wc_google_trusted_stores' ),
			'ay' => __( 'Aymara', 'wc_google_trusted_stores' ),
			'az' => __( 'Azerbaijani', 'wc_google_trusted_stores' ),
			'ba' => __( 'Bashkir', 'wc_google_trusted_stores' ),
			'be' => __( 'Byelorussian', 'wc_google_trusted_stores' ),
			'bg' => __( 'Bulgarian', 'wc_google_trusted_stores' ),
			'bh' => __( 'Bihari', 'wc_google_trusted_stores' ),
			'bi' => __( 'Bislama', 'wc_google_trusted_stores' ),
			'bn' => __( 'Bengali/Bangla', 'wc_google_trusted_stores' ),
			'bo' => __( 'Tibetan', 'wc_google_trusted_stores' ),
			'br' => __( 'Breton', 'wc_google_trusted_stores' ),
			'ca' => __( 'Catalan', 'wc_google_trusted_stores' ),
			'co' => __( 'Corsican', 'wc_google_trusted_stores' ),
			'cs' => __( 'Czech', 'wc_google_trusted_stores' ),
			'cy' => __( 'Welsh', 'wc_google_trusted_stores' ),
			'da' => __( 'Danish', 'wc_google_trusted_stores' ),
			'de' => __( 'German', 'wc_google_trusted_stores' ),
			'dz' => __( 'Bhutani', 'wc_google_trusted_stores' ),
			'el' => __( 'Greek', 'wc_google_trusted_stores' ),
			'eo' => __( 'Esperanto', 'wc_google_trusted_stores' ),
			'es' => __( 'Spanish', 'wc_google_trusted_stores' ),
			'et' => __( 'Estonian', 'wc_google_trusted_stores' ),
			'eu' => __( 'Basque', 'wc_google_trusted_stores' ),
			'fa' => __( 'Persian', 'wc_google_trusted_stores' ),
			'fi' => __( 'Finnish', 'wc_google_trusted_stores' ),
			'fj' => __( 'Fiji', 'wc_google_trusted_stores' ),
			'fo' => __( 'Faeroese', 'wc_google_trusted_stores' ),
			'fr' => __( 'French', 'wc_google_trusted_stores' ),
			'fy' => __( 'Frisian', 'wc_google_trusted_stores' ),
			'ga' => __( 'Irish', 'wc_google_trusted_stores' ),
			'gd' => __( 'Scots/Gaelic', 'wc_google_trusted_stores' ),
			'gl' => __( 'Galician', 'wc_google_trusted_stores' ),
			'gn' => __( 'Guarani', 'wc_google_trusted_stores' ),
			'gu' => __( 'Gujarati', 'wc_google_trusted_stores' ),
			'ha' => __( 'Hausa', 'wc_google_trusted_stores' ),
			'hi' => __( 'Hindi', 'wc_google_trusted_stores' ),
			'hr' => __( 'Croatian', 'wc_google_trusted_stores' ),
			'hu' => __( 'Hungarian', 'wc_google_trusted_stores' ),
			'hy' => __( 'Armenian', 'wc_google_trusted_stores' ),
			'ia' => __( 'Interlingua', 'wc_google_trusted_stores' ),
			'ie' => __( 'Interlingue', 'wc_google_trusted_stores' ),
			'ik' => __( 'Inupiak', 'wc_google_trusted_stores' ),
			'in' => __( 'Indonesian', 'wc_google_trusted_stores' ),
			'is' => __( 'Icelandic', 'wc_google_trusted_stores' ),
			'it' => __( 'Italian', 'wc_google_trusted_stores' ),
			'iw' => __( 'Hebrew', 'wc_google_trusted_stores' ),
			'ja' => __( 'Japanese', 'wc_google_trusted_stores' ),
			'ji' => __( 'Yiddish', 'wc_google_trusted_stores' ),
			'jw' => __( 'Javanese', 'wc_google_trusted_stores' ),
			'ka' => __( 'Georgian', 'wc_google_trusted_stores' ),
			'kk' => __( 'Kazakh', 'wc_google_trusted_stores' ),
			'kl' => __( 'Greenlandic', 'wc_google_trusted_stores' ),
			'km' => __( 'Cambodian', 'wc_google_trusted_stores' ),
			'kn' => __( 'Kannada', 'wc_google_trusted_stores' ),
			'ko' => __( 'Korean', 'wc_google_trusted_stores' ),
			'ks' => __( 'Kashmiri', 'wc_google_trusted_stores' ),
			'ku' => __( 'Kurdish', 'wc_google_trusted_stores' ),
			'ky' => __( 'Kirghiz', 'wc_google_trusted_stores' ),
			'la' => __( 'Latin', 'wc_google_trusted_stores' ),
			'ln' => __( 'Lingala', 'wc_google_trusted_stores' ),
			'lo' => __( 'Laothian', 'wc_google_trusted_stores' ),
			'lt' => __( 'Lithuanian', 'wc_google_trusted_stores' ),
			'lv' => __( 'Latvian/Lettish', 'wc_google_trusted_stores' ),
			'mg' => __( 'Malagasy', 'wc_google_trusted_stores' ),
			'mi' => __( 'Maori', 'wc_google_trusted_stores' ),
			'mk' => __( 'Macedonian', 'wc_google_trusted_stores' ),
			'ml' => __( 'Malayalam', 'wc_google_trusted_stores' ),
			'mn' => __( 'Mongolian', 'wc_google_trusted_stores' ),
			'mo' => __( 'Moldavian', 'wc_google_trusted_stores' ),
			'mr' => __( 'Marathi', 'wc_google_trusted_stores' ),
			'ms' => __( 'Malay', 'wc_google_trusted_stores' ),
			'mt' => __( 'Maltese', 'wc_google_trusted_stores' ),
			'my' => __( 'Burmese', 'wc_google_trusted_stores' ),
			'na' => __( 'Nauru', 'wc_google_trusted_stores' ),
			'ne' => __( 'Nepali', 'wc_google_trusted_stores' ),
			'nl' => __( 'Dutch', 'wc_google_trusted_stores' ),
			'no' => __( 'Norwegian', 'wc_google_trusted_stores' ),
			'oc' => __( 'Occitan', 'wc_google_trusted_stores' ),
			'om' => __( '(Afan)/Oromoor/Oriya', 'wc_google_trusted_stores' ),
			'pa' => __( 'Punjabi', 'wc_google_trusted_stores' ),
			'pl' => __( 'Polish', 'wc_google_trusted_stores' ),
			'ps' => __( 'Pashto/Pushto', 'wc_google_trusted_stores' ),
			'pt' => __( 'Portuguese', 'wc_google_trusted_stores' ),
			'qu' => __( 'Quechua', 'wc_google_trusted_stores' ),
			'rm' => __( 'Rhaeto-Romance', 'wc_google_trusted_stores' ),
			'rn' => __( 'Kirundi', 'wc_google_trusted_stores' ),
			'ro' => __( 'Romanian', 'wc_google_trusted_stores' ),
			'ru' => __( 'Russian', 'wc_google_trusted_stores' ),
			'rw' => __( 'Kinyarwanda', 'wc_google_trusted_stores' ),
			'sa' => __( 'Sanskrit', 'wc_google_trusted_stores' ),
			'sd' => __( 'Sindhi', 'wc_google_trusted_stores' ),
			'sg' => __( 'Sangro', 'wc_google_trusted_stores' ),
			'sh' => __( 'Serbo-Croatian', 'wc_google_trusted_stores' ),
			'si' => __( 'Singhalese', 'wc_google_trusted_stores' ),
			'sk' => __( 'Slovak', 'wc_google_trusted_stores' ),
			'sl' => __( 'Slovenian', 'wc_google_trusted_stores' ),
			'sm' => __( 'Samoan', 'wc_google_trusted_stores' ),
			'sn' => __( 'Shona', 'wc_google_trusted_stores' ),
			'so' => __( 'Somali', 'wc_google_trusted_stores' ),
			'sq' => __( 'Albanian', 'wc_google_trusted_stores' ),
			'sr' => __( 'Serbian', 'wc_google_trusted_stores' ),
			'ss' => __( 'Siswati', 'wc_google_trusted_stores' ),
			'st' => __( 'Sesotho', 'wc_google_trusted_stores' ),
			'su' => __( 'Sundanese', 'wc_google_trusted_stores' ),
			'sv' => __( 'Swedish', 'wc_google_trusted_stores' ),
			'sw' => __( 'Swahili', 'wc_google_trusted_stores' ),
			'ta' => __( 'Tamil', 'wc_google_trusted_stores' ),
			'te' => __( 'Tegulu', 'wc_google_trusted_stores' ),
			'tg' => __( 'Tajik', 'wc_google_trusted_stores' ),
			'th' => __( 'Thai', 'wc_google_trusted_stores' ),
			'ti' => __( 'Tigrinya', 'wc_google_trusted_stores' ),
			'tk' => __( 'Turkmen', 'wc_google_trusted_stores' ),
			'tl' => __( 'Tagalog', 'wc_google_trusted_stores' ),
			'tn' => __( 'Setswana', 'wc_google_trusted_stores' ),
			'to' => __( 'Tonga', 'wc_google_trusted_stores' ),
			'tr' => __( 'Turkish', 'wc_google_trusted_stores' ),
			'ts' => __( 'Tsonga', 'wc_google_trusted_stores' ),
			'tt' => __( 'Tatar', 'wc_google_trusted_stores' ),
			'tw' => __( 'Twi', 'wc_google_trusted_stores' ),
			'uk' => __( 'Ukrainian', 'wc_google_trusted_stores' ),
			'ur' => __( 'Urdu', 'wc_google_trusted_stores' ),
			'uz' => __( 'Uzbek', 'wc_google_trusted_stores' ),
			'vi' => __( 'Vietnamese', 'wc_google_trusted_stores' ),
			'vo' => __( 'Volapuk', 'wc_google_trusted_stores' ),
			'wo' => __( 'Wolof', 'wc_google_trusted_stores' ),
			'xh' => __( 'Xhosa', 'wc_google_trusted_stores' ),
			'yo' => __( 'Yoruba', 'wc_google_trusted_stores' ),
			'zh' => __( 'Chinese', 'wc_google_trusted_stores' ),
			'zu' => __( 'Zulu', 'wc_google_trusted_stores' ),
		) );
	}


}
