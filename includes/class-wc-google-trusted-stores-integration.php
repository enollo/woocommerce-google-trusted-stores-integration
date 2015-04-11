<?php

/**
 * Google Analytics Integration Class
 *
 * Allows tracking code to be inserted into store pages.
 *
 * @since 1.0.0
 * @class       WC_Google_Trusted_Stores
 * @extends     WC_Integration
 */
class WC_Google_Trusted_Stores extends WC_Integration {


	/**
	 * Init and hook in the integration.
	 */
	public function __construct() {

		$this->id                 = 'google_trusted_stores';
		$this->method_title       = __( 'Google Trusted Stores', 'wc-google-trusted-stores' );
		$this->method_description = __( 'Google Trusted Stores is a free service offered by Google that adds a badge to your online store allowing you to reach new customers and improve sales.', 'wc-google-trusted-stores' );

		$this->languages = $this->get_languages();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		foreach ( $this->form_fields as $key => $_ ) {
			$this->$key = $this->get_option( $key );
		}

		// Save the settings
		add_action( 'woocommerce_update_options_integration_google_trusted_stores', array( $this, 'process_admin_options' ) );

		// output the Google Trusted Stores badge code
		add_action( 'wp_footer', array( $this, 'output_badge_code' ) );

		// output the order confirmation module code
		add_action( 'woocommerce_thankyou', array( $this, 'output_confirmation_code' ) );
	}


	/**
	 * Initialise Settings Form Fields
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {

		/**
		 * Filter the settings array
		 *
		 * @since 1.0.0
		 * @param array associative array of the integration's settings
		 */
		$this->form_fields = apply_filters( 'wc_google_trusted_stores_settings', array(

			'title_general' => array(
				'title'       => __( 'General Options', 'wc-google-trusted-stores' ),
				'type'        => 'title',
				'description' => __( 'The following options are required to show the Google Trusted Stores Badge', 'wc-google-trusted-stores' ),
				'id'          => 'wc_gts_general_options',
			),

			'gts_id' => array(
				'title'       => __( 'Google Trusted Stores ID', 'wc-google-trusted-stores' ),
				'description' => __( 'Log into your Google Trusted Stores account to find your ID. e.g. <code>000000</code>', 'wc-google-trusted-stores' ),
				'type'        => 'text',
				'default'     => '',
			),

			'gts_locale' => array(
				'title'       => __( 'Locale', 'wc-google-trusted-stores' ),
				'description' => sprintf( __( 'Set the main locale of your site. The locale should be in the format of <code>%s</code>', 'wc-google-trusted-stores' ), esc_html( '<language>_<country>' ) ),
				'type'        => 'text',
				'default'     => defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US',
			),

			'gts_ship_time' => array(
				'title'       => __( 'Estimate Ship Time (weekdays)', 'wc-google-trusted-stores' ),
				'description' => __( 'Set the estimated ship time in weekdays', 'wc-google-trusted-stores' ),
				'type'        => 'text',
				'default'     => '1',
			),

			'gts_delivery_time' => array(
				'title'       => __( 'Estimate Delivery Time (weekdays)', 'wc-google-trusted-stores' ),
				'description' => __( 'Set the estimated delivery time in weekdays from the ship date', 'wc-google-trusted-stores' ),
				'type'        => 'text',
				'default'     => '7',
			),

			'title_google_shopping' => array(
				'title'       => __( 'Google Shopping Options', 'wc-google-trusted-stores' ),
				'type'        => 'title',
				'description' => __( 'The following options are recommended if you submit product feeds for Google Shopping. <br>Provide these fields only if you submit feeds for Google Shopping.', 'wc-google-trusted-stores' ),
			),

			'gts_google_shopping_account_enable' => array(
				'title'           => __( 'Google Shopping', 'wc-google-trusted-stores' ),
				'label'           => __( 'Enable Google Shopping', 'wc-google-trusted-stores' ),
				'type'            => 'checkbox',
				'default'         => 'no',
				'show_if_checked' => 'option',
			),

			'gts_google_shopping_account_id' => array(
				'title'           => __( 'Google Shopping Account ID', 'wc-google-trusted-stores' ),
				'description'     => __( 'Account ID from Google Shopping. This value should match the account ID you use to submit your product data feed you submit to Google Shopping.<br>Provide this field only if you submit feeds for Google Shopping.', 'wc-google-trusted-stores' ),
				'type'            => 'text',
				'default'         => '',
				'show_if_checked' => 'yes',
			),

			'gts_google_shopping_account_country' => array(
				'title'           => __( 'Google Shopping Account Country', 'wc-google-trusted-stores' ),
				'description'     => __( 'Account country from Google Shopping. This value should match the account country you use to submit your product data feed to Google Shopping.', 'wc-google-trusted-stores' ),
				'type'            => 'select',
				'class'           => 'chosen_select',
				'default'         => WC()->countries->get_base_country(),
				'options'         => WC()->countries->countries,
				'show_if_checked' => 'yes',
			),

			'gts_google_shopping_account_language' => array(
				'title'           => __( 'Language', 'wc-google-trusted-stores' ),
				'description'     => __( 'Account language from Google Shopping. This value should match the account language you use to submit your product data feed to Google Shopping.', 'wc-google-trusted-stores' ),
				'type'            => 'select',
				'class'           => 'chosen_select',
				'default'         => defined( 'WPLANG' ) && WPLANG ? substr( WPLANG, 0, 2 ) : 'en',
				'options'         => $this->languages,
				'show_if_checked' => 'yes',
			),

			'title_advanced' => array(
				'title'       => __( 'Advanced Options', 'wc-google-trusted-stores' ),
				'type'        => 'title',
				'description' => __( 'Use the following settings only if you having issues with validation.', 'wc-google-trusted-stores' ),
				'id'          => 'wc_gts_advanced_options',
			),

			'gts_old_src' => array(
				'title'   => __( 'Older JS source', 'wc-google-trusted-stores' ),
				'label'   => __( "Use the older Google Trusted Stores JavaScript. If you're unable to pass Google Trusted Stores validation, try enabling this setting as your account may still be using the older source code.", 'wc-google-trusted-stores' ),
				'type'    => 'checkbox',
				'default' => 'no',
			),

		) );
	}


	/**
	 * Google Trusted Stores badge code
	 *
	 * @since 1.0.0
	 */
	public function output_badge_code() {

		if ( ! $this->gts_id ) {
			return;
		}

		$code = '
				var gts = gts || [];
				gts.push(["id", "' . esc_js( $this->gts_id ) . '"]);
				gts.push(["locale", "' . esc_js( $this->gts_locale ) . '"]);
		';

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

		$src = 'www.googlecommerce.com/trustedstores/api/js';

		if ( 'yes' === $this->gts_old_src ) {
			$src = 'www.googlecommerce.com/trustedstores/gtmp_compiled.js';
		}

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
	 * @since 1.0.0
	 * @param mixed $order_id
	 */
	public function output_confirmation_code( $order_id ) {

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
	 * Helper method which returns the languages supported by Google Trusted Stores
	 *
	 * @since 1.0.0
	 * @return array associative array of languages
	 */
	public function get_languages() {

		/**
		 * Filter the languages array
		 *
		 * @since 1.0.0
		 * @param array associative array of languages
		 */
		return apply_filters( 'wc_google_trusted_stores_languages', array(
			'en' => __( 'English', 'wc-google-trusted-stores' ),
			'aa' => __( 'Afar', 'wc-google-trusted-stores' ),
			'ab' => __( 'Abkhazian', 'wc-google-trusted-stores' ),
			'af' => __( 'Afrikaans', 'wc-google-trusted-stores' ),
			'am' => __( 'Amharic', 'wc-google-trusted-stores' ),
			'ar' => __( 'Arabic', 'wc-google-trusted-stores' ),
			'as' => __( 'Assamese', 'wc-google-trusted-stores' ),
			'ay' => __( 'Aymara', 'wc-google-trusted-stores' ),
			'az' => __( 'Azerbaijani', 'wc-google-trusted-stores' ),
			'ba' => __( 'Bashkir', 'wc-google-trusted-stores' ),
			'be' => __( 'Byelorussian', 'wc-google-trusted-stores' ),
			'bg' => __( 'Bulgarian', 'wc-google-trusted-stores' ),
			'bh' => __( 'Bihari', 'wc-google-trusted-stores' ),
			'bi' => __( 'Bislama', 'wc-google-trusted-stores' ),
			'bn' => __( 'Bengali/Bangla', 'wc-google-trusted-stores' ),
			'bo' => __( 'Tibetan', 'wc-google-trusted-stores' ),
			'br' => __( 'Breton', 'wc-google-trusted-stores' ),
			'ca' => __( 'Catalan', 'wc-google-trusted-stores' ),
			'co' => __( 'Corsican', 'wc-google-trusted-stores' ),
			'cs' => __( 'Czech', 'wc-google-trusted-stores' ),
			'cy' => __( 'Welsh', 'wc-google-trusted-stores' ),
			'da' => __( 'Danish', 'wc-google-trusted-stores' ),
			'de' => __( 'German', 'wc-google-trusted-stores' ),
			'dz' => __( 'Bhutani', 'wc-google-trusted-stores' ),
			'el' => __( 'Greek', 'wc-google-trusted-stores' ),
			'eo' => __( 'Esperanto', 'wc-google-trusted-stores' ),
			'es' => __( 'Spanish', 'wc-google-trusted-stores' ),
			'et' => __( 'Estonian', 'wc-google-trusted-stores' ),
			'eu' => __( 'Basque', 'wc-google-trusted-stores' ),
			'fa' => __( 'Persian', 'wc-google-trusted-stores' ),
			'fi' => __( 'Finnish', 'wc-google-trusted-stores' ),
			'fj' => __( 'Fiji', 'wc-google-trusted-stores' ),
			'fo' => __( 'Faeroese', 'wc-google-trusted-stores' ),
			'fr' => __( 'French', 'wc-google-trusted-stores' ),
			'fy' => __( 'Frisian', 'wc-google-trusted-stores' ),
			'ga' => __( 'Irish', 'wc-google-trusted-stores' ),
			'gd' => __( 'Scots/Gaelic', 'wc-google-trusted-stores' ),
			'gl' => __( 'Galician', 'wc-google-trusted-stores' ),
			'gn' => __( 'Guarani', 'wc-google-trusted-stores' ),
			'gu' => __( 'Gujarati', 'wc-google-trusted-stores' ),
			'ha' => __( 'Hausa', 'wc-google-trusted-stores' ),
			'hi' => __( 'Hindi', 'wc-google-trusted-stores' ),
			'hr' => __( 'Croatian', 'wc-google-trusted-stores' ),
			'hu' => __( 'Hungarian', 'wc-google-trusted-stores' ),
			'hy' => __( 'Armenian', 'wc-google-trusted-stores' ),
			'ia' => __( 'Interlingua', 'wc-google-trusted-stores' ),
			'ie' => __( 'Interlingue', 'wc-google-trusted-stores' ),
			'ik' => __( 'Inupiak', 'wc-google-trusted-stores' ),
			'in' => __( 'Indonesian', 'wc-google-trusted-stores' ),
			'is' => __( 'Icelandic', 'wc-google-trusted-stores' ),
			'it' => __( 'Italian', 'wc-google-trusted-stores' ),
			'iw' => __( 'Hebrew', 'wc-google-trusted-stores' ),
			'ja' => __( 'Japanese', 'wc-google-trusted-stores' ),
			'ji' => __( 'Yiddish', 'wc-google-trusted-stores' ),
			'jw' => __( 'Javanese', 'wc-google-trusted-stores' ),
			'ka' => __( 'Georgian', 'wc-google-trusted-stores' ),
			'kk' => __( 'Kazakh', 'wc-google-trusted-stores' ),
			'kl' => __( 'Greenlandic', 'wc-google-trusted-stores' ),
			'km' => __( 'Cambodian', 'wc-google-trusted-stores' ),
			'kn' => __( 'Kannada', 'wc-google-trusted-stores' ),
			'ko' => __( 'Korean', 'wc-google-trusted-stores' ),
			'ks' => __( 'Kashmiri', 'wc-google-trusted-stores' ),
			'ku' => __( 'Kurdish', 'wc-google-trusted-stores' ),
			'ky' => __( 'Kirghiz', 'wc-google-trusted-stores' ),
			'la' => __( 'Latin', 'wc-google-trusted-stores' ),
			'ln' => __( 'Lingala', 'wc-google-trusted-stores' ),
			'lo' => __( 'Laothian', 'wc-google-trusted-stores' ),
			'lt' => __( 'Lithuanian', 'wc-google-trusted-stores' ),
			'lv' => __( 'Latvian/Lettish', 'wc-google-trusted-stores' ),
			'mg' => __( 'Malagasy', 'wc-google-trusted-stores' ),
			'mi' => __( 'Maori', 'wc-google-trusted-stores' ),
			'mk' => __( 'Macedonian', 'wc-google-trusted-stores' ),
			'ml' => __( 'Malayalam', 'wc-google-trusted-stores' ),
			'mn' => __( 'Mongolian', 'wc-google-trusted-stores' ),
			'mo' => __( 'Moldavian', 'wc-google-trusted-stores' ),
			'mr' => __( 'Marathi', 'wc-google-trusted-stores' ),
			'ms' => __( 'Malay', 'wc-google-trusted-stores' ),
			'mt' => __( 'Maltese', 'wc-google-trusted-stores' ),
			'my' => __( 'Burmese', 'wc-google-trusted-stores' ),
			'na' => __( 'Nauru', 'wc-google-trusted-stores' ),
			'ne' => __( 'Nepali', 'wc-google-trusted-stores' ),
			'nl' => __( 'Dutch', 'wc-google-trusted-stores' ),
			'no' => __( 'Norwegian', 'wc-google-trusted-stores' ),
			'oc' => __( 'Occitan', 'wc-google-trusted-stores' ),
			'om' => __( '(Afan)/Oromoor/Oriya', 'wc-google-trusted-stores' ),
			'pa' => __( 'Punjabi', 'wc-google-trusted-stores' ),
			'pl' => __( 'Polish', 'wc-google-trusted-stores' ),
			'ps' => __( 'Pashto/Pushto', 'wc-google-trusted-stores' ),
			'pt' => __( 'Portuguese', 'wc-google-trusted-stores' ),
			'qu' => __( 'Quechua', 'wc-google-trusted-stores' ),
			'rm' => __( 'Rhaeto-Romance', 'wc-google-trusted-stores' ),
			'rn' => __( 'Kirundi', 'wc-google-trusted-stores' ),
			'ro' => __( 'Romanian', 'wc-google-trusted-stores' ),
			'ru' => __( 'Russian', 'wc-google-trusted-stores' ),
			'rw' => __( 'Kinyarwanda', 'wc-google-trusted-stores' ),
			'sa' => __( 'Sanskrit', 'wc-google-trusted-stores' ),
			'sd' => __( 'Sindhi', 'wc-google-trusted-stores' ),
			'sg' => __( 'Sangro', 'wc-google-trusted-stores' ),
			'sh' => __( 'Serbo-Croatian', 'wc-google-trusted-stores' ),
			'si' => __( 'Singhalese', 'wc-google-trusted-stores' ),
			'sk' => __( 'Slovak', 'wc-google-trusted-stores' ),
			'sl' => __( 'Slovenian', 'wc-google-trusted-stores' ),
			'sm' => __( 'Samoan', 'wc-google-trusted-stores' ),
			'sn' => __( 'Shona', 'wc-google-trusted-stores' ),
			'so' => __( 'Somali', 'wc-google-trusted-stores' ),
			'sq' => __( 'Albanian', 'wc-google-trusted-stores' ),
			'sr' => __( 'Serbian', 'wc-google-trusted-stores' ),
			'ss' => __( 'Siswati', 'wc-google-trusted-stores' ),
			'st' => __( 'Sesotho', 'wc-google-trusted-stores' ),
			'su' => __( 'Sundanese', 'wc-google-trusted-stores' ),
			'sv' => __( 'Swedish', 'wc-google-trusted-stores' ),
			'sw' => __( 'Swahili', 'wc-google-trusted-stores' ),
			'ta' => __( 'Tamil', 'wc-google-trusted-stores' ),
			'te' => __( 'Tegulu', 'wc-google-trusted-stores' ),
			'tg' => __( 'Tajik', 'wc-google-trusted-stores' ),
			'th' => __( 'Thai', 'wc-google-trusted-stores' ),
			'ti' => __( 'Tigrinya', 'wc-google-trusted-stores' ),
			'tk' => __( 'Turkmen', 'wc-google-trusted-stores' ),
			'tl' => __( 'Tagalog', 'wc-google-trusted-stores' ),
			'tn' => __( 'Setswana', 'wc-google-trusted-stores' ),
			'to' => __( 'Tonga', 'wc-google-trusted-stores' ),
			'tr' => __( 'Turkish', 'wc-google-trusted-stores' ),
			'ts' => __( 'Tsonga', 'wc-google-trusted-stores' ),
			'tt' => __( 'Tatar', 'wc-google-trusted-stores' ),
			'tw' => __( 'Twi', 'wc-google-trusted-stores' ),
			'uk' => __( 'Ukrainian', 'wc-google-trusted-stores' ),
			'ur' => __( 'Urdu', 'wc-google-trusted-stores' ),
			'uz' => __( 'Uzbek', 'wc-google-trusted-stores' ),
			'vi' => __( 'Vietnamese', 'wc-google-trusted-stores' ),
			'vo' => __( 'Volapuk', 'wc-google-trusted-stores' ),
			'wo' => __( 'Wolof', 'wc-google-trusted-stores' ),
			'xh' => __( 'Xhosa', 'wc-google-trusted-stores' ),
			'yo' => __( 'Yoruba', 'wc-google-trusted-stores' ),
			'zh' => __( 'Chinese', 'wc-google-trusted-stores' ),
			'zu' => __( 'Zulu', 'wc-google-trusted-stores' ),
		) );
	}


}
