<?php

/**
 * Google Analytics Integration
 *
 * Allows tracking code to be inserted into store pages.
 *
 * @class 		WC_Google_Trusted_Stores
 * @extends		WC_Integration
 */
class WC_Google_Trusted_Stores extends WC_Integration {

	/**
	 * Init and hook in the integration.
	 *
	 * @access public
	 * @return void
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
		$this->gts_language                         = $this->get_option( 'gts_language' );
		$this->gts_ship_time                        = $this->get_option( 'gts_ship_time' );
		$this->gts_google_shopping_account_enable   = $this->get_option( 'gts_google_shopping_account_enable' );
		$this->gts_google_shopping_account_id       = $this->get_option( 'gts_google_shopping_account_id' );
		$this->gts_google_shopping_account_country  = $this->get_option( 'gts_google_shopping_account_country' );
		$this->gts_google_shopping_account_language = $this->get_option( 'gts_google_shopping_account_language' );


		// Actions
		add_action( 'woocommerce_update_options_integration_google_trusted_stores', array( $this, 'process_admin_options') );

		// Google Trusted Stores Badge Code
		add_action( 'wp_footer', array( $this, 'badge_code' ) );

		// Order Confirmation Module Code
		add_action( 'woocommerce_thankyou', array( $this, 'confirmation_code' ) );

		//
	}


	/**
	 * Initialise Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields() {

		$this->form_fields = array(

			'title_general' => array (
				'title'       => __( 'General Options', 'wc_google_trusted_stores' ),
				'type'        => 'title',
				'description' => __( 'The following options are required to show the Google Trusted Stores Badge', 'wc_google_trusted_stores' ),
				'id'          => 'wc_gts_general_options'
			),

			'gts_id' => array(
				'title'       => __( 'Google Trusted Stores ID', 'wc_google_trusted_stores' ),
				'description' => __( 'Log into your Google Trusted Stores account to find your ID. e.g. <code>000000</code>', 'wc_google_trusted_stores' ),
				'type'        => 'text',
				'default'     => ''
			),

			'gts_language' => array(
				'title'       => __( 'Language', 'wc_google_trusted_stores' ),
				'description' => __( 'Set the main language used by your store', 'wc_google_trusted_stores' ),
				'type'        => 'select',
				'class'       => 'chosen_select',
				'default'     => defined( 'WPLANG' ) && WPLANG ? substr(WPLANG, 0, 2) : 'en',
				'options'     => $this->languages
			),

			'gts_ship_time' => array(
				'title'       => __( 'Estimate Ship Time (weekdays)', 'wc_google_trusted_stores' ),
				'description' => __( 'Set the main language used by your store', 'wc_google_trusted_stores' ),
				'type'        => 'text',
				'default'     => '1'
			),

			'title_google_shopping' => array (
				'title'       => __( 'Google Shopping Options', 'wc_google_trusted_stores' ),
				'type'        => 'title',
				'description' => __( 'The following options are recommended if you submit product feeds for Google Shopping.<br>Provide these fields only if you submit feeds for Google Shopping.', 'wc_google_trusted_stores' )
			),

			'gts_google_shopping_account_enable' => array(
				'title'       => __( 'Enable Google Shopping', 'wc_google_trusted_stores' ),
				'type'        => 'checkbox',
				'default'     => 'no',
				'show_if_checked' => 'option'
			),

			'gts_google_shopping_account_id' => array(
				'title'       => __( 'Google Shopping Account ID', 'wc_google_trusted_stores' ),
				'description' => __( 'Account ID from Google Shopping. This value should match the account ID you use to submit your product data feed you submit to Google Shopping.<br>Provide this field only if you submit feeds for Google Shopping.', 'wc_google_trusted_stores' ),
				'type'        => 'text',
				'default'     => '',
				'show_if_checked' => 'yes'
			),

			'gts_google_shopping_account_country' => array(
				'title'       => __( 'Google Shopping Account Country', 'wc_google_trusted_stores' ),
				'description' => __( 'Account country from Google Shopping. This value should match the account country you use to submit your product data feed to Google Shopping.', 'wc_google_trusted_stores' ),
				'type'        => 'select',
				'class'       => 'chosen_select',
				'default'     => WC()->countries->get_base_country(),
				'options'     => WC()->countries->countries,
				'show_if_checked' => 'yes'
			),

			'gts_google_shopping_account_language' => array(
				'title'       => __( 'Language', 'wc_google_trusted_stores' ),
				'description' => __( 'Account language from Google Shopping. This value should match the account language you use to submit your product data feed to Google Shopping.', 'wc_google_trusted_stores' ),
				'type'        => 'select',
				'class'       => 'chosen_select',
				'default'     => defined( 'WPLANG' ) && WPLANG ? substr(WPLANG, 0, 2) : 'en',
				'options'     => $this->languages,
				'show_if_checked' => 'yes'
			),

			'gts_html' => array(
				'type' => 'gts'
			)

		);

	} // End init_form_fields()


	function generate_gts_html() {
		ob_start();
		?>
		<script type="text/javascript">

			jQuery(window).load(function(){

				jQuery('#woocommerce_google_trusted_stores_gts_google_shopping_account_enable').change(function(){
					if ( jQuery(this).is(':checked') ) {
						jQuery('#woocommerce_google_trusted_stores_gts_google_shopping_account_id').closest('tr').show();
						jQuery('#woocommerce_google_trusted_stores_gts_google_shopping_account_country').closest('tr').show();
						jQuery('#woocommerce_google_trusted_stores_gts_google_shopping_account_language').closest('tr').show();
					} else {
						jQuery('#woocommerce_google_trusted_stores_gts_google_shopping_account_id').closest('tr').hide();
						jQuery('#woocommerce_google_trusted_stores_gts_google_shopping_account_country').closest('tr').hide();
						jQuery('#woocommerce_google_trusted_stores_gts_google_shopping_account_language').closest('tr').hide();
					}
				}).change();
			});

		</script>
		<?php
		return ob_get_clean();
	}


	/**
	 * Google Trusted Stores badge code
	 *
	 * @access public
	 * @return void
	 */
	function badge_code() {

		if ( ! $this->gts_id ) return;

		$code = 'var gts = gts || [];
					gts.push(["id", "' . esc_js( $this->gts_id ) . '"]);
					gts.push(["locale", "' . esc_js( $this->gts_language ) . '"]);';

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

		$code .= '
				(function() {
					var scheme = (("https:" == document.location.protocol) ? "https://" : "http://");
					var gts = document.createElement("script");
					gts.type = "text/javascript";
					gts.async = true;
					gts.src = scheme + "www.googlecommerce.com/trustedstores/gtmp_compiled.js";
					var s = document.getElementsByTagName("script")[0];
					s.parentNode.insertBefore(gts, s);
				})();
			';

		echo '<!-- BEGIN: Google Trusted Stores --><script type="text/javascript">' . $code . '</script><!-- END: Google Trusted Stores -->';
	}


	/**
	 * Google Trusted Stores confirmation code
	 *
	 * @access public
	 * @param mixed $order_id
	 * @return void
	 */
	function confirmation_code( $order_id ) {

		if ( get_post_meta( $order_id, '_wc_gts_tracked', true ) == 1 ) { //current_user_can('manage_options')
			return;
		}

		if ( ! $this->gts_id  ) return;

		// Get the order
		$order = new WC_Order( $order_id );

		// Get order items
		$items = $order->get_items();

		$ship_date = date ( 'Y-m-d' , strtotime ( $this->gts_ship_time . ' weekdays', strtotime( $order->order_date ) ) );
		$ship_date = apply_filters( 'wc_google_trusted_stores_order_ship_date', $ship_date, $order_id );

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
			<span id="gts-o-has-preorder">' . ( $has_backorder ? 'Y' : 'N' ) . '</span>
			<span id="gts-o-has-digital">' . ( $order->has_downloadable_item() ? 'Y' : 'N' ) . '</span>
			<!-- end order and merchant information -->
		'; //<span id="gts-o-est-delivery-date">' . esc_html( $order->?? ) . '</span>

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
	 * @access public
	 * @return array
	 */
	function get_languages() {

		return array(
			'en' => 'English' ,
			'aa' => 'Afar' ,
			'ab' => 'Abkhazian' ,
			'af' => 'Afrikaans' ,
			'am' => 'Amharic' ,
			'ar' => 'Arabic' ,
			'as' => 'Assamese' ,
			'ay' => 'Aymara' ,
			'az' => 'Azerbaijani' ,
			'ba' => 'Bashkir' ,
			'be' => 'Byelorussian' ,
			'bg' => 'Bulgarian' ,
			'bh' => 'Bihari' ,
			'bi' => 'Bislama' ,
			'bn' => 'Bengali/Bangla' ,
			'bo' => 'Tibetan' ,
			'br' => 'Breton' ,
			'ca' => 'Catalan' ,
			'co' => 'Corsican' ,
			'cs' => 'Czech' ,
			'cy' => 'Welsh' ,
			'da' => 'Danish' ,
			'de' => 'German' ,
			'dz' => 'Bhutani' ,
			'el' => 'Greek' ,
			'eo' => 'Esperanto' ,
			'es' => 'Spanish' ,
			'et' => 'Estonian' ,
			'eu' => 'Basque' ,
			'fa' => 'Persian' ,
			'fi' => 'Finnish' ,
			'fj' => 'Fiji' ,
			'fo' => 'Faeroese' ,
			'fr' => 'French' ,
			'fy' => 'Frisian' ,
			'ga' => 'Irish' ,
			'gd' => 'Scots/Gaelic' ,
			'gl' => 'Galician' ,
			'gn' => 'Guarani' ,
			'gu' => 'Gujarati' ,
			'ha' => 'Hausa' ,
			'hi' => 'Hindi' ,
			'hr' => 'Croatian' ,
			'hu' => 'Hungarian' ,
			'hy' => 'Armenian' ,
			'ia' => 'Interlingua' ,
			'ie' => 'Interlingue' ,
			'ik' => 'Inupiak' ,
			'in' => 'Indonesian' ,
			'is' => 'Icelandic' ,
			'it' => 'Italian' ,
			'iw' => 'Hebrew' ,
			'ja' => 'Japanese' ,
			'ji' => 'Yiddish' ,
			'jw' => 'Javanese' ,
			'ka' => 'Georgian' ,
			'kk' => 'Kazakh' ,
			'kl' => 'Greenlandic' ,
			'km' => 'Cambodian' ,
			'kn' => 'Kannada' ,
			'ko' => 'Korean' ,
			'ks' => 'Kashmiri' ,
			'ku' => 'Kurdish' ,
			'ky' => 'Kirghiz' ,
			'la' => 'Latin' ,
			'ln' => 'Lingala' ,
			'lo' => 'Laothian' ,
			'lt' => 'Lithuanian' ,
			'lv' => 'Latvian/Lettish' ,
			'mg' => 'Malagasy' ,
			'mi' => 'Maori' ,
			'mk' => 'Macedonian' ,
			'ml' => 'Malayalam' ,
			'mn' => 'Mongolian' ,
			'mo' => 'Moldavian' ,
			'mr' => 'Marathi' ,
			'ms' => 'Malay' ,
			'mt' => 'Maltese' ,
			'my' => 'Burmese' ,
			'na' => 'Nauru' ,
			'ne' => 'Nepali' ,
			'nl' => 'Dutch' ,
			'no' => 'Norwegian' ,
			'oc' => 'Occitan' ,
			'om' => '(Afan)/Oromoor/Oriya' ,
			'pa' => 'Punjabi' ,
			'pl' => 'Polish' ,
			'ps' => 'Pashto/Pushto' ,
			'pt' => 'Portuguese' ,
			'qu' => 'Quechua' ,
			'rm' => 'Rhaeto-Romance' ,
			'rn' => 'Kirundi' ,
			'ro' => 'Romanian' ,
			'ru' => 'Russian' ,
			'rw' => 'Kinyarwanda' ,
			'sa' => 'Sanskrit' ,
			'sd' => 'Sindhi' ,
			'sg' => 'Sangro' ,
			'sh' => 'Serbo-Croatian' ,
			'si' => 'Singhalese' ,
			'sk' => 'Slovak' ,
			'sl' => 'Slovenian' ,
			'sm' => 'Samoan' ,
			'sn' => 'Shona' ,
			'so' => 'Somali' ,
			'sq' => 'Albanian' ,
			'sr' => 'Serbian' ,
			'ss' => 'Siswati' ,
			'st' => 'Sesotho' ,
			'su' => 'Sundanese' ,
			'sv' => 'Swedish' ,
			'sw' => 'Swahili' ,
			'ta' => 'Tamil' ,
			'te' => 'Tegulu' ,
			'tg' => 'Tajik' ,
			'th' => 'Thai' ,
			'ti' => 'Tigrinya' ,
			'tk' => 'Turkmen' ,
			'tl' => 'Tagalog' ,
			'tn' => 'Setswana' ,
			'to' => 'Tonga' ,
			'tr' => 'Turkish' ,
			'ts' => 'Tsonga' ,
			'tt' => 'Tatar' ,
			'tw' => 'Twi' ,
			'uk' => 'Ukrainian' ,
			'ur' => 'Urdu' ,
			'uz' => 'Uzbek' ,
			'vi' => 'Vietnamese' ,
			'vo' => 'Volapuk' ,
			'wo' => 'Wolof' ,
			'xh' => 'Xhosa' ,
			'yo' => 'Yoruba' ,
			'zh' => 'Chinese' ,
			'zu' => 'Zulu' ,
		);
	}

}
