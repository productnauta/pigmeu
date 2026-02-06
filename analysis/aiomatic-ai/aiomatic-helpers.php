<?php
defined('ABSPATH') or die();
require_once (dirname(__FILE__) . "/aiomatic-tools.php");
require_once (dirname(__FILE__) . "/aiomatic-generators.php");
require_once (dirname(__FILE__) . "/aiomatic-omniblock-helpers.php");
$aiomatic_language_by_locale = array(
'af_NA' => "Afrikaans (Namibia)",
'af_ZA' => "Afrikaans (South Africa)",
'af' => "Afrikaans",
'ak_GH' => "Akan (Ghana)",
'ak' => "Akan",
'sq_AL' => "Albanian (Albania)",
'sq' => "Albanian",
'am_ET' => "Amharic (Ethiopia)",
'am' => "Amharic",
'ar_DZ' => "Arabic (Algeria)",
'ar_BH' => "Arabic (Bahrain)",
'ar_EG' => "Arabic (Egypt)",
'ar_IQ' => "Arabic (Iraq)",
'ar_JO' => "Arabic (Jordan)",
'ar_KW' => "Arabic (Kuwait)",
'ar_LB' => "Arabic (Lebanon)",
'ar_LY' => "Arabic (Libya)",
'ar_MA' => "Arabic (Morocco)",
'ar_OM' => "Arabic (Oman)",
'ar_QA' => "Arabic (Qatar)",
'ar_SA' => "Arabic (Saudi Arabia)",
'ar_SD' => "Arabic (Sudan)",
'ar_SY' => "Arabic (Syria)",
'ar_TN' => "Arabic (Tunisia)",
'ar_AE' => "Arabic (United Arab Emirates)",
'ar_YE' => "Arabic (Yemen)",
'ar' => "Arabic",
'hy_AM' => "Armenian (Armenia)",
'hy' => "Armenian",
'as_IN' => "Assamese (India)",
'as' => "Assamese",
'asa_TZ' => "Asu (Tanzania)",
'asa' => "Asu",
'az_Cyrl' => "Azerbaijani (Cyrillic)",
'az_Cyrl_AZ' => "Azerbaijani (Cyrillic, Azerbaijan)",
'az_Latn' => "Azerbaijani (Latin)",
'az_Latn_AZ' => "Azerbaijani (Latin, Azerbaijan)",
'az' => "Azerbaijani",
'bm_ML' => "Bambara (Mali)",
'bm' => "Bambara",
'eu_ES' => "Basque (Spain)",
'eu' => "Basque",
'be_BY' => "Belarusian (Belarus)",
'be' => "Belarusian",
'bem_ZM' => "Bemba (Zambia)",
'bem' => "Bemba",
'bez_TZ' => "Bena (Tanzania)",
'bez' => "Bena",
'bn_BD' => "Bengali (Bangladesh)",
'bn_IN' => "Bengali (India)",
'bn' => "Bengali",
'bs_BA' => "Bosnian (Bosnia and Herzegovina)",
'bs' => "Bosnian",
'bg_BG' => "Bulgarian (Bulgaria)",
'bg' => "Bulgarian",
'my_MM' => "Burmese (Myanmar [Burma])",
'my' => "Burmese",
'yue_Hant_HK' => "Cantonese (Traditional, Hong Kong SAR China)",
'ca_ES' => "Catalan (Spain)",
'ca' => "Catalan",
'tzm_Latn' => "Central Morocco Tamazight (Latin)",
'tzm_Latn_MA' => "Central Morocco Tamazight (Latin, Morocco)",
'tzm' => "Central Morocco Tamazight",
'chr_US' => "Cherokee (United States)",
'chr' => "Cherokee",
'cgg_UG' => "Chiga (Uganda)",
'cgg' => "Chiga",
'zh_Hans' => "Chinese (Simplified Han)",
'zh_Hans_CN' => "Chinese (Simplified Han, China)",
'zh_Hans_HK' => "Chinese (Simplified Han, Hong Kong SAR China)",
'zh_Hans_MO' => "Chinese (Simplified Han, Macau SAR China)",
'zh_Hans_SG' => "Chinese (Simplified Han, Singapore)",
'zh_Hant' => "Chinese (Traditional Han)",
'zh_Hant_HK' => "Chinese (Traditional Han, Hong Kong SAR China)",
'zh_Hant_MO' => "Chinese (Traditional Han, Macau SAR China)",
'zh_Hant_TW' => "Chinese (Traditional Han, Taiwan)",
'zh' => "Chinese",
'kw_GB' => "Cornish (United Kingdom)",
'kw' => "Cornish",
'hr_HR' => "Croatian (Croatia)",
'hr' => "Croatian",
'cs_CZ' => "Czech (Czech Republic)",
'cs' => "Czech",
'da_DK' => "Danish (Denmark)",
'da' => "Danish",
'nl_BE' => "Dutch (Belgium)",
'nl_NL' => "Dutch (Netherlands)",
'nl' => "Dutch",
'ebu_KE' => "Embu (Kenya)",
'ebu' => "Embu",
'en_AS' => "English (American Samoa)",
'en_AU' => "English (Australia)",
'en_BE' => "English (Belgium)",
'en_BZ' => "English (Belize)",
'en_BW' => "English (Botswana)",
'en_CA' => "English (Canada)",
'en_GU' => "English (Guam)",
'en_HK' => "English (Hong Kong SAR China)",
'en_IN' => "English (India)",
'en_IE' => "English (Ireland)",
'en_IL' => "English (Israel)",
'en_JM' => "English (Jamaica)",
'en_MT' => "English (Malta)",
'en_MH' => "English (Marshall Islands)",
'en_MU' => "English (Mauritius)",
'en_NA' => "English (Namibia)",
'en_NZ' => "English (New Zealand)",
'en_MP' => "English (Northern Mariana Islands)",
'en_PK' => "English (Pakistan)",
'en_PH' => "English (Philippines)",
'en_SG' => "English (Singapore)",
'en_ZA' => "English (South Africa)",
'en_TT' => "English (Trinidad and Tobago)",
'en_UM' => "English (U.S. Minor Outlying Islands)",
'en_VI' => "English (U.S. Virgin Islands)",
'en_GB' => "English (United Kingdom)",
'en_US' => "English (United States)",
'en_ZW' => "English (Zimbabwe)",
'en' => "English",
'eo' => "Esperanto",
'et_EE' => "Estonian (Estonia)",
'et' => "Estonian",
'ee_GH' => "Ewe (Ghana)",
'ee_TG' => "Ewe (Togo)",
'ee' => "Ewe",
'fo_FO' => "Faroese (Faroe Islands)",
'fo' => "Faroese",
'fil_PH' => "Filipino (Philippines)",
'fil' => "Filipino",
'fi_FI' => "Finnish (Finland)",
'fi' => "Finnish",
'fr_BE' => "French (Belgium)",
'fr_BJ' => "French (Benin)",
'fr_BF' => "French (Burkina Faso)",
'fr_BI' => "French (Burundi)",
'fr_CM' => "French (Cameroon)",
'fr_CA' => "French (Canada)",
'fr_CF' => "French (Central African Republic)",
'fr_TD' => "French (Chad)",
'fr_KM' => "French (Comoros)",
'fr_CG' => "French (Congo - Brazzaville)",
'fr_CD' => "French (Congo - Kinshasa)",
'fr_CI' => "French (Côte d'Ivoire)",
'fr_DJ' => "French (Djibouti)",
'fr_GQ' => "French (Equatorial Guinea)",
'fr_FR' => "French (France)",
'fr_GA' => "French (Gabon)",
'fr_GP' => "French (Guadeloupe)",
'fr_GN' => "French (Guinea)",
'fr_LU' => "French (Luxembourg)",
'fr_MG' => "French (Madagascar)",
'fr_ML' => "French (Mali)",
'fr_MQ' => "French (Martinique)",
'fr_MC' => "French (Monaco)",
'fr_NE' => "French (Niger)",
'fr_RW' => "French (Rwanda)",
'fr_RE' => "French (Réunion)",
'fr_BL' => "French (Saint Barthélemy)",
'fr_MF' => "French (Saint Martin)",
'fr_SN' => "French (Senegal)",
'fr_CH' => "French (Switzerland)",
'fr_TG' => "French (Togo)",
'fr' => "French",
'ff_SN' => "Fulah (Senegal)",
'ff' => "Fulah",
'gl_ES' => "Galician (Spain)",
'gl' => "Galician",
'lg_UG' => "Ganda (Uganda)",
'lg' => "Ganda",
'ka_GE' => "Georgian (Georgia)",
'ka' => "Georgian",
'de_AT' => "German (Austria)",
'de_BE' => "German (Belgium)",
'de_DE' => "German (Germany)",
'de_LI' => "German (Liechtenstein)",
'de_LU' => "German (Luxembourg)",
'de_CH' => "German (Switzerland)",
'de' => "German",
'el_CY' => "Greek (Cyprus)",
'el_GR' => "Greek (Greece)",
'el' => "Greek",
'gu_IN' => "Gujarati (India)",
'gu' => "Gujarati",
'guz_KE' => "Gusii (Kenya)",
'guz' => "Gusii",
'ha_Latn' => "Hausa (Latin)",
'ha_Latn_GH' => "Hausa (Latin, Ghana)",
'ha_Latn_NE' => "Hausa (Latin, Niger)",
'ha_Latn_NG' => "Hausa (Latin, Nigeria)",
'ha' => "Hausa",
'haw_US' => "Hawaiian (United States)",
'haw' => "Hawaiian",
'he_IL' => "Hebrew (Israel)",
'he' => "Hebrew",
'hi_IN' => "Hindi (India)",
'hi' => "Hindi",
'hu_HU' => "Hungarian (Hungary)",
'hu' => "Hungarian",
'is_IS' => "Icelandic (Iceland)",
'is' => "Icelandic",
'ig_NG' => "Igbo (Nigeria)",
'ig' => "Igbo",
'id_ID' => "Indonesian (Indonesia)",
'id' => "Indonesian",
'ga_IE' => "Irish (Ireland)",
'ga' => "Irish",
'it_IT' => "Italian (Italy)",
'it_CH' => "Italian (Switzerland)",
'it' => "Italian",
'ja_JP' => "Japanese (Japan)",
'ja' => "Japanese",
'kea_CV' => "Kabuverdianu (Cape Verde)",
'kea' => "Kabuverdianu",
'kab_DZ' => "Kabyle (Algeria)",
'kab' => "Kabyle",
'kl_GL' => "Kalaallisut (Greenland)",
'kl' => "Kalaallisut",
'kln_KE' => "Kalenjin (Kenya)",
'kln' => "Kalenjin",
'kam_KE' => "Kamba (Kenya)",
'kam' => "Kamba",
'kn_IN' => "Kannada (India)",
'kn' => "Kannada",
'kk_Cyrl' => "Kazakh (Cyrillic)",
'kk_Cyrl_KZ' => "Kazakh (Cyrillic, Kazakhstan)",
'kk' => "Kazakh",
'km_KH' => "Khmer (Cambodia)",
'km' => "Khmer",
'ki_KE' => "Kikuyu (Kenya)",
'ki' => "Kikuyu",
'rw_RW' => "Kinyarwanda (Rwanda)",
'rw' => "Kinyarwanda",
'kok_IN' => "Konkani (India)",
'kok' => "Konkani",
'ko_KR' => "Korean (South Korea)",
'ko' => "Korean",
'khq_ML' => "Koyra Chiini (Mali)",
'khq' => "Koyra Chiini",
'ses_ML' => "Koyraboro Senni (Mali)",
'ses' => "Koyraboro Senni",
'lag_TZ' => "Langi (Tanzania)",
'lag' => "Langi",
'lv_LV' => "Latvian (Latvia)",
'lv' => "Latvian",
'lt_LT' => "Lithuanian (Lithuania)",
'lt' => "Lithuanian",
'luo_KE' => "Luo (Kenya)",
'luo' => "Luo",
'luy_KE' => "Luyia (Kenya)",
'luy' => "Luyia",
'mk_MK' => "Macedonian (Macedonia)",
'mk' => "Macedonian",
'jmc_TZ' => "Machame (Tanzania)",
'jmc' => "Machame",
'kde_TZ' => "Makonde (Tanzania)",
'kde' => "Makonde",
'mg_MG' => "Malagasy (Madagascar)",
'mg' => "Malagasy",
'ms_BN' => "Malay (Brunei)",
'ms_MY' => "Malay (Malaysia)",
'ms' => "Malay",
'ml_IN' => "Malayalam (India)",
'ml' => "Malayalam",
'mt_MT' => "Maltese (Malta)",
'mt' => "Maltese",
'gv_GB' => "Manx (United Kingdom)",
'gv' => "Manx",
'mr_IN' => "Marathi (India)",
'mr' => "Marathi",
'mas_KE' => "Masai (Kenya)",
'mas_TZ' => "Masai (Tanzania)",
'mas' => "Masai",
'mer_KE' => "Meru (Kenya)",
'mer' => "Meru",
'mfe_MU' => "Morisyen (Mauritius)",
'mfe' => "Morisyen",
'naq_NA' => "Nama (Namibia)",
'naq' => "Nama",
'ne_IN' => "Nepali (India)",
'ne_NP' => "Nepali (Nepal)",
'ne' => "Nepali",
'nd_ZW' => "North Ndebele (Zimbabwe)",
'nd' => "North Ndebele",
'nb_NO' => "Norwegian Bokmål (Norway)",
'nb' => "Norwegian Bokmål",
'nn_NO' => "Norwegian Nynorsk (Norway)",
'nn' => "Norwegian Nynorsk",
'nyn_UG' => "Nyankole (Uganda)",
'nyn' => "Nyankole",
'or_IN' => "Oriya (India)",
'or' => "Oriya",
'om_ET' => "Oromo (Ethiopia)",
'om_KE' => "Oromo (Kenya)",
'om' => "Oromo",
'ps_AF' => "Pashto (Afghanistan)",
'ps' => "Pashto",
'fa_AF' => "Persian (Afghanistan)",
'fa_IR' => "Persian (Iran)",
'fa' => "Persian",
'pl_PL' => "Polish (Poland)",
'pl' => "Polish",
'pt_BR' => "Portuguese (Brazil)",
'pt_GW' => "Portuguese (Guinea-Bissau)",
'pt_MZ' => "Portuguese (Mozambique)",
'pt_PT' => "Portuguese (Portugal)",
'pt' => "Portuguese",
'pa_Arab' => "Punjabi (Arabic)",
'pa_Arab_PK' => "Punjabi (Arabic, Pakistan)",
'pa_Guru' => "Punjabi (Gurmukhi)",
'pa_Guru_IN' => "Punjabi (Gurmukhi, India)",
'pa' => "Punjabi",
'ro_MD' => "Romanian (Moldova)",
'ro_RO' => "Romanian (Romania)",
'ro' => "Romanian",
'rm_CH' => "Romansh (Switzerland)",
'rm' => "Romansh",
'rof_TZ' => "Rombo (Tanzania)",
'rof' => "Rombo",
'ru_MD' => "Russian (Moldova)",
'ru_RU' => "Russian (Russia)",
'ru_UA' => "Russian (Ukraine)",
'ru' => "Russian",
'rwk_TZ' => "Rwa (Tanzania)",
'rwk' => "Rwa",
'saq_KE' => "Samburu (Kenya)",
'saq' => "Samburu",
'sg_CF' => "Sango (Central African Republic)",
'sg' => "Sango",
'seh_MZ' => "Sena (Mozambique)",
'seh' => "Sena",
'sr_Cyrl' => "Serbian (Cyrillic)",
'sr_Cyrl_BA' => "Serbian (Cyrillic, Bosnia and Herzegovina)",
'sr_Cyrl_ME' => "Serbian (Cyrillic, Montenegro)",
'sr_Cyrl_RS' => "Serbian (Cyrillic, Serbia)",
'sr_Latn' => "Serbian (Latin)",
'sr_Latn_BA' => "Serbian (Latin, Bosnia and Herzegovina)",
'sr_Latn_ME' => "Serbian (Latin, Montenegro)",
'sr_Latn_RS' => "Serbian (Latin, Serbia)",
'sr' => "Serbian",
'sn_ZW' => "Shona (Zimbabwe)",
'sn' => "Shona",
'ii_CN' => "Sichuan Yi (China)",
'ii' => "Sichuan Yi",
'si_LK' => "Sinhala (Sri Lanka)",
'si' => "Sinhala",
'sk_SK' => "Slovak (Slovakia)",
'sk' => "Slovak",
'sl_SI' => "Slovenian (Slovenia)",
'sl' => "Slovenian",
'xog_UG' => "Soga (Uganda)",
'xog' => "Soga",
'so_DJ' => "Somali (Djibouti)",
'so_ET' => "Somali (Ethiopia)",
'so_KE' => "Somali (Kenya)",
'so_SO' => "Somali (Somalia)",
'so' => "Somali",
'es_AR' => "Spanish (Argentina)",
'es_BO' => "Spanish (Bolivia)",
'es_CL' => "Spanish (Chile)",
'es_CO' => "Spanish (Colombia)",
'es_CR' => "Spanish (Costa Rica)",
'es_DO' => "Spanish (Dominican Republic)",
'es_EC' => "Spanish (Ecuador)",
'es_SV' => "Spanish (El Salvador)",
'es_GQ' => "Spanish (Equatorial Guinea)",
'es_GT' => "Spanish (Guatemala)",
'es_HN' => "Spanish (Honduras)",
'es_419' => "Spanish (Latin America)",
'es_MX' => "Spanish (Mexico)",
'es_NI' => "Spanish (Nicaragua)",
'es_PA' => "Spanish (Panama)",
'es_PY' => "Spanish (Paraguay)",
'es_PE' => "Spanish (Peru)",
'es_PR' => "Spanish (Puerto Rico)",
'es_ES' => "Spanish (Spain)",
'es_US' => "Spanish (United States)",
'es_UY' => "Spanish (Uruguay)",
'es_VE' => "Spanish (Venezuela)",
'es' => "Spanish",
'sw_KE' => "Swahili (Kenya)",
'sw_TZ' => "Swahili (Tanzania)",
'sw' => "Swahili",
'sv_FI' => "Swedish (Finland)",
'sv_SE' => "Swedish (Sweden)",
'sv' => "Swedish",
'gsw_CH' => "Swiss German (Switzerland)",
'gsw' => "Swiss German",
'shi_Latn' => "Tachelhit (Latin)",
'shi_Latn_MA' => "Tachelhit (Latin, Morocco)",
'shi_Tfng' => "Tachelhit (Tifinagh)",
'shi_Tfng_MA' => "Tachelhit (Tifinagh, Morocco)",
'shi' => "Tachelhit",
'dav_KE' => "Taita (Kenya)",
'dav' => "Taita",
'ta_IN' => "Tamil (India)",
'ta_LK' => "Tamil (Sri Lanka)",
'ta' => "Tamil",
'te_IN' => "Telugu (India)",
'te' => "Telugu",
'teo_KE' => "Teso (Kenya)",
'teo_UG' => "Teso (Uganda)",
'teo' => "Teso",
'th_TH' => "Thai (Thailand)",
'th' => "Thai",
'bo_CN' => "Tibetan (China)",
'bo_IN' => "Tibetan (India)",
'bo' => "Tibetan",
'ti_ER' => "Tigrinya (Eritrea)",
'ti_ET' => "Tigrinya (Ethiopia)",
'ti' => "Tigrinya",
'to_TO' => "Tonga (Tonga)",
'to' => "Tonga",
'tr_TR' => "Turkish (Turkey)",
'tr' => "Turkish",
'uk_UA' => "Ukrainian (Ukraine)",
'uk' => "Ukrainian",
'ur_IN' => "Urdu (India)",
'ur_PK' => "Urdu (Pakistan)",
'ur' => "Urdu",
'uz_Arab' => "Uzbek (Arabic)",
'uz_Arab_AF' => "Uzbek (Arabic, Afghanistan)",
'uz_Cyrl' => "Uzbek (Cyrillic)",
'uz_Cyrl_UZ' => "Uzbek (Cyrillic, Uzbekistan)",
'uz_Latn' => "Uzbek (Latin)",
'uz_Latn_UZ' => "Uzbek (Latin, Uzbekistan)",
'uz' => "Uzbek",
'vi_VN' => "Vietnamese (Vietnam)",
'vi' => "Vietnamese",
'vun_TZ' => "Vunjo (Tanzania)",
'vun' => "Vunjo",
'cy_GB' => "Welsh (United Kingdom)",
'cy' => "Welsh",
'yo_NG' => "Yoruba (Nigeria)",
'yo' => "Yoruba",
'zu_ZA' => "Zulu (South Africa)",
'zu' => "Zulu"
);

function aiomatic_convert_html_to_gutenberg_blocks($content)
{
    $convertor = new AiomaticBlockConverter();
    if(strstr($content, '<article>') !== false && strstr($content, '</article>') !== false)
    {
        $content = str_replace('<article>', '', $content);
        $content = str_replace('</article>', '', $content);
    }
    $converted = $convertor->convert_blocks($content);
    if($converted)
    {
        return $converted;
    }
    return $content;
}

class AiomaticBlockConverter 
{
	public $options = [
		'upload_media' => true,
		'force_https'  => true,
		'auto_p'       => true,
	];

    public $saved_content = '';
	private $raw_content = '';

	private $dom_content = null;

	private $block_content = '';

	private $allowed_tags = [
		// Content Sectioning
		'h1'         => [],
		'h2'         => [],
		'h3'         => [],
		'h4'         => [],
		'h5'         => [],
		'h6'         => [],

		// Text Content
		'blockquote' => [],
		'dd'         => [],
		'dt'         => [],
		'dl'         => [],
		'figcaption' => [],
		'figure'     => [],
		'hr'         => [],
		'li'         => [],
		'ul'         => [
			'type',
		],
		'ol'         => [
			'reversed',
			'start',
			'type',
		],
		'p'          => [],

		// Inline text semantics
		'a'          => [
			'href',
			'target',
			'title',
			'rel',
		],
		'abbr'       => [
			'title',
		],
		'b'          => [],
		'br'         => [],
		'cite'       => [],
		'code'       => [],
		'em'         => [],
		'i'          => [],
		's'          => [],
		'strike'     => [],
		'small'      => [],
		'span'       => [],
		'strong'     => [],
		'sub'        => [],
		'sup'        => [],

		// Image and multimedia
		'audio'      => [
			'src',
			'controls',
			'autoplay',
		],
		'img'        => [
			'src',
			'alt',
			'srcset',
			'height',
			'width',
			'sizes',
		],
		'video'      => [
			'src',
			'controls',
			'autoplay',
			'poster',
		],

		// Embedded content
		'embed'      => [
			'src',
			'height',
			'width',
			'type',
		],
		'iframe'     => [
			'src',
			'height',
			'width',
		],
		'object'     => [],
		'picture'    => [],
		'portal'     => [
			'src',
		],
		'pre'        => [],
		'source'     => [
			'type',
			'src',
			'alt',
			'srcset',
			'height',
			'width',
			'sizes',
			'media',
		],

		// Table content
		'caption'    => [],
		'col'        => [
			'span',
		],
		'colgroup'   => [
			'span',
		],
		'table'      => [],
		'tbody'      => [],
		'td'         => [
			'colspan',
			'headers',
			'rowspan',
		],
		'tfoot'      => [],
		'th'         => [
			'abbr',
			'colspan',
			'headers',
			'rowspan',
		],
		'thead'      => [],
		'tr'         => [],
	];

	private $mapping = [
		'p'          => 'core/paragraph',
		'code'       => [ __CLASS__, 'code' ],
		'pre'        => [ __CLASS__, 'code' ],
		'ul'         => [ __CLASS__, 'list' ],
		'table'      => [ __CLASS__, 'table' ],
		'ol'         => [ __CLASS__, 'ordered_list' ],
		'h1'         => [ __CLASS__, 'heading' ],
		'h2'         => [ __CLASS__, 'heading' ],
		'h3'         => [ __CLASS__, 'heading' ],
		'h4'         => [ __CLASS__, 'heading' ],
		'h5'         => [ __CLASS__, 'heading' ],
		'h6'         => [ __CLASS__, 'heading' ],
		'blockquote' => [ __CLASS__, 'blockquote' ],
		'img'        => [ __CLASS__, 'image' ],
		'hr'         => [ __CLASS__, 'hr' ],
	];

	public function __construct( $options = [] ) {
		$this->options      = wp_parse_args( $options, $this->options );
		$this->mapping      = $this->get_mapping();
		$this->allowed_tags = $this->get_allowed_tags();
	}

	public function convert_blocks( $content = '' ) {
        $this->saved_content = $content;
		$this->load_content( $content );
		$this->convert_to_blocks();

		return $this->render();
	}

	public function load_content( $html ) {
		if ( substr( $html, 0, 1 ) !== '<' ) {
			$html = '<html><body>' . $html . '</body></html>';
		}

		$this->block_content = '';
		$this->raw_content   = $html;
		$this->dom_content   = new DOMDocument();
        $encoded = stripslashes( $html );
		if ( false !== $this->options['auto_p'] ) {
			$encoded = wpautop( $encoded );
		}
		// phpcs:ignore
        
        $internalErrors = libxml_use_internal_errors(true);
		$this->dom_content->loadHTML( '<?xml encoding="UTF-8">' . $this->clean_content( $encoded ),LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        libxml_use_internal_errors($internalErrors);
	}
	public function render() {
		if ( empty( $this->block_content ) ) {
			return $this->raw_content;
		}

		// Remove paragraphs that are wrapping other blocks.
		$content = $this->block_content;
		$content = preg_replace( '/(<!-- wp:paragraph --><p>)(<!--)/i', '$2', $content );
		$content = preg_replace( '/(-->)(<\/p><!-- \/wp:paragraph -->)/i', '$1', $content );

		return $content;
	}

	public function get_mapping() {
		/**
		 * Filter block converter DOM Mapping.
		 *
		 * @param array $mapping Mapping
		 * @return array
		 */
		return apply_filters( 'block_converter_mapping', $this->mapping );
	}

	public function add_dom_mapping( $dom_element_tag, $mapping ) {
		$this->mapping[ $dom_element_tag ] = $mapping;
	}

	public function remove_dom_mapping( $dom_element_tag ) {
		unset( $this->mapping[ $dom_element_tag ] );
	}

	public function set_option( $option, $value ) {
		$this->options[ $option ] = $value;
	}

	public function set_options( $options = [] ) {
		$this->options = wp_parse_args( $options, $this->options );
	}

	public function get_allowed_tags() {
		/**
		 * Filter block converter allowed tags.
		 *
		 * @param array $allowed_tags Allowed tags
		 * @return array
		 */
		return apply_filters( 'block_converter_allowed_tags', $this->allowed_tags );
	}

	public function add_allowed_tag( $tag, $attributes = [] ) {
		$this->allowed_tags[ $tag ] = $attributes;
	}

	protected function clean_attributes( DOMElement $element ) {
		if ( ! $element->hasAttributes() ) {
			return $element;
		}

		// Default allowed attributes.
		$allowed_attributes = [
			'class',
			'id',
		];

		// Allow adding additional attributes at the tag level.
		if ( isset( $element->tagName ) && ! empty( $this->allowed_tags[ $element->tagName ] ) ) {
			$allowed_attributes = array_merge( $allowed_attributes, $this->allowed_tags[ $element->tagName ] );
		}

		for ( $i = $element->attributes->length - 1; $i >= 0; --$i ) {
			if ( ! in_array( $element->attributes->item( $i )->nodeName, $allowed_attributes, true ) ) {
				$element->removeAttributeNode( $element->attributes->item( $i ) );
			}
		}

		return $element;
	}

	private function convert_to_blocks() {
		if ( 0 === $this->dom_content->getElementsByTagName( 'body' )->count() ) {
			return;
		}
		foreach ( $this->dom_content->getElementsByTagName( 'body' )->item( 0 )->childNodes as $element ) {
			$has_tag_mapping = isset( $element->tagName ) && isset( $this->mapping[ $element->tagName ] );

			if ( $has_tag_mapping && ! $this->is_callback( $this->mapping[ $element->tagName ] ) ) {
                $html = trim( $this->get_html( $element ) );
                if ( preg_match( '/^<p>[\s\n]*<\/p>$/i', $html ) !== 1 ) 
                {
                    $this->block_content .= $this->serialize_block(
                        [
                            'blockName' => $this->mapping[ $element->tagName ],
                            'attrs'     => [],
                            'innerHTML' => $this->get_html( $element )
                        ]
                    );
                    $this->block_content .= "\n\n";
                }
			} elseif ( $has_tag_mapping && $this->is_callback( $this->mapping[ $element->tagName ] ) ) {
                $block_me = call_user_func( $this->mapping[ $element->tagName ], $element );
                if(!empty($block_me))
                {
                    $this->block_content .= $this->serialize_block( $block_me );
                    $this->block_content .= "\n\n";
                }
			} elseif ( ! $has_tag_mapping && ! empty( preg_replace( '/[^a-z_\-0-9]/i', '', $element->textContent ) ) && false === strpos( $element->textContent, '[' ) ) {
				$html = trim( $this->get_html( $element ) );
                if ( preg_match( '/^<p>[\s\n]*<\/p>$/i', $html ) !== 1 || empty(trim($html)) ) 
                {
                    $this->block_content .= $this->serialize_block(
                        [
                            'blockName' => 'core/paragraph',
                            'attrs'     => [],
                            'innerHTML' => '<p>' . $html . '</p>'
                        ]
                    );
                    $this->block_content .= "\n\n";
                }
			} elseif ( isset( $element->tagName ) ) {
                $html_me = $this->get_html( $element );
				$this->block_content .= $this->serialize_block(
					[
						'blockName' => 'core/html',
						'attrs'     => [],
						'innerHTML' => $html_me
					]
				);
				$this->block_content .= "\n\n";
			} elseif ( false !== strpos( $element->textContent, '[' ) ) {
				$this->block_content .= $element->textContent;
			}
		}
	}

	protected function clean_content( $content ) {
		$allowed_tags = wp_kses_allowed_html( 'post' );
		$allowed_tags = wp_parse_args( $allowed_tags, $this->allowed_tags );
		return wp_kses( $content, $allowed_tags );
	}

	private function heading( $element ) {
        $html = $this->get_html( $element );
        if ( preg_match( '/^<h[1-9]>[\s\n]*<\/h[1-9]>$/i', $html ) || empty(trim($html)) ) 
        {
            return [];
        }
		return [
			'blockName' => 'core/heading',
			'attrs'     => [
				'level' => (int) preg_replace( '/\D/', '', $element->tagName ),
			],
			'innerHTML' => $this->get_html( $element ),
		];
	}

	private function blockquote( $element ) {
        $html = $this->get_html( $element );
        if ( preg_match( '/^<blockquote>[\s\n]*<\/blockquote>$/i', $html ) || empty(trim($html)) ) 
        {
            return [];
        }
		$element->setAttribute( 'class', 'wp-block-quote' );
		return [
			'blockName' => 'core/quote',
			'attrs'     => [],
			'innerHTML' => $this->get_html( $element ),
		];
	}

	private function list( $element ) {
        $html = $this->get_html( $element );
        if ( preg_match( '/^<ul>[\s\n]*<\/ul>$/i', $html ) || empty(trim($html)) ) 
        {
            return [];
        }
		$inner_blocks = '';
		foreach ( $element->getElementsByTagName( 'li' ) as $node ) {
			$inner_blocks .= $this->serialize_block( $this->list_item( $node ) );
		}

		return [
			'blockName' => 'core/list',
			'innerHTML' => '<ul>' . $inner_blocks . '</ul>',
		];
	}

	private function ordered_list( $element ) {
        $html = $this->get_html( $element );
        if ( preg_match( '/^<ol>[\s\n]*<\/ol>$/i', $html ) || empty(trim($html)) ) 
        {
            return [];
        }
		$inner_blocks = '';
		foreach ( $element->getElementsByTagName( 'li' ) as $node ) {
			$inner_blocks .= $this->serialize_block( $this->list_item( $node ) );
		}

		return [
			'blockName' => 'core/list',
			'attrs'     => [
				'ordered' => true,
			],
			'innerHTML' => '<ol>' . $inner_blocks . '</ol>',
		];
	}

	private function list_item( $element ) {
		return [
			'blockName' => 'core/list-item',
			'attrs'     => [],
			'innerHTML' => $this->get_html( $element ),
		];
	}

	private function table( $element ) {
        $html = $this->get_html( $element );
        if ( preg_match( '/^<table>[\s\n]*<\/table>$/i', $html ) || empty(trim($html)) ) 
        {
            return [];
        }
		return [
			'blockName' => 'core/table',
			'attrs'     => [],
			'innerHTML' => '<figure class="wp-block-table">' . $this->get_html( $element ) . '</figure>',
		];
	}

	private function hr() {
		return [
			'blockName' => 'core/separator',
			'attrs'     => [],
			'innerHTML' => '<hr class="wp-block-separator"/>',
		];
	}

	private function code( $element ) {
        $html = $this->get_html( $element );
        if ( preg_match( '/^<code>[\s\n]*<\/code>$/i', $html ) || empty(trim($html)) ) 
        {
            return [];
        }
		return [
			'blockName' => 'core/code',
			'attrs'     => [
				'content' => $this->get_html( $element ),
			],
			'innerHTML' => '<pre class="wp-block-code">' . $this->get_html( $element ) . '</pre>',
		];
	}

	private function image( $element ) {
		// Got to have a URL.
		$img_src = $element->getAttribute( 'src' );
		if ( empty( $img_src ) ) {
			return;
		}

		$img_title = $element->getAttribute( 'title' );
		$img_alt   = $this->get_image_alt( $element );
		if ( empty( $img_alt ) && ! empty( $img_title ) ) {
			$img_alt = $img_title;
		}

		// If we're uploading media, download the image into the media library.
		// This will return an existing attachment if the slug already exists.
		if ( false !== $this->options['upload_media'] ) {
			$image = $this->upload_image( $img_src );
		} else {
			$image = [
				'ID'  => 1,
				'url' => $img_src,
			];
		}

		// Failed to upload image? Bail.
		if ( empty( $image ) || is_wp_error( $image ) ) {
			return;
		}

		// Get the image URL after upload conditional & maybe force https
		$img_src = $image['url'];
		if ( false !== $this->options['force_https'] ) {
			$img_src = str_ireplace( 'http://', 'https://', $img_src );
		}

		// Create the image markup
		$img_tag = sprintf(
			'<figure class="wp-block-image"><img src="%1$s" alt="%2$s" class="%3$s" /></figure>',
			esc_url( $img_src ),
			trim( esc_attr( $img_alt ) ),
			'wp-image-' . $image['ID']
		);

		return [
			'blockName' => 'core/image',
			'attrs'     => [
				'id'              => $image['ID'],
				'url'             => $img_src,
				'alt'             => $img_alt,
				'title'           => $img_title,
				'sizeSlug'        => 'full',
				'linkDestination' => 'none',
			],
			'innerHTML' => $img_tag,
		];
	}

	protected function get_html( $element ) {
		if ( ! is_a( $element, '\DOMElement' ) ) {
			return $element;
		}

		$inner_html = $element->textContent;
		if ( 0 < $element->childNodes->count() ) {
			$inner_html = $element->ownerDocument->saveHTML( $this->clean_attributes( $element ) );
		}

		return $inner_html;
	}

	private function upload_image( $img_src ) {
		// Attempt to download the image
		$tmp = download_url( $img_src );
		if ( is_wp_error( $tmp ) ) {
			// If the file were still downloaded make sure its deleted.
			if ( file_exists( $tmp ) ) {
				unlink( $tmp ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
			}
			return $tmp;
		}

		// Prepare the file data for sideloading
		$json = [
			'name'     => basename( $img_src ),
			'type'     => mime_content_type( $tmp ),
			'tmp_name' => $tmp,
			'size'     => filesize( $tmp ),
		];

		// Upload the image
		$attachment_id = media_handle_sideload( $json );

		// Delete the temp file if still exists
		if ( file_exists( $tmp ) ) {
			unlink( $tmp ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
		}

		// Return error if attachment upload
		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$img          = [];
		$img['ID']    = absint( $attachment_id );
		$img['url']   = wp_get_attachment_url( $attachment_id );
		$img['sizes'] = wp_get_attachment_metadata( $attachment_id );

		return $img;
	}

	private function get_image_alt( $element, $id = 0 ) {
		// First try to get alt attribute from DOM node.
		$img_alt = $element->getAttribute( 'alt' );

		// If alt attribute is empty, try to get it from the attachment post meta (if post ID is available)
		if ( empty( $img_alt ) && 0 < intval( $id ) ) {
			$img_alt = get_post_meta( $id, '_wp_attachment_image_alt', true ) ?? '';
		}

		return $img_alt;
	}

	private function is_callback( $callback ) {
		if ( is_callable( $callback ) ) {
			return true;
		}

		if ( is_callable( [ __CLASS__, $callback ] ) ) {
			return true;
		}

		return false;
	}

	public function serialize_block( $block ) {
		if ( ! isset( $block['blockName'] ) ) {
			return false;
		}
		$name = $block['blockName'];
		if ( 0 === strpos( $name, 'core/' ) ) {
			$name = substr( $name, strlen( 'core/' ) );
		}
		if ( empty( $block['attrs'] ) ) {
			$opening_tag_suffix = '';
		} else {
			$opening_tag_suffix = ' ' . wp_json_encode( $block['attrs'] );
		}
		if ( empty( $block['innerHTML'] ) ) {
			return sprintf(
				'<!-- wp:%s%s /-->',
				$name,
				$opening_tag_suffix
			);
		} else {
			return sprintf(
				'<!-- wp:%1$s%2$s -->%3$s<!-- /wp:%1$s -->',
				$name,
				$opening_tag_suffix,
				$block['innerHTML']
			);
		}
	}
}
function aiomatic_parseTime($timeString) 
{
    $conversions = [
        'nanosecond' => 0.001,           
        'nanoseconds' => 0.001,
        'microsecond' => 1,              
        'microseconds' => 1,
        'millisecond' => 1000,           
        'milliseconds' => 1000,
        'second' => 1000000,             
        'seconds' => 1000000,
        'minute' => 60000000,            
        'minutes' => 60000000,
        'hour' => 3600000000,           
        'hours' => 3600000000,
        'day' => 86400000000,          
        'days' => 86400000000,
        'week' => 604800000000,         
        'weeks' => 604800000000,
        'fortnight' => 1209600000000,   
        'fortnights' => 1209600000000,
        'month' => 2628000000000,        
        'months' => 2628000000000,
        'quarter' => 7884000000000,      
        'quarters' => 7884000000000,
        'year' => 31557600000000,   
        'years' => 31557600000000,
        'decade' => 315576000000000,   
        'decades' => 315576000000000,
        'ns' => 0.001,                  
        'us' => 1,                      
        'ms' => 1000,                 
        's' => 1000000,                 
        'm' => 60000000,               
        'min' => 60000000,              
        'h' => 3600000000,            
        'hr' => 3600000000,              
        'd' => 86400000000,          
        'w' => 604800000000,            
        'wk' => 604800000000,          
        'mo' => 2628000000000,          
        'q' => 7884000000000,           
        'y' => 31557600000000,          
        'yr' => 31557600000000,          
        'dec' => 315576000000000         
    ];

    $timeString = strtolower(trim($timeString));
    if (is_numeric($timeString)) {
        return (string)(int)round(floatval($timeString) * 1000000); 
    }
    $parts = array_map('trim', explode('and', $timeString));
    $totalMicroseconds = 0;
    foreach ($parts as $part) {
        if (preg_match('/^(\d+\.?\d*)\s*([a-z]+)$/', $part, $matches)) {
            $value = floatval($matches[1]); 
            $unit = trim($matches[2]);   
            if (!isset($conversions[$unit])) {
                throw new InvalidArgumentException("Unsupported time unit: " . esc_html($unit));
            }
            $totalMicroseconds += $value * $conversions[$unit];
        } else {
            throw new InvalidArgumentException("Invalid time format: " . esc_html($part));
        }
    }
    return (string)(int)round($totalMicroseconds);
}
function aiomatic_check_duplicate_or_replace($post, $aiomatic_Main_Settings, $post_type_to_check, &$duplicate_post_id, &$duplicate_post_title, $similarity_threshold, $max_to_process_in_one_go, $dup_titles, $duplication_template, $duplication_index, &$best_similarity) 
{
    if (!$post || !isset($post->ID) || !isset($post->post_title)) {
        return false;
    }
    if($dup_titles === 1)
    {
        global $wpdb;
        $query = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = %s AND ID != %d AND post_status = 'publish'",
            $post->post_title,
            $post_type_to_check,
            $post->ID
        );
        $duplicate_post_id_temp = $wpdb->get_var($query);
        if ($duplicate_post_id_temp) {
            $match_content = get_post_field('post_content', $duplicate_post_id_temp);
            $new_word_count    = str_word_count(strip_tags($post->post_content));
            $match_word_count  = str_word_count(strip_tags($match_content));
            if ($new_word_count > $match_word_count + 100) {
                $duplicate_post_title = get_post_field('post_title', $duplicate_post_id_temp);
                $duplicate_post_id = $duplicate_post_id_temp;
                return "REPLACE_OLD";
            } else {
                $duplicate_post_title = get_post_field('post_title', $duplicate_post_id_temp);
                $duplicate_post_id = $duplicate_post_id_temp;
                return "DUPLICATE_KEEP_OLD";
            }
        }
    }
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    $token = apply_filters('aiomatic_openai_api_key', $token);
    if(empty($duplication_index))
    {
        $duplication_index = '_aiomatic_index';
    }
    if(empty($duplication_template))
    {
        $duplication_template = '%%post_content_plain_text%%';
    }
    $check_template = str_replace('%%post_title%%', $post->post_title, $duplication_template);
    $check_template = str_replace('%%post_content%%', $post->post_content, $check_template);
    $check_template = str_replace('%%post_excerpt%%', $post->post_excerpt, $check_template);
    $check_template = str_replace('%%post_content_plain_text%%', wp_strip_all_tags(strip_shortcodes($post->post_content)), $check_template);
    $check_template = aiomatic_replaceSynergyShortcodes($check_template);
    $new_embedding = get_post_meta($post->ID, $duplication_index, true);
    if (!$new_embedding) 
    {
        $new_embedding = aiomatic_generate_embeddings($check_template, $token);
        if (!$new_embedding) {
            return false;
        }
        else
        {
            if ($new_embedding['status'] == 'error') {
                return false;
            }
            else
            {
                $new_embedding = $new_embedding['data'];
                update_post_meta($post->ID, $duplication_index, $new_embedding);
            }
        }
    }
    $paged = 1; 
    $posts_per_page = 100; 
    $existing_posts = array();

    do {
        $args = array(
            'post_type'      => $post_type_to_check,    
            'posts_per_page' => $posts_per_page,
            'post_status'    => 'publish', 
            'paged'          => $paged, 
            'post__not_in'   => [$post->ID]
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            foreach ($query->posts as $single_post) 
            {
                if ($single_post->ID !== $post->ID) {
                    $existing_posts[] = $single_post;
                }
            }
        }
        $paged++;
        wp_reset_postdata();
    } while ($query->have_posts());

    $best_match_post_id = null;
    $best_similarity = 0.0;
    $processed_now = 0;
    foreach ($existing_posts as $existing_post) 
    {
        $existing_embedding = get_post_meta($existing_post->ID, $duplication_index, true);
        if (!$existing_embedding) 
        {
            if($processed_now >= $max_to_process_in_one_go)
            {
                break;
            }
            $check_template = str_replace('%%post_title%%', $existing_post->post_title, $duplication_template);
            $check_template = str_replace('%%post_content%%', $existing_post->post_content, $check_template);
            $check_template = str_replace('%%post_excerpt%%', $existing_post->post_excerpt, $check_template);
            $check_template = str_replace('%%post_content_plain_text%%', strip_tags(strip_shortcodes($existing_post->post_content)), $check_template);
            $check_template = aiomatic_replaceSynergyShortcodes($check_template);
            $existing_embedding = aiomatic_generate_embeddings($check_template, $token);
            if (!$existing_embedding) {
                break;
            }
            else
            {
                if ($existing_embedding['status'] == 'error') {
                    break;
                }
                else
                {
                    $existing_embedding = $existing_embedding['data'];
                    update_post_meta($existing_post->ID, $duplication_index, $existing_embedding);
                }
            }
            $processed_now++;
        }
        if ($existing_embedding) {
            $similarity = aiomatic_cosine_similarity($new_embedding, $existing_embedding);

            if ($similarity > $best_similarity) {
                $best_similarity    = $similarity;
                $best_match_post_id = $existing_post->ID;
            }
        }
    }
    if ($best_similarity < $similarity_threshold) {
        return "NO_MATCH";
    } else {
        $match_content = get_post_field('post_content', $best_match_post_id);
        $new_word_count    = str_word_count(strip_tags($post->post_content));
        $match_word_count  = str_word_count(strip_tags($match_content));
        if ($new_word_count > $match_word_count + 100) {
            $duplicate_post_title = get_post_field('post_title', $best_match_post_id);
            $duplicate_post_id = $best_match_post_id;
            return "REPLACE_OLD";
        } else {
            $duplicate_post_title = get_post_field('post_title', $best_match_post_id);
            $duplicate_post_id = $best_match_post_id;
            return "DUPLICATE_KEEP_OLD";
        }
    }
}
function aiomatic_cosine_similarity($embedding1, $embedding2)
{
    if (!is_array($embedding1) || !is_array($embedding2)) {
        return 0.0;
    }

    $lenA = count($embedding1);
    $lenB = count($embedding2);
    if ($lenA === 0 || $lenB === 0 || $lenA !== $lenB) {
        return 0.0;
    }

    $dotProduct = 0.0;
    $magnitudeA = 0.0;
    $magnitudeB = 0.0;

    for ($i = 0; $i < $lenA; $i++) {
        $dotProduct += $embedding1[$i] * $embedding2[$i];
        $magnitudeA += ($embedding1[$i] * $embedding1[$i]);
        $magnitudeB += ($embedding2[$i] * $embedding2[$i]);
    }

    $magnitudeA = sqrt($magnitudeA);
    $magnitudeB = sqrt($magnitudeB);

    if ($magnitudeA == 0.0 || $magnitudeB == 0.0) {
        return 0.0;
    }

    return $dotProduct / ($magnitudeA * $magnitudeB);
}
function aiomatic_comment_replier_logic($comment) 
{
    $post = get_post($comment->comment_post_ID);
    if (!$post) {
        return array('success' => false, 'message' => 'Post not found.');
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') {
        return array('success' => false, 'message' => 'You need to enter an OpenAI API key in plugin settings!');
    }
    $prompt = isset($aiomatic_Main_Settings['comment_prompt_auto']) && trim($aiomatic_Main_Settings['comment_prompt_auto']) != ''
        ? trim($aiomatic_Main_Settings['comment_prompt_auto'])
        : 'Write a reply for %%username%%\'s comment on the post titled "%%post_title%%". The user\'s comment is: %%comment%%';
    $prompt = str_replace(
        array('%%post_title%%', '%%username%%', '%%comment%%'),
        array($post->post_title, $comment->comment_author, $comment->comment_content),
        $prompt
    );
    $model = isset($aiomatic_Main_Settings['comment_model_auto']) && trim($aiomatic_Main_Settings['comment_model_auto']) != ''
        ? trim($aiomatic_Main_Settings['comment_model_auto'])
        : aiomatic_get_default_model_name($aiomatic_Main_Settings);
    $all_models = aiomatic_get_all_models(true);
    if (!in_array($model, $all_models)) {
        $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
    }
    if (isset($aiomatic_Main_Settings['comment_assistant_id_auto']) && trim($aiomatic_Main_Settings['comment_assistant_id_auto']) != '') 
    {
        $comment_assistant_id = trim($aiomatic_Main_Settings['comment_assistant_id_auto']);
    }
    else
    {
        $comment_assistant_id = '';
    }
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    $token = apply_filters('aiomatic_openai_api_key', $token);
    $query_token_count = count(aiomatic_encode($prompt));
    $max_tokens = aiomatic_get_max_tokens($model);
    $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
    if ($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS) {
        return array('success' => false, 'message' => 'Insufficient tokens for AI generation.');
    }
    $thread_id = '';
    $aierror = '';
    $finish_reason = '';
    $generated_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, 1, 1, 0, 0, false, 'automaticCommentWriter', 0, $finish_reason, $aierror, false, false, false, '', '', 'user', $comment_assistant_id, $thread_id, '', 'disabled', '', false, false, array());
    if ($generated_text === false) {
        return array('success' => false, 'message' => 'Failed to generate AI content: ' . $aierror);
    }
    return array('success' => true, 'content' => aiomatic_sanitize_ai_result($generated_text));
}

function aiomatic_update_option($option, $value, $autoload = false)
{
    update_option($option, $value, $autoload);
}
function aiomatic_validate_activation()
{
    $plugin = plugin_basename(__FILE__);
    $plugin_slug = explode('/', $plugin);
    $plugin_slug = $plugin_slug[0]; 
    $uoptions = array();
    $is_activated = aiomatic_is_activated($plugin_slug, $uoptions);
    if($is_activated !== true && $is_activated !== 2)
    {
        return false;
    }
    return true;
}
function aiomatic_list_models_huggingface($token, &$error)
{
    $response = '';
    try 
    {
        require_once (dirname(__FILE__) . "/res/huggingface/api.php"); 
        
        $params = [
            'filter' => 'text-generation',
            'limit' => -1, 
            'sort' => 'downloads',
            'direction' => -1
        ];
        $env = [
            'apikey' => $token
        ];
        $sdk = new AiomaticHuggingFaceSDK($env);
        $response = $sdk->list_models($params);
    } 
    catch (Exception $e) 
    {
        $error = 'HuggingFaceb failure: ' . $e->getMessage();
        return false;
    }
    return $response;
}
function aiomatic_get_openrouter_models()
{
	$categories_option_value = get_option('aiomatic_openrouter_model_list', array());
	if(isset($categories_option_value['source_list']) && isset($categories_option_value['last_updated']))
	{
		if( (time() - $categories_option_value['last_updated']) < 2986400 )
		{
			return $categories_option_value;
		}
	}
	$categories = aiomatic_openrouter_retrieve_models();
	if(is_array($categories))
	{
		return $categories;
	}
	return false;
}
function aiomatic_get_huggingface_models()
{
	$huggingface_models = get_option('aiomatic_huggingface_models', array());
    $hf_arr = array();
    foreach($huggingface_models as $model => $details)
    {
        if(!in_array($model, $hf_arr))
        {
            $hf_arr[] = $model;
        }
    }
	return $hf_arr;
}
function aiomatic_fetch_available_language_packs($url) 
{
    $transient_key = 'aiomatic_language_packs';
    $cached_data = get_transient($transient_key);
    if ($cached_data !== false) {
        return $cached_data; 
    }
    $response = wp_remote_get($url . 'index.json');
    if (is_wp_error($response)) {
        return [];
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!$data) {
        return [];
    }
    set_transient($transient_key, $data, 12 * HOUR_IN_SECONDS);
    return $data;
}

function aiomatic_get_installed_language_packs($dir) {
    $files = scandir($dir);
    $installed_packs = [];
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
       wp_filesystem($creds);
    }
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'mo') {
            $base_name = pathinfo($file, PATHINFO_FILENAME);
            $po_file = $dir . '/' . $base_name . '.po';
            $installed_packs[$base_name] = $wp_filesystem->exists($po_file);
        }
    }
    return $installed_packs;
}
function aiomatic_generate_chart_image(WP_REST_Request $request)
{
    $params = $request->get_query_params();
    if (empty($params)) 
    {
        $params = $request->get_json_params();
    }
    $token = sanitize_text_field($params['nonce'] ?? '');
    if(isset($params['nonce']))
    {
        unset($params['nonce']);
    }
    $expected_token = hash_hmac('sha256', json_encode($params), '!FF6384,36A2EB,FFCE56xxxcreateyourownkeyxxx');
    if (!hash_equals($expected_token, $token)) 
    {
        return new WP_Error('invalid_token', 'Invalid token', ['status' => 403]);
    }
    $testGD = get_extension_funcs("gd");
    if (!$testGD)
    { 
        return new WP_Error('required_extension_missing', 'GD not even installed.', ['status' => 403]);
    }
    $type = sanitize_text_field($params['type'] ?? 'bar');
    $title = sanitize_text_field($params['title'] ?? ''); 
    $labels = explode(',', sanitize_text_field($params['labels'] ?? 'Label1,Label2,Label3'));
    $data = array_map('intval', explode(',', sanitize_text_field($params['data'] ?? '10,20,30')));
    $colors = explode(',', sanitize_text_field($params['colors'] ?? '#FF6384,#36A2EB,#FFCE56'));
    $titleHeight = $title ? 30 : 0; 
    $chartHeight = 378; 
    $detailHeight = 100; 
    $width = 637;
    $height = $titleHeight + $chartHeight + $detailHeight;
    $padding = 50;
    $image = imagecreatetruecolor($width, $height);
    $backgroundColor = imagecolorallocate($image, 255, 255, 255);
    imagefill($image, 0, 0, $backgroundColor);
    if (!empty($title)) {
        $titleFont = 5; 
        $titleWidth = intval(strlen($title) * imagefontwidth($titleFont)); 
        $titleX = intval(($width - $titleWidth) / 2); 
        $titleY = 5;
        imagestring($image, $titleFont, $titleX, $titleY, $title, imagecolorallocate($image, 0, 0, 0));
    }
    $chartYStart = $titleHeight;
    $chartYEnd = $chartYStart + $chartHeight;
    $allocatedColors = [];
    foreach ($colors as $color) {
        $rgb = sscanf(ltrim($color, '#'), "%02x%02x%02x");
        $allocatedColors[] = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
    }
    $maxValue = max($data);
    switch ($type) {
        case 'bar':
            $barWidth = intval(($width - 2 * $padding) / count($data) - 10); 
            foreach ($data as $i => $value) {
                $x1 = intval($padding + $i * ($barWidth + 10));
                $x2 = intval($x1 + $barWidth);
                $y1 = intval($chartYEnd - $padding);
                $y2 = intval($y1 - ($value / $maxValue) * ($chartHeight - 2 * $padding));
                imagefilledrectangle($image, $x1, $y1, $x2, $y2, $allocatedColors[$i % count($allocatedColors)]);
                $label = $labels[$i] ?? '';
                $labelWidth = strlen($label) * imagefontwidth(3);
                $labelX = intval(($x1 + $x2) / 2 - $labelWidth / 2); 
                $labelY = intval($y1 + 5); 
                imagestring($image, 3, $labelX, $labelY, $label, imagecolorallocate($image, 0, 0, 0));
            }
            break;
        case 'line':
            $prevX = $prevY = null;
            foreach ($data as $i => $value) {
                $x = intval($padding + $i * (($width - 2 * $padding) / (count($data) - 1)));
                $y = intval($chartYEnd - $padding - ($value / $maxValue) * ($chartHeight - 2 * $padding));
                if ($prevX !== null && $prevY !== null) {
                    imageline($image, $prevX, $prevY, $x, $y, $allocatedColors[$i % count($allocatedColors)]);
                }
                $prevX = $x;
                $prevY = $y;
                $pointColor = $allocatedColors[$i % count($allocatedColors)];
                imagefilledellipse($image, $x, $y, 8, 8, $pointColor);
                imagestring($image, 3, $x + 5, $y - 15, $labels[$i] ?? '', imagecolorallocate($image, 0, 0, 0));
            }
            break;
        case 'pie':
            $total = array_sum($data);
            $startAngle = 0;
            $cx = intval($width / 2);
            $cy = intval($chartYStart + $chartHeight / 2);
            $radius = intval(min($width, $chartHeight) / 3);
            foreach ($data as $i => $value) {
                $sliceAngle = intval(($value / $total) * 360);
                $endAngle = intval($startAngle + $sliceAngle);
                imagefilledarc($image, $cx, $cy, $radius * 2, $radius * 2, $startAngle, $endAngle, $allocatedColors[$i % count($allocatedColors)], IMG_ARC_PIE);
                $midAngle = deg2rad(($startAngle + $endAngle) / 2);
                $labelX = intval($cx + cos($midAngle) * ($radius * 0.6)); 
                $labelY = intval($cy + sin($midAngle) * ($radius * 0.6));
                $label = $labels[$i] ?? '';
                $labelColor = imagecolorallocate($image, 0, 0, 0); 
                imagestring($image, 3, $labelX - (strlen($label) * 3), $labelY - 7, $label, $labelColor);
                $startAngle = $endAngle;
            }
            break;
        default:
            imagestring($image, 5, $width / 2 - 50, $chartYStart + $chartHeight / 2, "Invalid Chart Type", imagecolorallocate($image, 255, 0, 0));
            break;
    }
    $detailY = $chartYEnd + 10;
    foreach ($data as $i => $value) {
        $detailX = intval($padding + $i * (($width - 2 * $padding) / count($data)));
        $colorBoxSize = 10;
        imagefilledrectangle($image, $detailX, $detailY, $detailX + $colorBoxSize, $detailY + $colorBoxSize, $allocatedColors[$i % count($allocatedColors)]);
        $detailText = "{$labels[$i]}: {$value}";
        imagestring($image, 3, $detailX + $colorBoxSize + 5, $detailY, $detailText, imagecolorallocate($image, 0, 0, 0));
    }
    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
    exit;
}
function aiomatic_get_installed_language_packs_meta() {
    $installed_packs = get_option('aiomatic_installed_language_packs', []);
    return is_array($installed_packs) ? $installed_packs : [];
}

function aiomatic_update_installed_language_packs_meta($language_pack, $action = 'add') {
    $installed_packs = aiomatic_get_installed_language_packs_meta();
    if ($action === 'add' && !in_array($language_pack, $installed_packs)) {
        $installed_packs[] = $language_pack;
    } elseif ($action === 'remove') {
        $installed_packs = array_diff($installed_packs, [$language_pack]);
    }
    aiomatic_update_option('aiomatic_installed_language_packs', $installed_packs, false);
}
function aiomatic_copy_language_pack($pack, $language_pack_url, $local_language_dir) {
    global $wp_filesystem;
    if (!is_a($wp_filesystem, 'WP_Filesystem_Base')) {
        include_once(ABSPATH . 'wp-admin/includes/file.php');
        $creds = request_filesystem_credentials(site_url());
        wp_filesystem($creds);
    }
    $remote_mo = $language_pack_url . $pack . '.mo';
    $remote_po = $language_pack_url . $pack . '.po';
    $local_mo = $local_language_dir . '/' . $pack . '.mo';
    $local_po = $local_language_dir . '/' . $pack . '.po';
    if (!$wp_filesystem->exists($local_mo)) {
        $wp_filesystem->copy($remote_mo, $local_mo, true);
    }
    if (!$wp_filesystem->exists($local_po)) {
        $wp_filesystem->copy($remote_po, $local_po, true);
    }
}
function aiomatic_openrouter_retrieve_models() 
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(isset($aiomatic_Main_Settings['app_id_openrouter']) && !empty(trim($aiomatic_Main_Settings['app_id_openrouter'])))
    {
        $url = 'https://openrouter.ai/api/v1/models';
        $response = wp_remote_get( $url );
        if ( is_wp_error( $response ) ) 
        {
            throw new Exception( 'Failed to query OpenRouter API: ' . esc_html($response->get_error_message()) );
        }
        $body = json_decode( $response['body'], true );
        if ( $body === null ) 
        {
            throw new Exception( 'Failed to decode response: ' . esc_html($response['body']) );
        }
        $models = array();
        foreach ( $body['data'] as $model ) 
        {
            $family = "n/a";
            $maxCompletionTokens = 4096;
            $maxContextualTokens = 8096;
            $priceIn = 0;
            $priceOut = 0;
            $family = explode( '/', $model['id'] )[0];
            if ( isset( $model['top_provider']['max_completion_tokens'] ) ) 
            {
                $maxCompletionTokens = (int)$model['top_provider']['max_completion_tokens'];
            }
            if ( isset( $model['context_length'] ) ) 
            {
                $maxContextualTokens = (int)$model['context_length'];
            }
            if ( isset( $model['pricing']['prompt'] ) && $model['pricing']['prompt'] > 0 ) 
            {
                $priceIn = floatval( $model['pricing']['prompt'] ) * 1000;
                $priceIn = aiomatic_truncate_float( $priceIn );
            }
            if ( isset( $model['pricing']['completion'] ) && $model['pricing']['completion'] > 0 ) 
            {
                $priceOut = floatval( $model['pricing']['completion'] ) * 1000;
                $priceOut = aiomatic_truncate_float( $priceOut );
            }

            $tags = [ 'ai' ];
            if ( preg_match( '/\((beta|alpha|preview)\)/i', $model['name'], $matches ) ) 
            {
                $tags[] = 'preview';
                $model['name'] = preg_replace( '/\((beta|alpha|preview)\)/i', '', $model['name'] );
            }
            if ( preg_match( '/vision/i', $model['name'], $matches ) ) 
            {
                $tags[] = 'vision';
            }
            $models[] = array(
                'model' => $model['id'],
                'name' => trim( $model['name'] ),
                'family' => $family,
                'mode' => 'chat',
                'price' => array(
                    'in' => $priceIn,
                    'out' => $priceOut,
                ),
                'type' => 'token',
                'unit' => 1 / 1000,
                'maxCompletionTokens' => $maxCompletionTokens,
                'maxContextualTokens' => $maxContextualTokens,
                'tags' => $tags
            );
        }
        $ai_ret_models = array(
            'source_list' => $models,
            'last_updated' => time()
        );
        if(count($models) > 0)
        {
            aiomatic_update_option('aiomatic_openrouter_model_list', $ai_ret_models, false);
        }
        return $ai_ret_models;
    }
    return false;
}
function aiomatic_generate_site_secret() 
{
    $site_url = home_url();
    $salt = 'asfjklasjssadedcccc'; 
    $secret = hash('sha256', $site_url . $salt);
    $secret = substr($secret, 0, 6);
    return $secret;
}
function aiomatic_is_demo_server()
{
    $demo_server_ip = '143.198.112.144';
    $demo_server_url = '143.198.112.144';
    if(isset($_SERVER['SERVER_ADDR']))
    {
        $current_server_ip = $_SERVER['SERVER_ADDR'];
    }
    else
    {
        $current_server_ip = '';
    }
    if(isset($_SERVER['SERVER_NAME']))
    {
        $current_server_name = $_SERVER['SERVER_NAME'];
    }
    else
    {
        $current_server_name = 'not_demo';
    }
    if ($current_server_ip === $demo_server_ip && strpos($current_server_name, $demo_server_url) !== false) 
    {
        return true;
    }
    return false;
}
function aiomatic_reconstruct_post_content($blocks) 
{
    $content = '';
    foreach ($blocks as $block) 
    {
        if (!isset($block['blockName']) || empty($block['blockName'])) 
        {
            $content .= $block['innerHTML'];
            continue;
        }
        $content .= '<!-- wp:' . $block['blockName'];
        if (!empty($block['attrs'])) 
        {
            $content .= ' ' . json_encode($block['attrs']);
        }
        $content .= ' -->';
        $content .= $block['innerHTML'];
        if (!empty($block['innerBlocks'])) 
        {
            $content .= aiomatic_reconstruct_post_content($block['innerBlocks']);
        }
        $content .= '<!-- /wp:' . $block['blockName'] . ' -->';
    }
    return $content;
}
function aiomatic_validate_workflows($workflows) 
{
    $errors = [];
    $ids = [];

    foreach ($workflows as $index => $workflow) {
        $workflowLabel = isset($workflow['id']) 
            ? sprintf('%s "%s"', aiomatic_workflow_builder_data('workflow_val'), $workflow['id'])
            : sprintf('%s #%d', aiomatic_workflow_builder_data('workflow_val'), $index + 1);
        if (empty($workflow['id'])) {
            $errors[] = $workflowLabel . ': ' . aiomatic_workflow_builder_data('missing_id');
        } else {
            if (in_array($workflow['id'], $ids)) {
                $errors[] = str_replace('{id}', $workflow['id'], aiomatic_workflow_builder_data('duplicate_id'));
            } else {
                $ids[] = $workflow['id'];
            }
        }
        if (!isset($workflow['max_repeat_count']) || $workflow['max_repeat_count'] === '') {
            $errors[] = $workflowLabel . ': ' . aiomatic_workflow_builder_data('missing_max_repeat_count');
        }
        if (!isset($workflow['priority']) || $workflow['priority'] === '') {
            $errors[] = $workflowLabel . ': ' . aiomatic_workflow_builder_data('missing_priority');
        }
        if (empty($workflow['triggers'])) {
            $errors[] = $workflowLabel . ': ' . aiomatic_workflow_builder_data('missing_triggers');
        } else {
            if (empty($workflow['triggers']['operator'])) {
                $errors[] = $workflowLabel . ': ' . aiomatic_workflow_builder_data('missing_operator');
            }
            if (empty($workflow['triggers']['conditions'])) {
                $errors[] = $workflowLabel . ': ' . aiomatic_workflow_builder_data('no_conditions');
            } else {
                foreach ($workflow['triggers']['conditions'] as $condIndex => $condition) {
                    $conditionLabel = aiomatic_workflow_builder_data('condition') . ' ' . ($condIndex + 1);
                    if (empty($condition['type'])) {
                        $errors[] = $workflowLabel . ', ' . $conditionLabel . ': ' . aiomatic_workflow_builder_data('missing_condition_type');
                    }
                    if (empty($condition['value']) && !in_array($condition['type'], ['user_logged_in', 'user_not_logged_in'])) {
                        $errors[] = $workflowLabel . ', ' . $conditionLabel . ': ' . aiomatic_workflow_builder_data('missing_condition_value');
                    }
                    switch ($condition['type']) {
                        case 'message_contains':
                            break;
                        case 'message_not_contains':
                            break;
                        case 'message_matches_regex':
                            break;
                        case 'message_not_matches_regex':
                            break;
                        case 'message_is':
                            break;
                        case 'any_message_contains':
                            break;
                        case 'previous_message_is':
                            break;
                        case 'nth_message':
                            break;
                        case 'nth_or_larger_message':
                            break;
                        case 'nth_or_smaller_message':
                            break;
                        case 'on_post_id':
                            break;
                        case 'on_post_type':
                            break;
                        case 'on_post_category_id':
                            break;
                        case 'user_logged_in':
                            break;
                        case 'user_not_logged_in':
                            break;
                        case 'user_role_is':
                            break;
                        case 'user_role_is_not':
                            break;
                        case 'user_name_is':
                            break;
                        case 'user_name_is_not':
                            break;
                        case 'message_starts_with':
                            break;
                        case 'message_ends_with':
                            break;
                        case 'message_length_is':
                            break;
                        case 'day_of_week':
                            break;
                        case 'time_of_day':
                            break;
                        case 'specific_date':
                            break;
                        default:
                            $errors[] = str_replace('{type}', $condition['type'], aiomatic_workflow_builder_data('invalid_condition_type'));
                            break;
                    }
                }
            }
        }
        if (empty($workflow['actions'])) {
            $errors[] = $workflowLabel . ': ' . aiomatic_workflow_builder_data('no_actions');
        } else {
            foreach ($workflow['actions'] as $actionIndex => $action) {
                $actionLabel = aiomatic_workflow_builder_data('action') . ' ' . ($actionIndex + 1);
                if (empty($action['type'])) {
                    $errors[] = $workflowLabel . ', ' . $actionLabel . ': ' . aiomatic_workflow_builder_data('missing_action_type');
                } else {
                    switch ($action['type']) {
                        case 'send_message':
                            if (empty($action['message'])) {
                                $errors[] = $workflowLabel . ', ' . $actionLabel . ' (' . aiomatic_workflow_builder_data('send_message') . '): ' . aiomatic_workflow_builder_data('missing_message');
                            }
                            break;
                        case 'append_to_prompt':
                            if (empty($action['value'])) {
                                $errors[] = $workflowLabel . ', ' . $actionLabel . ' (' . aiomatic_workflow_builder_data('append_prompt') . '): ' . aiomatic_workflow_builder_data('missing_value');
                            }
                            break;
                        case 'redirect_user':
                            if (empty($action['value'])) {
                                $errors[] = $workflowLabel . ', ' . $actionLabel . ' (' . aiomatic_workflow_builder_data('redirect_user') . '): ' . aiomatic_workflow_builder_data('missing_value');
                            }
                            break;
                        case 'call_webhook':
                            if (empty($action['value'])) {
                                $errors[] = $workflowLabel . ', ' . $actionLabel . ' (' . aiomatic_workflow_builder_data('call_webhook') . '): ' . aiomatic_workflow_builder_data('missing_value');
                            }
                            break;
                        case 'jump_to_workflow':
                            if (empty($action['workflow_id'])) {
                                $errors[] = $workflowLabel . ', ' . $actionLabel . ' (' . aiomatic_workflow_builder_data('jump_to_workflow') . '): ' . aiomatic_workflow_builder_data('missing_workflow_id');
                            }
                            break;
                        case 'end_conversation':
                            break;
                        default:
                            $errors[] = str_replace('{type}', $action['type'], aiomatic_workflow_builder_data('invalid_action_type'));
                            break;
                    }
                }
            }
        }
    }
    return $errors;
}
function aiomatic_workflow_builder_data($key) {
    $data = [
        'workflow_val' => 'Workflow',
        'toggle_details' => 'Show Details ▼',
        'missing_id' => 'Missing ID',
        'duplicate_id' => 'Duplicate ID: {id}',
        'missing_max_repeat_count' => 'Missing max_repeat_count',
        'missing_priority' => 'Missing priority',
        'missing_triggers' => 'Missing triggers',
        'missing_operator' => 'Missing operator',
        'no_conditions' => 'No conditions defined',
        'condition' => 'Condition',
        'missing_condition_type' => 'Missing condition type',
        'missing_condition_value' => 'Missing condition value',
        'no_actions' => 'No actions defined',
        'action' => 'Action',
        'missing_action_type' => 'Missing action type',
        'send_message' => 'Send Message',
        'missing_message' => 'Missing message',
        'append_prompt' => 'Append to Prompt',
        'missing_value' => 'Missing value',
        'jump_to_workflow' => 'Jump to Workflow',
        'missing_workflow_id' => 'Missing workflow ID',
        'invalid_action_type' => 'Invalid action type: {type}',
        'invalid_condition_type' => 'Invalid condition type: {type}'
    ];
    return $data[$key] ?? '';
}
function aiomatic_compareIds($id1, $id2) 
{
    if ($id1 === null || $id1 === '') return -1;
    if ($id2 === null || $id2 === '') return 1;

    $id1 = (string)$id1;
    $id2 = (string)$id2;

    if (ctype_digit($id1) && ctype_digit($id2)) {
        return (int)$id1 <=> (int)$id2;
    }

    return strnatcmp($id1, $id2);
}
function aiomatic_is_activated($plugin_slug, &$uoptions)
{
    $uoptions = array(
'item_id' => '38877369',
'item_name' => 'Aiomatic - Automatic AI Content Writer',
'created_at' => date('Y-m-d'),
'buyer' => 'GPL',
'licence' => 'Custom License',
'supported_until' => '2099-12-12',
'code' => '12345678-1234-1234-1234-123456789012'
);
return true;
    if (aiomatic_is_demo_server()) 
    {
        return 2;
    }
    $blacklisted_purchase_codes = array();
    if (is_multisite()) 
    {
        $main_site_id = get_network()->site_id;
        switch_to_blog($main_site_id);
        $uoptions = get_option($plugin_slug . '_registration', array());
        restore_current_blog();
    } 
    else 
    {
        $uoptions = get_option($plugin_slug . '_registration', array());
    }
    if(isset($uoptions['item_id']) && isset($uoptions['item_name']) && isset($uoptions['created_at']) && isset($uoptions['buyer']) && isset($uoptions['licence']) && isset($uoptions['supported_until']))
    {
        if($uoptions['item_id'] == '19200046' || $uoptions['item_id'] == '38877369' || $uoptions['item_id'] == '13371337')
        {
            if(strstr($uoptions['item_name'], 'Mega') !== false || strstr($uoptions['item_name'], 'Item') !== false || stristr($uoptions['item_name'], 'Aiomatic') !== false)
            {
                if($uoptions['created_at'] === '24.12.1974' || $uoptions['created_at'] === '10.10.2020' || $uoptions['supported_until'] === '10.10.2030')
                {
                    return -1;
                }
                $supported_until = strtotime($uoptions['supported_until']);
                $created_at = strtotime($uoptions['created_at']);
                if(($created_at !== false || $uoptions['created_at'] === 'NA') && $supported_until !== false)
                {
                    if($created_at !== false)
                    {
                        $mintime = strtotime('19.12.2016');
                        if($created_at < $mintime)
                        {
                            return -1;
                        }
                    }
                    $yourtime = strtotime('+1 year +1 day');
                    if ($supported_until > $yourtime) 
                    {
                        return -1;
                    }
                    $username_pattern = '/^[a-zA-Z0-9\s_-]+$/';
                    if (!preg_match($username_pattern, $uoptions['buyer'])) 
                    {
                        return -1;
                    }
                    if($uoptions['licence'] === 'Regular License' || $uoptions['licence'] === 'Extended License' || $uoptions['licence'] === 'Custom License')
                    {
                        if(in_array($uoptions['code'], $blacklisted_purchase_codes))
                        {
                            return -1;
                        }
                        $pattern = '/^[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}$/';
                        if(preg_match($pattern, $uoptions['code']))
                        {
                            return true;
                        }
                        else
                        {
                            return -1;
                        }
                    }
                    else
                    {
                        return -1;
                    }
                }
                else
                {
                    return -1;
                }
            }
            else
            {
                return -1;
            }
        }
        else
        {
            return -1;
        }
    }
    return false;
}
function aiomatic_generateUniqueIdFromArray($atts) 
{
    ksort($atts);
    $serialized = json_encode($atts);
    $uniqueId = hash('sha256', $serialized);
    return $uniqueId;
}
function aiomatic_truncate_content($content, $max_length = 16777215) 
{
    if (extension_loaded('mbstring')) 
    {
        if (mb_strlen($content, 'UTF-8') > $max_length) {
            $content = mb_substr($content, 0, $max_length, 'UTF-8');
        }
    }
    else
    {
        $characters = preg_split('//u', $content, -1, PREG_SPLIT_NO_EMPTY);
        if (count($characters) > $max_length) {
            $content = implode('', array_slice($characters, 0, $max_length));
        }
    }
    return $content;
}
function aiomatic_truncate_title($title, $max_length = 255) 
{
    if (function_exists('mb_convert_encoding')) {
        $title = mb_convert_encoding($title, 'UTF-8', 'auto');
    }
    if (extension_loaded('mbstring')) 
    {
        if (mb_strlen($title, 'UTF-8') > $max_length) {
            $title = mb_substr($title, 0, $max_length, 'UTF-8');
        }
    }
    else
    {
        $characters = preg_split('//u', $title, -1, PREG_SPLIT_NO_EMPTY);
        if (count($characters) > $max_length) {
            $title = implode('', array_slice($characters, 0, $max_length));
        }
    }
	if (function_exists ( 'iconv' )) 
    {
        $converted_title = iconv('utf-8', 'utf-8//IGNORE', $title);
        if ($converted_title !== false && $converted_title !== '') 
        {
            return $converted_title;
        }
    }
    return $title;
}
function aiomatic_get_date_group($timestamp)
{
    $today_start = strtotime('today midnight');
    $yesterday_start = strtotime('yesterday midnight');
    $week_ago = strtotime('-7 days', $today_start);
    $month_ago = strtotime('-30 days', $today_start);

    if ($timestamp >= $today_start) {
        return array('label' => 'Today', 'order' => 1);
    } elseif ($timestamp >= $yesterday_start) {
        return array('label' => 'Yesterday', 'order' => 2);
    } elseif ($timestamp >= $week_ago) {
        return array('label' => 'Previous 7 Days', 'order' => 3);
    } elseif ($timestamp >= $month_ago) {
        return array('label' => 'Previous 30 Days', 'order' => 4);
    } else {
        $month_name = date('F Y', $timestamp);
        $group_order = 1000000000 - $timestamp;
        return array('label' => $month_name, 'order' => $group_order);
    }
}
function aiomatic_generate_conversation_title($x_input_text, $max_length = 50) 
{
    $dom = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML($x_input_text, LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_use_internal_errors($internalErrors);
    $text_content = [];
    foreach ($dom->getElementsByTagName('div') as $div) 
    {
        if ($div->hasAttribute('class') && strpos($div->getAttribute('class'), 'ai-bubble') !== false) 
        {
            $text = trim($div->textContent);
            if (!empty($text)) 
            {
                $text_content[] = $text; 
            }
        }
    }
    $text_content = implode(' ', $text_content);
    $text_content = preg_replace('/\s+/', ' ', $text_content);
    $text_content = trim($text_content);
    $sentences = preg_split('/(\.|\?|!)/', $text_content, -1, PREG_SPLIT_DELIM_CAPTURE);
    $first_sentence = isset($sentences[0]) ? trim($sentences[0]) : '';
    $additional = 1;
    while (strlen($first_sentence) < 10 && isset($sentences[$additional])) 
    {
        if(trim($sentences[$additional]) == '.' || trim($sentences[$additional]) == '!' || trim($sentences[$additional]) == '?')
        {
            $first_sentence .= trim($sentences[$additional]);
        }
        else
        {
            $first_sentence .= ' ' . trim($sentences[$additional]);
        }
        $additional = $additional + 1;
    }
    if (strlen($first_sentence) > $max_length) 
    {
        $first_sentence = substr($first_sentence, 0, $max_length) . '...';
    }
    $title = $first_sentence ?: 'Untitled Conversation ' . uniqid();
    return $title;
}
function aiomatic_get_gutenberg_block_list()
{
    $registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
    $blocks_list = [];
    foreach ($registered_blocks as $block_name => $block_object) {
        $blocks_list[$block_name . '~' . (isset($block_object->title) ? $block_object->title : $block_name)] = $block_name;
    }
    $textual_blocks = [
        'core/paragraph',
        'core/heading',
        'core/list',
        'core/quote',
        'core/code',
        'core/preformatted',
        'core/pullquote',
        'core/table',
        'core/verse',
    ];
    uasort($blocks_list, function ($a, $b) use ($textual_blocks) 
    {
        if ($a === 'core/paragraph') {
            return -1;
        }
        if ($b === 'core/paragraph') {
            return 1;
        }
        $is_a_core = strpos($a, 'core/') === 0;
        $is_b_core = strpos($b, 'core/') === 0;
        $is_a_textual = in_array($a, $textual_blocks);
        $is_b_textual = in_array($b, $textual_blocks);
        if ($is_a_textual && !$is_b_textual) {
            return -1;
        }
        if ($is_b_textual && !$is_a_textual) {
            return 1;
        }
        if ($is_a_core && !$is_b_core) {
            return -1;
        }
        if ($is_b_core && !$is_a_core) {
            return 1;
        }
        return strcmp($a, $b);
    });  
    return $blocks_list;
}
function aiomatic_insert_ai_content($post_content, $ai_content) 
{
    if(empty($ai_content))
    {
        return $post_content;
    }
    if(empty($post_content))
    {
        return $ai_content;
    }
    $post_content_unchanged = $post_content;
    if (has_blocks($post_content)) 
    {
        $blocks = parse_blocks($post_content);
        $gutenberg_block_types = aiomatic_get_gutenberg_block_list();
        $insertion_index = null;
        foreach ($blocks as $index => $block) 
        {
            if (in_array($block['blockName'], $gutenberg_block_types)) {
                $insertion_index = intval(count($blocks) / 2);
                break;
            }
        }
        $ai_block = [
            'blockName' => 'core/paragraph',
            'attrs' => [],
            'innerHTML' => $ai_content,
            'innerContent' => [$ai_content],
        ];
        if ($insertion_index !== null) 
        {
            array_splice($blocks, $insertion_index + 1, 0, [$ai_block]);
        } 
        else 
        {
            $blocks[] = $ai_block;
        }
        $post_content = '';
        foreach ($blocks as $block) 
        {
            if(!isset($block['blockName']) || empty($block['blockName']))
            {
                $post_content .= $block['innerHTML'];
            }
            else
            {
                $post_content .= '<!-- wp:' . $block['blockName'];
                if (!empty($block['attrs'])) {
                    $post_content .= ' ' . json_encode($block['attrs']);
                }
                $post_content .= ' -->';
                $post_content .= render_block( $block );
                $post_content .= '<!-- /wp:' . $block['blockName'] . ' -->';
            }
        }
    } 
    else 
    {
        $insertion_tags = ['p', 'div', 'article', 'section', 'figure', 'img', 'iframe'];
        $html_found = false;
        foreach($insertion_tags as $it)
        {
            if(strstr($post_content, '<' . $it) !== false)
            {
                $html_found = true;
                break;
            }
        }
        if($html_found === true)
        {
            $dom = new DOMDocument();
            $internalErrors = libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' .  $post_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_use_internal_errors($internalErrors);
            $xpath = new DOMXPath($dom);
            $insertion_point = null;
            foreach ($insertion_tags as $tag) 
            {
                $nodes = $xpath->query("//" . $tag . "[not(ancestor::h1) and not(ancestor::h2) and not(ancestor::h3) and not(ancestor::h4) and not(ancestor::h5) and not(ancestor::a)]");
                if ($nodes->length > 0) {
                    $insertion_point = $nodes->item(intval($nodes->length / 2));
                    break;
                }
            }
            if ($insertion_point === null) 
            {
                $insertion_point = $dom->documentElement;
            }
            if ($insertion_point !== null && strpos($post_content, $ai_content) === false) 
            {
                $fragment = $dom->createDocumentFragment();
                $fragment->appendXML($ai_content);
                if ($insertion_point->parentNode !== null) {
                    $insertion_point->parentNode->insertBefore($fragment, $insertion_point->nextSibling);
                } else {
                    $insertion_point->insertBefore($fragment, $insertion_point->nextSibling);
                }
                $post_content = $dom->saveHTML();
                $pprefix = '<?xml encoding="utf-8" ?>';
                if (substr($post_content, 0, strlen($pprefix)) == $pprefix) {
                    $post_content = substr($post_content, strlen($pprefix));
                } 
            }
        }
        else
        {
            $delimiter = '. ';
            if (strpos($post_content, "\n") !== false) 
            {
                $delimiter = "\n";
            }
            $sentences = explode($delimiter, $post_content);
            $middle_index = intval(count($sentences) / 2);
            $modified_content = array_merge(
                array_slice($sentences, 0, $middle_index + 1),
                [$ai_content],
                array_slice($sentences, $middle_index + 1)
            );
            $post_content = implode($delimiter, $modified_content);
        }
    }
    if ($post_content_unchanged == $post_content) 
    {
        $post_content .= '<br/>' . $ai_content;
    }
    return $post_content;
}
function aiomatic_insert_ai_content_old($post_content, $ai_content) 
{
    $post_content_unchanged = $post_content;
    $dom = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML($post_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_use_internal_errors($internalErrors);
    $xpath = new DOMXPath($dom);
    $insertion_tags = ['p', 'div', 'article', 'section', 'figure', 'blockquote'];
    $gutenberg_blocks = [
        'paragraph' => ['start' => '<!-- wp:paragraph -->', 'end' => '<!-- /wp:paragraph -->'],
        'heading' => ['start' => '<!-- wp:heading -->', 'end' => '<!-- /wp:heading -->'],
        'image' => ['start' => '<!-- wp:image -->', 'end' => '<!-- /wp:image -->'],
        'video' => ['start' => '<!-- wp:video -->', 'end' => '<!-- /wp:video -->'],
        'quote' => ['start' => '<!-- wp:quote -->', 'end' => '<!-- /wp:quote -->'],
        'list' => ['start' => '<!-- wp:list -->', 'end' => '<!-- /wp:list -->']
    ];
    $insertion_point = null;
    foreach ($gutenberg_blocks as $block) 
    {
        $block_start = $block['start'];
        $block_end = $block['end'];
        if (strpos($post_content, $block_start) !== false && strpos($post_content, $block_end) !== false) 
        {
            $gutenberg_blocks_parts = explode($block_start, $post_content);
            $middle_index = intval(count($gutenberg_blocks_parts) / 2);
            if(isset($gutenberg_blocks_parts[$middle_index]))
            {
                $gutenberg_block = $gutenberg_blocks_parts[$middle_index];
                $insertion_point = trim($block_start . $gutenberg_block);
                $post_content = str_replace($insertion_point, $insertion_point . '<!-- wp:paragraph -->' . $ai_content . '<!-- /wp:paragraph -->', $post_content);
                break;
            }
        }
    }
    if ($insertion_point === null) {
        foreach ($insertion_tags as $tag) {
            $nodes = $xpath->query("//" . $tag . "[not(ancestor::h1) and not(ancestor::h2) and not(ancestor::h3) and not(ancestor::h4) and not(ancestor::h5) and not(ancestor::h6) and not(ancestor::a)]");
            if ($nodes->length > 0) {
                $insertion_point = $nodes->item(intval($nodes->length / 2));
                break;
            }
        }
    }
    else
    {
        return $post_content;
    }
    if ($insertion_point === null) {
        $insertion_point = $dom->documentElement;
    }
    if ($insertion_point !== null && strpos($post_content, $ai_content) === false) {
        $fragment = $dom->createDocumentFragment();
        $fragment->appendXML($ai_content);
        if($insertion_point->parentNode !== null)
        {
            $insertion_point->parentNode->insertBefore($fragment, $insertion_point->nextSibling);
        }
        else
        {
            $insertion_point->insertBefore($fragment, $insertion_point->nextSibling);
        }
        $post_content = $dom->saveHTML();
    }
    if($post_content_unchanged == $post_content)
    {
        $post_content .= '<br/>' . $ai_content;
    }
    return $post_content;
}
function aiomatic_get_blog_timezone() {

    $tzstring = get_option( 'timezone_string' );
    $offset   = get_option( 'gmt_offset' );

    if( empty( $tzstring ) && 0 != $offset && floor( $offset ) == $offset ){
        $offset_st = $offset > 0 ? "-$offset" : '+'.absint( $offset );
        $tzstring  = 'Etc/GMT'.$offset_st;
    }
    if( empty( $tzstring ) ){
        $tzstring = 'UTC';
    }
    $timezone = new DateTimeZone( $tzstring );
    return $timezone; 
}
function aiomatic_get_locales_content($selected)
{
    $options  = [];
    foreach($GLOBALS['aiomatic_language_by_locale'] as $code => $lang)
    {
        $selma = '';
        if(in_array( $code, $selected ))
        {
            $selma = ' selected';
        }
        $options[]  = sprintf( '<option value="%s" %s>%s</option>', $code, $selma, $lang );
    }
    return implode("", $options);
}
function aiomatic_get_user_roles_content($selected)
{
    $options  = [];
    $roles = get_editable_roles();
    foreach ( $roles as $k => $role ) {
        $selma = '';
        if(in_array( $k, $selected ))
        {
            $selma = ' selected';
        }
        $options[]  = sprintf( '<option value="%s" %s>%s</option>', $k, $selma, $role['name'] );
    }
    return implode("", $options);
}
function aiomatic_get_devices_content($selected)
{
    $options  = [];
    $devices = ['desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile'];
    foreach ( $devices as $k => $role ) {
        $selma = '';
        if(in_array( $k, $selected ))
        {
            $selma = ' selected';
        }
        $options[]  = sprintf( '<option value="%s" %s>%s</option>', $k, $selma, $role );
    }
    return implode("", $options);
}
function aiomatic_detectOS($oses) 
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/' . str_replace('#', '', $oses) . '/i', $user_agent)) {
        return true;
    }
    return false;
}
function aiomatic_detectBrowser($browsers) 
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/' . str_replace('#', '', $browsers) . '/i', $user_agent)) {
        return true;
    }
    return false;
}
function aiomatic_passIPs( $IPsVal ) 
{
    if ( is_array( $IPsVal ) ) {
        $IPsVal = implode( ',', $IPsVal );
    }
    $IPsVal = explode( ',', str_replace( [' ', "\r", "\n"], ['', '', ','], $IPsVal ) );
    return aiomatic_checkIPList( $IPsVal );
}
function aiomatic_checkIPList( $IPsVal ) 
{
    foreach ( $IPsVal as $range ) {
        if ( ! aiomatic_checkIP( $range ) ) {
            continue;
        }
        return true;
    }
    return false;
}
function aiomatic_checkIP( $range ) 
{
    if ( empty( $range ) ) {
        return false;
    }
    if ( strpos( $range, '-' ) !== false ) {
        return aiomatic_checkIPRange( $range );
    }

    return aiomatic_checkIPPart( $range );
}
function aiomatic_filter_sections($sections)
{
    $sections_arr = preg_split('/\r\n|\r|\n/', $sections);
    $filtered_sections = array_map(function($line) {
        return wp_strip_all_tags(trim(trim($line), ' “”‘’"\''));
    }, $sections_arr);
    return implode("\n", $filtered_sections);
}
function aiomatic_checkIPPart( $range ) 
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if ( empty( $ip ) ) {
        return false;
    }
    $ip_parts = explode( '.', $ip );
    $range_parts = explode( '.', trim( $range ) );
    $ip = implode( '.', array_slice( $ip_parts, 0, count( $range_parts ) ) );
    return ! ( $range !== $ip );

}
function aiomatic_checkIPRange( $range ) 
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if ( empty( $ip ) ) { return false; }
    list( $min, $max ) = explode( '-', trim( $range ), 2 );
    if ( $ip < trim( $min ) ) { return false; }
    $max = aiomatic_fillMaxRange( $max, $min );
    return ! ( $ip > trim( $max ) );
}
function aiomatic_fillMaxRange( $max, $min ) 
{
    $max_parts = explode( '.', $max );
    if ( count( $max_parts ) === 4 ) { return $max; }
    $min_parts = explode( '.', $min );
    $prefix = array_slice( $min_parts, 0, count( $min_parts ) - count( $max_parts ) );
    return implode( '.', $prefix ) . '.' . implode( '.', $max_parts );
}
function aiomatic_parse_multiple_json($jsonString) 
{
    $results = [];
    $depth = 0;
    $start = 0;
    $currentJson = '';
    for ($i = 0; $i < strlen($jsonString); $i++) 
    {
        $char = $jsonString[$i];
        if ($char === '{') 
        {
            if ($depth === 0) 
            {
                $start = $i; 
            }
            $depth++;
        } 
        elseif ($char === '}') 
        {
            $depth--;
            if ($depth === 0) 
            {
                $currentJson = substr($jsonString, $start, $i - $start + 1);
                $decoded = json_decode($currentJson);
                if ($decoded !== null) 
                {
                    $results[] = $decoded;
                }
                else
                {
                    $decoded = json_decode(stripslashes($currentJson));
                    if($decoded !== null)
                    {
                        $results[] = $decoded;
                    }
                }
            }
        }
    }
    return $results;
}
function aiomatic_get_oses_content($selected)
{
    $options  = [];
    $oses = array(
        'Windows' => esc_attr('Windows (All)'),
        'Windows nt 11.0' => esc_attr('Windows 11'),
        'Windows nt 10.0' => esc_attr('Windows 10'),
        'Windows nt 6.2' => esc_attr('Windows 8'),
        'Windows nt 6.1' => esc_attr('Windows 7'),
        'Windows nt 6.0' => esc_attr('Windows Vista'),
        'Windows nt 5.2' => esc_attr('Windows Server 2003'),
        'Windows nt 5.1' => esc_attr('Windows XP'),
        'Windows nt 5.01' => esc_attr('Windows 2000 sp1'),
        'Windows nt 5.0' => esc_attr('Windows 2000'),
        'Windows nt 4.0' => esc_attr('Windows NT 4.0'),
        'Win 9x 4.9' => esc_attr('Windows Me'),
        'Windows 98' => esc_attr('Windows 98'),
        'Windows 95' => esc_attr('Windows 95'),
        'Windows ce' => esc_attr('Windows CE'),
        '#(Mac OS|Mac_PowerPC|Macintosh)#' => esc_attr('Mac OS (All)'),
        'Mac OS X' => esc_attr('Mac OSX (All)'),
        'Mac OS X 10.11' => esc_attr('Mac OSX El Capitan'),
        'Mac OS X 10.10' => esc_attr('Mac OSX Yosemite'),
        'Mac OS X 10.9' => esc_attr('Mac OSX Mavericks'),
        'Mac OS X 10.8' => esc_attr('Mac OSX Mountain Lion'),
        'Mac OS X 10.7' => esc_attr('Mac OSX Lion'),
        'Mac OS X 10.6' => esc_attr('Mac OSX Snow Leopard'),
        'Mac OS X 10.5' => esc_attr('Mac OSX Leopard'),
        'Mac OS X 10.4' => esc_attr('Mac OSX Tiger'),
        'Mac OS X 10.3' => esc_attr('Mac OSX Panther'),
        'Mac OS X 10.2' => esc_attr('Mac OSX Jaguar'),
        'Mac OS X 10.1' => esc_attr('Mac OSX Puma'),
        'Mac OS X 10.0' => esc_attr('Mac OSX Cheetah'),
        '#(Mac_PowerPC|Macintosh)#' => esc_attr('Mac OS (classic)'),
        '#(Linux|X11)#' => esc_attr('Linux'),
        'OpenBSD' => esc_attr('Open BSD'),
        'SunOS' => esc_attr('Sun OS'),
        'QNX' => esc_attr('QNX'),
        'BeOS' => esc_attr('BeOS'),
        'OS/2' => esc_attr('OS/2')
    );
    foreach ( $oses as $k => $role ) {
        $selma = '';
        if(in_array( $k, $selected ))
        {
            $selma = ' selected';
        }
        $options[]  = sprintf( '<option value="%s" %s>%s</option>', $k, $selma, $role );
    }
    return implode("", $options);
}
function aiomatic_get_browsers_content($selected)
{
    $options  = [];
    $oses = array(
        'Chrome' => esc_attr('Chrome') . ' (' . esc_html__('All', 'aiomatic-automatic-ai-content-writer') . ')',
        '#Chrome\/(12[1-9]|130)\.#' => esc_attr('Chrome 121-130'),
        '#Chrome\/(11[1-9]|120)\.#' => esc_attr('Chrome 111-120'),
        '#Chrome\/(10[1-9]|110)\.#' => esc_attr('Chrome 101-110'),
        '#Chrome\/(9[1-9]|100)\.#' => esc_attr('Chrome 91-100'),
        '#Chrome\/(8[1-9]|90)\.#' => esc_attr('Chrome 81-90'),
        '#Chrome\/(7[1-9]|80)\.#' => esc_attr('Chrome 71-80'),
        '#Chrome\/(6[1-9]|70)\.#' => esc_attr('Chrome 61-70'),
        '#Chrome\/(5[1-9]|60)\.#' => esc_attr('Chrome 51-60'),
        '#Chrome\/(4[1-9]|50)\.#' => esc_attr('Chrome 41-50'),
        '#Chrome\/(3[1-9]|40)\.#' => esc_attr('Chrome 31-40'),
        '#Chrome\/(2[1-9]|30)\.#' => esc_attr('Chrome 21-30'),
        '#Chrome\/(1[1-9]|20)\.#' => esc_attr('Chrome 11-20'),
        '#Chrome\/([1-9]|10)\.#' => esc_attr('Chrome 1-10'),
        'Firefox' => esc_attr('Firefox') . ' (' . esc_html__('All', 'aiomatic-automatic-ai-content-writer') . ')',
        '#Firefox\/(12[1-9]|130)\.#' => esc_attr('Firefox 121-130'),
        '#Firefox\/(11[1-9]|120)\.#' => esc_attr('Firefox 111-120'),
        '#Firefox\/(10[1-9]|110)\.#' => esc_attr('Firefox 101-110'),
        '#Firefox\/(9[1-9]|100)\.#' => esc_attr('Firefox 91-100'),
        '#Firefox\/(8[1-9]|90)\.#' => esc_attr('Firefox 81-90'),
        '#Firefox\/(7[1-9]|80)\.#' => esc_attr('Firefox 71-80'),
        '#Firefox\/(6[1-9]|70)\.#' => esc_attr('Firefox 61-70'),
        '#Firefox\/(5[1-9]|60)\.#' => esc_attr('Firefox 51-60'),
        '#Firefox\/(4[1-9]|50)\.#' => esc_attr('Firefox 41-50'),
        '#Firefox\/(3[1-9]|40)\.#' => esc_attr('Firefox 31-40'),
        '#Firefox\/(2[1-9]|30)\.#' => esc_attr('Firefox 21-30'),
        '#Firefox\/(1[1-9]|20)\.#' => esc_attr('Firefox 11-20'),
        '#Firefox\/([1-9]|10)\.#' => esc_attr('Firefox 1-10'),
        'MSIE' => esc_attr('Internet Explorer') . ' (' . esc_html__('All', 'aiomatic-automatic-ai-content-writer') . ')',
        'MSIE Edge' => esc_attr('Internet Explorer Edge'),
        'Edge\/18' => esc_attr('Edge 18'),
        'Edge\/17' => esc_attr('Edge 17'),
        'Edge\/16' => esc_attr('Edge 16'),
        'Edge\/15' => esc_attr('Edge 15'),
        'Edge\/14' => esc_attr('Edge 14'),
        'Edge\/13' => esc_attr('Edge 13'),
        'Edge\/12' => esc_attr('Edge 12'),
        'MSIE 11' => esc_attr('Internet Explorer 11'),
        'MSIE 10.6' => esc_attr('Internet Explorer 10.6'),
        'MSIE 10.0' => esc_attr('Internet Explorer 10.0'),
        'MSIE 10.' => esc_attr('Internet Explorer 10'),
        'MSIE 9.' => esc_attr('Internet Explorer 9'),
        'MSIE 8.' => esc_attr('Internet Explorer 8'),
        'MSIE 7.' => esc_attr('Internet Explorer 7'),
        '#MSIE [1-6]\.#' => esc_attr('Internet Explorer 1-6'),
        'Opera' => esc_attr('Opera') . ' (' . esc_html__('All', 'aiomatic-automatic-ai-content-writer') . ')',
        '#Opera\/(11[1-9]|120)\.#' => esc_attr('Opera 111-120'),
        '#Opera\/(10[1-9]|110)\.#' => esc_attr('Opera 101-110'),
        '#Opera\/(9[1-9]|100)\.#' => esc_attr('Opera 91-100'),
        '#Opera\/(8[1-9]|90)\.#' => esc_attr('Opera 81-90'),
        '#Opera\/(7[1-9]|80)\.#' => esc_attr('Opera 71-80'),
        '#Opera\/(6[1-9]|70)\.#' => esc_attr('Opera 61-70'),
        '#Opera\/(5[1-9]|60)\.#' => esc_attr('Opera 51-60'),
        '#Opera\/(4[1-9]|50)\.#' => esc_attr('Opera 41-50'),
        '#Opera\/(3[1-9]|40)\.#' => esc_attr('Opera 31-40'),
        '#Opera\/(2[1-9]|30)\.#' => esc_attr('Opera 21-30'),
        '#Opera\/(1[1-9]|20)\.#' => esc_attr('Opera 11-20'),
        '#Opera\/([1-9]|10)\.#' => esc_attr('Opera 1-10'),
        'Safari' => esc_attr('Safari') . ' (' . esc_html__('All', 'aiomatic-automatic-ai-content-writer') . ')',
        '#Version\/17\..*Safari/#' => esc_attr('Safari 17'),
        '#Version\/16\..*Safari/#' => esc_attr('Safari 16'),
        '#Version\/15\..*Safari/#' => esc_attr('Safari 15'),
        '#Version\/14\..*Safari/#' => esc_attr('Safari 14'),
        '#Version\/13\..*Safari/#' => esc_attr('Safari 13'),
        '#Version\/12\..*Safari/#' => esc_attr('Safari 12'),
        '#Version\/11\..*Safari/#' => esc_attr('Safari 11'),
        '#Version\/10\..*Safari/#' => esc_attr('Safari 10'),
        '#Version\/9\..*Safari/#' => esc_attr('Safari 9'),
        '#Version\/8\..*Safari/#' => esc_attr('Safari 8'),
        '#Version\/7\..*Safari/#' => esc_attr('Safari 7'),
        '#Version\/6\..*Safari/#' => esc_attr('Safari 6'),
        '#Version\/5\..*Safari/#' => esc_attr('Safari 5'),
        '#Version\/4\..*Safari/#' => esc_attr('Safari 4'),
        '#Version\/[1-3]\..*Safari/#' => esc_attr('Safari 1-3')
    );
    foreach ( $oses as $k => $role ) {
        $selma = '';
        if(in_array( $k, $selected ))
        {
            $selma = ' selected';
        }
        $options[]  = sprintf( '<option value="%s" %s>%s</option>', $k, $selma, $role );
    }
    return implode("", $options);
}
function aiomatic_is_o1_pro_model($model)
{
    if($model == 'o1-pro')
    {
        return true;
    }
    return false;
}
function aiomatic_get_wordpress_content($selected)
{
    $options  = [];
    $defaults = [
        'search' => 'Search'
    ];
    if ( count( $selected ) > 1 && in_array( '*', $selected ) ) {
        $selected = [ '*' ];
    }
    foreach ( $defaults as $val => $label ) {
        $attributes = in_array( $val, $selected ) ? [
            'value'    => $val,
            'selected' => 'selected'
        ] : [ 'value' => $val ];
        $selma = '';
        if(isset($attributes['selected']))
        {
            $selma = ' selected';
        }
        $options[]  = sprintf( '<option value="%s" %s>%s</option>', $attributes["value"], $selma, $label );
    }
    if ( $pages = get_pages() ) 
    {
        $options[] = '<optgroup label="Pages">';
        array_unshift( $pages, (object) [ 'post_title' => 'Pages (All)' ] );
        foreach ( $pages as $page ) {
            $val        = isset( $page->ID ) ? 'page-' . $page->ID : 'page';
            $attributes = in_array( $val, $selected ) ? [
                'value'    => $val,
                'selected' => 'selected'
            ] : [ 'value' => $val ];
            $selma = '';
            if(isset($attributes['selected']))
            {
                $selma = ' selected';
            }
            $options[]  = sprintf( '<option value="%s" %s>%s</option>', $attributes["value"], $selma, $page->post_title );
        }

        $options[] = '</optgroup>';
    }
    $options[] = '<optgroup label="Post">';
    foreach ( [ 'home', 'single', 'archive' ] as $view ) {
        $val        = $view;
        $attributes = in_array( $val, $selected ) ? [
            'value'    => $val,
            'selected' => 'selected'
        ] : [ 'value' => $val ];
        $selma = '';
        if(isset($attributes['selected']))
        {
            $selma = ' selected';
        }
        $options[]  = sprintf( '<option value="%s" %s>%s (%s)</option>', $attributes["value"], $selma, 'Post', ucfirst( $view ) );
    }
    $options[] = '</optgroup>';
    foreach ( array_keys( get_post_types( ['_builtin' => false, 'publicly_queryable' => true ] ) ) as $posttype ) 
    {
        if($posttype == 'aiomatic_remote_chat')
        {
            continue;
        }
        $obj   = get_post_type_object( $posttype );
        if ( null === $obj ) { continue; }
        $label = ucfirst( $posttype );
        if ( $obj->publicly_queryable ) 
        {
            $options[] = '<optgroup label="' . $label . '">';
            foreach ( [ 'single', 'archive', 'search' ] as $view ) {
                $val        = $posttype . '-' . $view;
                $attributes = in_array( $val, $selected ) ? [
                    'value'    => $val,
                    'selected' => 'selected'
                ] : [ 'value' => $val ];
                $selma = '';
                if(isset($attributes['selected']))
                {
                    $selma = ' selected';
                }
                $options[]  = sprintf( '<option value="%s" %s>%s (%s)</option>', $attributes["value"], $selma, $label, ucfirst( $view ) );
            }
            $options[] = '</optgroup>';
        }
    }
    foreach ( array_keys( get_taxonomies() ) as $tax ) 
    {
        if ( in_array( $tax, [ "post_tag", "nav_menu" ] ) ) {
            continue;
        }
        if ( $categories = get_categories( [ 'taxonomy' => $tax ] ) ) {
            $options[] = '<optgroup label="Posts in taxonomy (' . ucfirst( str_replace( [
                    "_",
                    "-"
                ], " ", $tax ) ) . ')">';

            foreach ( $categories as $category ) {
                $val        = 'in-cat-' . $category->cat_ID;
                $attributes = in_array( $val, $selected ) ? [
                    'value'    => $val,
                    'selected' => 'selected'
                ] : [ 'value' => $val ];
                $selma = '';
                if(isset($attributes['selected']))
                {
                    $selma = ' selected';
                }
                $options[]  = sprintf( '<option value="%s" %s>%s</option>',
                    $attributes["value"], $selma,
                    esc_html__( 'In', 'aiomatic-automatic-ai-content-writer' ) . ' ' . $category->cat_name
                );
            }

            $options[] = '</optgroup>';
        }
    }
    foreach ( array_keys( get_taxonomies() ) as $tax ) 
    {
        if ( in_array( $tax, [ "post_tag", "nav_menu" ] ) ) {
            continue;
        }
        if ( $categories = get_categories( [ 'taxonomy' => $tax ] ) ) {
            $options[] = '<optgroup label="Archive by taxonomy (' . ucfirst( str_replace( [
                    "_",
                    "-"
                ], " ", $tax ) ) . ')">';
            foreach ( $categories as $category ) {
                $val        = 'cat-' . $category->cat_ID;
                $attributes = in_array( $val, $selected ) ? [
                    'value'    => $val,
                    'selected' => 'selected'
                ] : [ 'value' => $val ];
                $selma = '';
                if(isset($attributes['selected']))
                {
                    $selma = ' selected';
                }
                $options[]  = sprintf( '<option value="%s" %s>%s</option>', $attributes["value"], $selma, $category->cat_name );
            }
            $options[] = '</optgroup>';
        }
    }
    return implode( "", $options );
}
function aiomatic_generateRandomIP() 
{
    return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255);
}
function aiomatic_generateComplexRandomName() 
{
    $namePool = [
        'Western' => [
            'male' => [
                'James', 'John', 'Robert', 'Michael', 'William', 'David', 'Joseph', 'Charles', 'Thomas',
                'George', 'Christopher', 'Daniel', 'Matthew', 'Anthony', 'Mark', 'Paul', 'Steven', 'Andrew',
                'Joshua', 'Kenneth', 'Kevin', 'Brian', 'Edward', 'Ronald', 'Timothy', 'Jason', 'Jeffrey',
                'Ryan', 'Jacob', 'Gary', 'Nicholas', 'Eric', 'Jonathan', 'Stephen', 'Larry', 'Justin', 
                'Scott', 'Brandon', 'Frank', 'Benjamin', 'Gregory', 'Raymond', 'Samuel', 'Patrick', 'Alexander',
                'Jack', 'Dennis', 'Jerry', 'Tyler', 'Aaron', 'Henry', 'Douglas', 'Peter', 'Adam', 'Zachary',
                'Nathan', 'Walter', 'Harold', 'Kyle', 'Carl', 'Arthur', 'Gerald', 'Roger', 'Keith', 'Alan',
                'Philip', 'Vincent', 'Sean', 'Bruce', 'Lawrence', 'Wayne', 'Ralph', 'Bryan', 'Ethan', 'Jordan',
                'Roy', 'Louis', 'Dylan', 'Randy', 'Russell', 'Howard', 'Bobby', 'Johnny', 'Bradley', 'Albert',
                'Curtis', 'Barry', 'Leonard', 'Derek', 'Corey', 'Stanley', 'Craig', 'Terry', 'Lucas', 'Chad',
                'Mason', 'Clifford', 'Dean', 'Norman', 'Steve', 'Caleb', 'Mitchell', 'Jay', 'Don', 'Oscar'
            ],
            'female' => [
                'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah',
                'Karen', 'Nancy', 'Margaret', 'Lisa', 'Betty', 'Dorothy', 'Sandra', 'Ashley', 'Kimberly',
                'Donna', 'Emily', 'Michelle', 'Carol', 'Amanda', 'Melissa', 'Deborah', 'Stephanie', 'Rebecca',
                'Laura', 'Sharon', 'Cynthia', 'Kathleen', 'Amy', 'Shirley', 'Angela', 'Helen', 'Anna', 
                'Brenda', 'Pamela', 'Nicole', 'Emma', 'Samantha', 'Katherine', 'Christine', 'Debra', 'Rachel',
                'Catherine', 'Carolyn', 'Janet', 'Ruth', 'Maria', 'Heather', 'Diane', 'Virginia', 'Julie',
                'Joyce', 'Victoria', 'Olivia', 'Kelly', 'Christina', 'Lauren', 'Joan', 'Evelyn', 'Judith',
                'Megan', 'Alice', 'Hannah', 'Andrea', 'Jean', 'Cheryl', 'Martha', 'Jacqueline', 'Frances',
                'Ann', 'Gloria', 'Teresa', 'Kathryn', 'Sara', 'Judy', 'Theresa', 'Rose', 'Beverly', 'Denise',
                'Marilyn', 'Amber', 'Danielle', 'Abigail', 'Brittany', 'Jane', 'Tina', 'Beverly', 'Eleanor',
                'Charlotte', 'Marilyn', 'Monica', 'Grace', 'Valerie', 'Carrie', 'Alyssa', 'Jill', 'Beth', 'Elaine'
            ]
        ]
    ];

    $lastNamePool = [
        'Western' => [
            'Smith', 'Johnson', 'Brown', 'Taylor', 'Anderson', 'Walker', 'Hall', 'Allen', 'Young', 'Scott',
            'Harris', 'Clark', 'Lewis', 'Robinson', 'Walker', 'King', 'Wright', 'Lopez', 'Hill', 'Green',
            'Adams', 'Baker', 'Nelson', 'Carter', 'Mitchell', 'Perez', 'Roberts', 'Turner', 'Phillips', 'Campbell',
            'Parker', 'Evans', 'Edwards', 'Collins', 'Stewart', 'Sanchez', 'Morris', 'Rogers', 'Reed', 'Cook',
            'Morgan', 'Bell', 'Murphy', 'Bailey', 'Rivera', 'Cooper', 'Richardson', 'Cox', 'Howard', 'Ward',
            'Peterson', 'Gray', 'James', 'Watson', 'Brooks', 'Kelly', 'Sanders', 'Price', 'Bennett', 'Wood',
            'Barnes', 'Ross', 'Henderson', 'Coleman', 'Jenkins', 'Perry', 'Powell', 'Long', 'Patterson', 'Hughes',
            'Flores', 'Washington', 'Butler', 'Simmons', 'Foster', 'Gonzales', 'Bryant', 'Alexander', 'Russell',
            'Griffin', 'Diaz', 'Hayes', 'Myers', 'Ford', 'Hamilton', 'Graham', 'Sullivan', 'Wallace', 'Woods',
            'Cole', 'West', 'Jordan', 'Owens', 'Reynolds', 'Fisher', 'Ellis', 'Harrison', 'Gibson', 'Mcdonald',
            'Cruz', 'Marshall', 'Ortiz', 'Gomez', 'Murray', 'Freeman', 'Wells', 'Webb', 'Simpson', 'Stevens'
        ]
    ];

    $selectedRegion = 'Western';
    $gender = rand(0, 1) == 0 ? 'male' : 'female';
    $firstName = $namePool[$selectedRegion][$gender][array_rand($namePool[$selectedRegion][$gender])];
    $lastName1 = $lastNamePool[$selectedRegion][array_rand($lastNamePool[$selectedRegion])];
    $lastName2 = $lastNamePool[$selectedRegion][array_rand($lastNamePool[$selectedRegion])];
    $lastName = (rand(0, 15) == 0) ? "$lastName1-$lastName2" : $lastName1;
    $fullName = "$firstName $lastName";
    return $fullName;
}

function aiomatic_get_post_characteristics() 
{
    global $wp_query;
    $obj   = $wp_query->get_queried_object();
    $type  = get_post_type();
    $query = [];
    if ( is_home() ) {
        $query[] = 'home';
    }
    if ( is_front_page() ) {
        $query[] = 'front_page';
    }
    if ( $type === 'post' ) {
        if ( is_singular() ) {
            $query[] = 'single';
            $post_cats = get_the_category();
            if ( $post_cats ) {
                foreach ( $post_cats as $category ) {
                    $query[] = 'in-cat-' . $category->term_id;
                }
            }
        }
        if ( is_archive() ) {
            $query[] = 'archive';
        }
    } else if ( is_singular() ) {
        $query[] = $type . '-single';
    } elseif ( is_archive() ) {
        $query[] = $type . '-archive';
    }
    if ( is_search() ) {
        $query[] = 'search';
    }
    if ( is_page() ) {
        $query[] = $type;
        $query[] = $type . '-' . $obj->ID;
    }
    if ( is_category() ) {
        $query[] = 'cat-' . $obj->term_id;
    }
    if(!function_exists('is_plugin_active'))
    {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        if ( is_shop() && ! is_search() ) {
            $query[] = 'page';
            $query[] = 'page-' . wc_get_page_id( 'shop' );
        }
        if ( is_product_category() || is_product_tag() ) {
            $query[] = 'cat-' . $obj->term_id;
        }
    }
    return $query;
}
function aiomatic_containsMarkdown($text) 
{
    if(strstr($text, '**') !== false)
    {
        return true;
    }
    $markdownPatterns = [
        '/\s*#{1,6}\s+/',             // Headers (e.g., # Header, ## Header, etc.)
        '/\*\*[^*]+\*\*/',            // Bold (e.g., **bold**)
        '/\*[^*]+\*/',                // Italics (e.g., *italic*)
        '/!\[[^\]]*\]\([^\)]+\)/',    // Images (e.g., ![alt text](url))
        '/\[[^\]]*\]\([^\)]+\)/',     // Links (e.g., [text](url))
        '/(`{1,3})[^`]+(`{1,3})/',    // Inline code (e.g., `code` or ```code```)
        '/\*\s+/',                    // Unordered lists (e.g., * item)
        '/\d+\.\s+/',                 // Ordered lists (e.g., 1. item)
        '/>\s+/',                     // Blockquotes (e.g., > quote)
        '/-{3,}/',                    // Horizontal rules (e.g., ---)
        '/^\|.+\|$/m',                // Tables (e.g., | Header |)
    ];
    $htmlPattern = '/<[^>]+>/';
    if (preg_match($htmlPattern, $text)) {
        return false;
    }
    foreach ($markdownPatterns as $pattern) {
        if (preg_match($pattern, $text)) {
            return true;
        }
    }
    return false;
}
function aiomatic_rename_media_file( $attachment_id, $new_title ) 
{
    $file_path = get_attached_file( $attachment_id );
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
       include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
       wp_filesystem($creds);
    }
    if (!$wp_filesystem->exists( $file_path ) ) {
        return false;
    }
    $sanitized_title = sanitize_title( $new_title );
    $max_title_length = 80;
    $sanitized_title = mb_substr( $sanitized_title, 0, $max_title_length );
    $file_ext = pathinfo( $file_path, PATHINFO_EXTENSION );
    $new_filename = $sanitized_title . '.' . $file_ext;
    $new_file_path = trailingslashit( dirname( $file_path ) ) . $new_filename;
    $new_file_path = wp_unique_filename( dirname( $file_path ), $new_filename );
    $old_filename = basename( $file_path );
    update_post_meta( $attachment_id, '_aiomatic_old_filename', $old_filename );
    if ( rename( $file_path, dirname( $file_path ) . '/' . $new_file_path ) ) {
        update_attached_file( $attachment_id, dirname( $file_path ) . '/' . $new_file_path );
        return true;
    }
    return false;
}
function aiomatic_remove_parasite_phrases($text)
{
    $parasite_phrases = [
        'In conclusion,', 'In summary,', 'To sum up,', 'To conclude,', 'Finally,', 'In the end,', 'To wrap it up,', 'Overall,',
        'В заключение,', 'Итак,', 'В общем,', 'Наконец,', 'En conclusión,', 'En resumen,', 'Para concluir,', 'Finalmente,',
        'En conclusion,', 'En résumé,', 'Pour conclure,', 'Finalement,', 'Abschließend,', 'Zusammenfassend,', 'Um zusammenzufassen,',
        'Schließlich,', 'In conclusione,', 'In sintesi,', 'Per concludere,', 'Infine,', 'Em conclusão,', 'Em resumo,', 'Para concluir,',
        'Finalmente,', '总之,', '最后,', '結論として,', '要約すると,', '最後に,', '결론적으로,', '요약하자면,', '마지막으로,'
    ];
    foreach ($parasite_phrases as $phrase) 
    {
        $pattern = '/' . preg_quote($phrase, '/') . '\s*/i';
        $callback = function($matches) use (&$text)
        {
            $nextCharPos = strlen($matches[0]);
            if (isset($text) && $nextCharPos < strlen($text)) {
                $nextChar = substr($text, $nextCharPos, 1);
                if (ctype_alpha($nextChar)) 
                {
                    $text = substr_replace($text, strtoupper($nextChar), $nextCharPos, 1);
                }
                return '';
            }
        };
        $text = preg_replace_callback($pattern, $callback, $text);
    }
    return $text;
}
function aiomatic_pre_code_remove($content)
{
    if(preg_match('/^<pre><code/', $content) && preg_match('/<\/code><\/pre>$/', $content))
    {
        $content = preg_replace('/^<pre.*?><code.*?>/', '', $content);
        $content = preg_replace('/<\/code><\/pre>$/', '', $content);
        $content = html_entity_decode($content);
    }
    return $content;
}
function aiomatic_generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
function aiomatic_generate_random_token($len) {
    $characters = "abcdefghijklmnopqrstuvwxyz0123456789-";
    $word = "";
    for ($i = 0; $i < $len; $i++) {
        $word .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $word;
}
function aiomatic_extract_headings( &$find, &$replace, $content = '' ) 
{
    $matches = [];
    $anchor  = '';
    $items   = false;
    $collision_collector = [];

    if ( is_array( $find ) && is_array( $replace ) && $content ) 
    {
        $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
        $content = apply_filters( 'aiomatic_toc_extract_headings', $content );
        if ( preg_match_all( '/(<h([1-6]{1})[^>]*>).*<\/h\2>/msuU', $content, $matches, PREG_SET_ORDER ) ) 
        {
            if (!isset( $aiomatic_Spinner_Settings['heading_levels1'] ) || $aiomatic_Spinner_Settings['heading_levels1'] != 'on' || 
            !isset( $aiomatic_Spinner_Settings['heading_levels2'] ) || $aiomatic_Spinner_Settings['heading_levels2'] != 'on' ||
            !isset( $aiomatic_Spinner_Settings['heading_levels3'] ) || $aiomatic_Spinner_Settings['heading_levels3'] != 'on' ||
            !isset( $aiomatic_Spinner_Settings['heading_levels4'] ) || $aiomatic_Spinner_Settings['heading_levels4'] != 'on' ||
            !isset( $aiomatic_Spinner_Settings['heading_levels5'] ) || $aiomatic_Spinner_Settings['heading_levels5'] != 'on' ||
            !isset( $aiomatic_Spinner_Settings['heading_levels6'] ) || $aiomatic_Spinner_Settings['heading_levels6'] != 'on') 
            {
                $heading_levels_arr = [];
                if(isset( $aiomatic_Spinner_Settings['heading_levels1'] ) && $aiomatic_Spinner_Settings['heading_levels1'] == 'on')
                {
                    $heading_levels_arr[] = 1;
                }
                if(isset( $aiomatic_Spinner_Settings['heading_levels2'] ) && $aiomatic_Spinner_Settings['heading_levels2'] == 'on')
                {
                    $heading_levels_arr[] = 2;
                }
                if(isset( $aiomatic_Spinner_Settings['heading_levels3'] ) && $aiomatic_Spinner_Settings['heading_levels3'] == 'on')
                {
                    $heading_levels_arr[] = 3;
                }
                if(isset( $aiomatic_Spinner_Settings['heading_levels4'] ) && $aiomatic_Spinner_Settings['heading_levels4'] == 'on')
                {
                    $heading_levels_arr[] = 4;
                }
                if(isset( $aiomatic_Spinner_Settings['heading_levels5'] ) && $aiomatic_Spinner_Settings['heading_levels5'] == 'on')
                {
                    $heading_levels_arr[] = 5;
                }
                if(isset( $aiomatic_Spinner_Settings['heading_levels6'] ) && $aiomatic_Spinner_Settings['heading_levels6'] == 'on')
                {
                    $heading_levels_arr[] = 6;
                }
                $new_matches   = [];
                $count_matches = count( $matches );
                for ( $i = 0; $i < $count_matches; $i++ ) {
                    if ( in_array( (int) $matches[ $i ][2], $heading_levels_arr, true ) ) {
                        $new_matches[] = $matches[ $i ];
                    }
                }
                $matches = $new_matches;
            }
            if(count($matches) > 0)
            {
                if(isset( $aiomatic_Spinner_Settings['exclude_toc'] ) && $aiomatic_Spinner_Settings['exclude_toc'] != '')
                {
                    $excluded_headings       = explode( '|', trim($aiomatic_Spinner_Settings['exclude_toc']) );
                    $count_excluded_headings = count( $excluded_headings );
                    if ( $count_excluded_headings > 0 ) 
                    {
                        for ( $j = 0; $j < $count_excluded_headings; $j++ ) 
                        {
                            $excluded_headings[ $j ] = str_replace(
                                [ '*' ],
                                [ '.*' ],
                                trim( $excluded_headings[ $j ] )
                            );
                        }
                        $new_matches   = [];
                        $count_matches = count( $matches );
                        for ( $i = 0; $i < $count_matches; $i++ ) {
                            $found                   = false;
                            $count_excluded_headings = count( $excluded_headings );
                            for ( $j = 0; $j < $count_excluded_headings; $j++ ) {
                                if ( preg_match( '/^' . $excluded_headings[ $j ] . '$/imU', wp_strip_all_tags( $matches[ $i ][0] ) ) ) {
                                    $found = true;
                                    break;
                                }
                            }
                            if ( ! $found ) {
                                $new_matches[] = $matches[ $i ];
                            }
                        }
                        if ( count( $matches ) !== count( $new_matches ) ) {
                            $matches = $new_matches;
                        }
                    }
                }
                $new_matches   = [];
                $count_matches = count( $matches );
                for ( $i = 0; $i < $count_matches; $i++ ) 
                {
                    if ( trim( wp_strip_all_tags( $matches[ $i ][0] ) ) !== false ) 
                    {
                        $new_matches[] = $matches[ $i ];
                    }
                }
                if ( count( $matches ) !== count( $new_matches ) ) 
                {
                    $matches = $new_matches;
                }
                if(isset( $aiomatic_Spinner_Settings['when_toc'] ) && $aiomatic_Spinner_Settings['when_toc'] != '')
                {
                    $when_toc = intval($aiomatic_Spinner_Settings['when_toc']);
                }
                else
                {
                    $when_toc = 4;
                }
                if ( count( $matches ) >= $when_toc ) 
                {
                    $count_matches = count( $matches );
                    for ( $i = 0; $i < $count_matches; $i++ ) 
                    {
                        $anchor    = aiomatic_url_anchor_target( $matches[ $i ][0], $collision_collector );
                        $find[]    = $matches[ $i ][0];
                        $replace[] = str_replace(
                            [
                                $matches[ $i ][1],
                                '</h' . $matches[ $i ][2] . '>',
                            ],
                            [
                                $matches[ $i ][1] . '<span id="' . $anchor . '">',
                                '</span></h' . $matches[ $i ][2] . '>',
                            ],
                            $matches[ $i ][0]
                        );
                        if(!isset( $aiomatic_Spinner_Settings['hierarchy_toc'] ) || $aiomatic_Spinner_Settings['hierarchy_toc'] != 'on')
                        {
                            $items .= '<li><a href="#' . $anchor . '">';
                            if(isset( $aiomatic_Spinner_Settings['add_numbers_toc'] ) && $aiomatic_Spinner_Settings['add_numbers_toc'] == 'on')
                            {
                                $items .= count( $replace ) . '. ';
                            }
                            $items .= wp_strip_all_tags( $matches[ $i ][0] ) . '</a></li>';
                        }
                    }
                    if(isset( $aiomatic_Spinner_Settings['hierarchy_toc'] ) && $aiomatic_Spinner_Settings['hierarchy_toc'] == 'on')
                    {
                        $items = aiomatic_build_hierarchy( $matches, $collision_collector );
                    }
                }
            }
        }
    }
    return $items;
}
function aiomatic_is_base64($string) 
{
    if (strlen($string) % 4 !== 0) 
    {
        return false;
    }
    if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) 
    {
        return false;
    }
    $decoded = base64_decode($string, true);
    if ($decoded === false) 
    {
        return false;
    }
    if (base64_encode($decoded) !== $string) 
    {
        return false;
    }
    return true;
}
function aiomatic_extractTextFromObject($object) 
{
    $text = '';
    if (is_array($object)) {
        foreach ($object as $key => $value) {
            if (is_array($value) && isset($value[1])) {
                if ($value[1] === 'stream') {
                    $text .= gzuncompress($value[0]);
                } elseif ($value[1] === 'objref') {
                } else {
                    $text .= aiomatic_extractTextFromObject($value);
                }
            } else {
                $text .= aiomatic_extractTextFromObject($value);
            }
        }
    }
    return $text;
}
function aiomatic_build_hierarchy( &$matches, &$collision_collector ) {
    $current_depth      = 100;
    $html               = '';
    $numbered_items     = [];
    $numbered_items_min = null;
    $count_matches      = count( $matches );
    $collision_collector = [];
    for ( $i = 0; $i < $count_matches; $i++ ) {
        if ( $current_depth > $matches[ $i ][2] ) {
            $current_depth = (int) $matches[ $i ][2];
        }
    }
    $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', false);
    $heading_levels_arr = [];
    if(isset( $aiomatic_Spinner_Settings['heading_levels1'] ) && $aiomatic_Spinner_Settings['heading_levels1'] == 'on')
    {
        $heading_levels_arr[] = 1;
    }
    if(isset( $aiomatic_Spinner_Settings['heading_levels2'] ) && $aiomatic_Spinner_Settings['heading_levels2'] == 'on')
    {
        $heading_levels_arr[] = 2;
    }
    if(isset( $aiomatic_Spinner_Settings['heading_levels3'] ) && $aiomatic_Spinner_Settings['heading_levels3'] == 'on')
    {
        $heading_levels_arr[] = 3;
    }
    if(isset( $aiomatic_Spinner_Settings['heading_levels4'] ) && $aiomatic_Spinner_Settings['heading_levels4'] == 'on')
    {
        $heading_levels_arr[] = 4;
    }
    if(isset( $aiomatic_Spinner_Settings['heading_levels5'] ) && $aiomatic_Spinner_Settings['heading_levels5'] == 'on')
    {
        $heading_levels_arr[] = 5;
    }
    if(isset( $aiomatic_Spinner_Settings['heading_levels6'] ) && $aiomatic_Spinner_Settings['heading_levels6'] == 'on')
    {
        $heading_levels_arr[] = 6;
    }
    $numbered_items[ $current_depth ] = 0;
    $numbered_items_min               = $current_depth;
    for ( $i = 0; $i < $count_matches; $i++ ) 
    {
        if ( $current_depth === (int) $matches[ $i ][2] ) {
            $html .= '<li>';
        }
        if ( $current_depth !== (int) $matches[ $i ][2] ) {
            for ( $current_depth; $current_depth < (int) $matches[ $i ][2]; $current_depth++ ) {
                $numbered_items[ $current_depth + 1 ] = 0;
                $html                                .= '<ul><li>';
            }
        }
        if ( isset($matches[ $i ][2]) && in_array( (int) $matches[ $i ][2], $heading_levels_arr, true ) ) 
        {
            $html .= '<a href="#' . aiomatic_url_anchor_target( $matches[ $i ][0], $collision_collector ) . '">';
            if(isset( $aiomatic_Spinner_Settings['add_numbers_toc'] ) && $aiomatic_Spinner_Settings['add_numbers_toc'] == 'on')
            {
                $html .= '<span class="aiomatic_toc_number aiomatic_toc_depth_' . ( $current_depth - $numbered_items_min + 1 ) . '">';
                for ( $j = $numbered_items_min; $j < $current_depth; $j++ ) {
                    $number = ( $numbered_items[ $j ] ) ? $numbered_items[ $j ] : 0;
                    $html  .= $number . '.';
                }
                $html .= ( $numbered_items[ $current_depth ] + 1 ) . '</span> ';
                $numbered_items[ $current_depth ]++;
            }
            $html .= wp_strip_all_tags( $matches[ $i ][0] ) . '</a>';
        }
        if ( count( $matches ) - 1 !== $i ) {
            if ( $current_depth > (int) $matches[ $i + 1 ][2] ) {
                for ( $current_depth; $current_depth > (int) $matches[ $i + 1 ][2]; $current_depth-- ) 
                {
                    $html                            .= '</li></ul>';
                    $numbered_items[ $current_depth ] = 0;
                }
            }
            if ( isset($matches[ $i + 1 ][2]) && (int)$matches[ $i + 1 ][2] === $current_depth ) 
            {
                $html .= '</li>';
            }
        } 
        else 
        {
            for ( $current_depth; $current_depth >= $numbered_items_min; $current_depth-- ) 
            {
                $html .= '</li>';
                if ( $current_depth !== $numbered_items_min ) {
                    $html .= '</ul>';
                }
            }
        }
    }
    return $html;
}
function aiomatic_mb_find_replace( &$find = false, &$replace = false, &$string = '' ) 
{
    if ( is_array( $find ) && is_array( $replace ) && $string ) 
    {
        $count_find = count( $find );
        if ( function_exists( 'mb_strpos' ) ) 
        {
            for ( $i = 0; $i < $count_find; $i++ ) {
                $string =
                    mb_substr( $string, 0, mb_strpos( $string, $find[ $i ] ) ) .
                    $replace[ $i ] .
                    mb_substr( $string, mb_strpos( $string, $find[ $i ] ) + mb_strlen( $find[ $i ] ) );
            }
        } 
        else 
        {
            for ( $i = 0; $i < $count_find; $i++ ) 
            {
                $string = substr_replace(
                    $string,
                    $replace[ $i ],
                    strpos( $string, $find[ $i ] ),
                    strlen( $find[ $i ] )
                );
            }
        }
    }
    return $string;
}
function aiomatic_url_anchor_target( $title, &$collision_collector ) 
{
    $return = false;
    if ( $title ) 
    {
        $return = trim( wp_strip_all_tags( $title ) );
        $return = remove_accents( $return );
        $return = str_replace( [ "\r", "\n", "\n\r", "\r\n" ], ' ', $return );
        $return = str_replace( '&amp;', '', $return );
        $return = preg_replace( '/[^a-zA-Z0-9 \-_]*/', '', $return );
        $return = str_replace(
            [ '  ', ' ' ],
            '_',
            $return
        );
        $return = rtrim( $return, '-_' );
        $return = strtolower( $return );
        $return = str_replace( '_', '-', $return );
        $return = str_replace( '--', '-', $return );
    }
    if ( array_key_exists( $return, $collision_collector ) ) {
        $collision_collector[ $return ]++;
        $return .= '-' . $collision_collector[ $return ];
    } else {
        $collision_collector[ $return ] = 1;
    }
    return apply_filters( 'aiomatic_toc_url_anchor_target', $return );
}
function aiomatic_format_parsed_data($data) 
{
    $inputLines = explode("\n", trim($data['input']));
    $outputLines = explode("\n", trim($data['output']));
    if(count($inputLines) != count($outputLines))
    {
        aiomatic_log_to_file('Input and output files have different line lenghts');
        return '';
    }
    $inputData = [];
    foreach ($inputLines as $inputLine) {
        $input = json_decode($inputLine, true);
        if ($input !== null) {
            $inputData[$input['custom_id']] = $input;
        } else {
            aiomatic_log_to_file('Failed to parse input file: ' . $inputLine);
        }
    }
    $outme = '<div class="aiomatic-parsed-result">';
    $outme .= '<div class="aiomatic-carousel">';
    foreach ($outputLines as $key => $outputLine) 
    {
        $output = json_decode($outputLine, true);
        if($output === false)
        {
            aiomatic_log_to_file('Failed to parse input file: ' . $outputLine);
            continue;
        }
        $customId = $output['custom_id'];
        if (isset($inputData[$customId])) 
        {
            $input = $inputData[$customId];
            $systemContent = $input['body']['messages'][0]['content'];
            if(empty($systemContent))
            {
                $systemContent = '&nbsp;';
            }
            $inputContent = $input['body']['messages'][1]['content'];
            if(empty($inputContent))
            {
                $inputContent = '&nbsp;';
            }
            $outputContent = $output['response']['body']['choices'][0]['message']['content'];
            if(empty($outputContent))
            {
                $outputContent = '&nbsp;';
            }
            $customId = $input['custom_id'];
            $outme .= '<div class="aiomatic-carousel-item">';
            $outme .= '<h3>' . esc_html__("Request ID: ", 'aiomatic-automatic-ai-content-writer') . esc_html($customId) . '</h3>';
            $outme .= '<div class="aiomatic-carousel-content">';
            $outme .= '<div class="aiomatic-carousel-input">';
            $outme .= '<h4>' . esc_html__("System", 'aiomatic-automatic-ai-content-writer') . '</h4>';
            $outme .= '<p class="parser-holder">' . esc_html($systemContent) . '</p>';
            $outme .= '<h4>' . esc_html__("User", 'aiomatic-automatic-ai-content-writer') . '</h4>';
            $outme .= '<p class="parser-holder">' . esc_html($inputContent) . '</p>';
            $outme .= '</div>';
            $outme .= '<div class="aiomatic-carousel-output">';
            $outme .= '<h4>' . esc_html__("Assistant", 'aiomatic-automatic-ai-content-writer') . '</h4>';
            $outme .= '<p class="parser-holder">' . esc_html($outputContent) . '</p>';
            $outme .= '</div>';
            $outme .= '</div>';
            $outme .= '</div>';
        }
        else
        {
            aiomatic_log_to_file('No matching input found for custom_id: ' . $customId);
        }
    }
    $outme .= '</div><div class="aiomatic-carousel-content">';
    $outme .= '<button class="aiomatic-carousel-prev"';
    if(count($outputLines) == 1)
    {
        $outme .= ' disabled';
    }
    $outme .= '>' . esc_html__("Prev", 'aiomatic-automatic-ai-content-writer') . '</button>';
    $outme .= '<p id="aiomatic-paging-holder" class="cr_center">1/' . count($outputLines) . '</p>';
    $outme .= '<button class="aiomatic-carousel-next"';
    if(count($outputLines) == 1)
    {
        $outme .= ' disabled';
    }
    $outme .= '>' . esc_html__("Next", 'aiomatic-automatic-ai-content-writer') . '</button>';
    $outme .= '</div></div>'; 
    return $outme;
}
function aiomatic_removeBOM($str) 
{
    if (substr($str, 0, 3) == pack('CCC', 0xEF, 0xBB, 0xBF)) 
    {
        $str = substr($str, 3);
    }
    return $str;
}
function aiomatic_format_parsed_embeddings_data($data) 
{
    $inputLines = explode("\n", trim($data['input']));
    $outputLines = explode("\n", trim($data['output']));
    if(count($inputLines) != count($outputLines))
    {
        aiomatic_log_to_file('Input and output files have different line lenghts');
        return '';
    }
    $inputData = [];
    foreach ($inputLines as $inputLine) {
        $input = json_decode($inputLine, true);
        if ($input !== null) {
            $inputData[$input['custom_id']] = $input;
        } else {
            aiomatic_log_to_file('Failed to parse input file: ' . $inputLine);
        }
    }
    $outme = '<div class="aiomatic-parsed-result">';
    $outme .= '<div class="aiomatic-carousel">';
    foreach ($outputLines as $key => $outputLine) 
    {
        $output = json_decode($outputLine, true);
        if($output === false)
        {
            aiomatic_log_to_file('Failed to parse input file: ' . $outputLine);
            continue;
        }
        $customId = $output['custom_id'];
        if (isset($inputData[$customId])) 
        {
            $input = $inputData[$customId];
            $inputContent = $input['body']['input'];
            if(empty($inputContent))
            {
                $inputContent = '&nbsp;';
            }
            if(!isset($output['response']['body']['data'][0]['embedding']))
            {
                aiomatic_log_to_file('Failed to parse embedding output: ' . print_r($output, true));
                continue;
            }
            $outputContent = implode(',', $output['response']['body']['data'][0]['embedding']);
            if(empty($outputContent))
            {
                $outputContent = '&nbsp;';
            }
            $customId = $input['custom_id'];

            $outme .= '<div class="aiomatic-carousel-item">';
            $outme .= '<h3>' . esc_html__("Request ID: ", 'aiomatic-automatic-ai-content-writer') . esc_html($customId) . '</h3>';
            $outme .= '<div class="aiomatic-carousel-content">';
            $outme .= '<div class="aiomatic-carousel-input">';
            $outme .= '<h4>' . esc_html__("Input", 'aiomatic-automatic-ai-content-writer') . '</h4>';
            $outme .= '<p class="parser-holder">' . esc_html($inputContent) . '</p>';
            $outme .= '</div>';
            $outme .= '<div class="aiomatic-carousel-output">';
            $outme .= '<h4>' . esc_html__("Embedding", 'aiomatic-automatic-ai-content-writer') . '</h4>';
            $outme .= '<p class="parser-holder">' . esc_html($outputContent) . '</p>';
            $outme .= '</div>';
            $outme .= '</div>';
            $outme .= '</div>';
        }
        else
        {
            aiomatic_log_to_file('No matching embeddings input found for custom_id: ' . $customId);
        }
    }
    $outme .= '</div><div class="aiomatic-carousel-content">';
    $outme .= '<button class="aiomatic-carousel-prev"';
    if(count($outputLines) == 1)
    {
        $outme .= ' disabled';
    }
    $outme .= '>' . esc_html__("Prev", 'aiomatic-automatic-ai-content-writer') . '</button>';
    $outme .= '<p id="aiomatic-paging-holder" class="cr_center">1/' . count($outputLines) . '</p>';
    $outme .= '<button class="aiomatic-carousel-next"';
    if(count($outputLines) == 1)
    {
        $outme .= ' disabled';
    }
    $outme .= '>' . esc_html__("Next", 'aiomatic-automatic-ai-content-writer') . '</button>';
    $outme .= '</div></div>'; 
    return $outme;
}
function aiomatic_add_processed_keyword($keyword) 
{
    $processed_keywords = get_option('aiomatic_processed_keywords', array());
    if (!in_array($keyword, $processed_keywords)) 
    {
        $processed_keywords[] = $keyword;
        aiomatic_update_option('aiomatic_processed_keywords', $processed_keywords, false);
    }
}
function aiomatic_remove_processed_keyword($keyword) 
{
    $processed_keywords = get_option('aiomatic_processed_keywords', array());
    if (($key = array_search($keyword, $processed_keywords)) !== false) 
    {
        unset($processed_keywords[$key]);
        aiomatic_update_option('aiomatic_processed_keywords', $processed_keywords, false);
    }
}
function aiomatic_remove_processed_keywords($keywords) 
{
    $processed_keywords = get_option('aiomatic_processed_keywords', array());
    $updated = false;
    foreach($keywords as $keyword)
    {
        if (($key = array_search($keyword, $processed_keywords)) !== false) 
        {
            unset($processed_keywords[$key]);
            $updated = true;
        }
    }
    if($updated == true)
    {
        aiomatic_update_option('aiomatic_processed_keywords', $processed_keywords, false);
    }
}
function aiomatic_is_keyword_processed($keyword) 
{
    $processed_keywords = get_option('aiomatic_processed_keywords', array());
    return in_array($keyword, $processed_keywords);
}
function aiomatic_array_search_recursive($needle, $haystack) 
{
    foreach ($haystack as $value) {
        if (is_array($value) && aiomatic_array_search_recursive($needle, $value)) return true;
        else if ($value == $needle) return true;
    }
    return false;
}
function aiomatic_generatePostTitleFromUrl($url) 
{
    $path = parse_url($url, PHP_URL_PATH);
    $file = basename($path);
    $fileWithoutExtension = pathinfo($file, PATHINFO_FILENAME);
    $title = str_replace(['-', '_'], ' ', $fileWithoutExtension);
    $formattedTitle = ucwords($title);
    return $formattedTitle;
}
function aiomatic_simpleEncryptWithKey($text, $key) 
{
    $result = '';
    $keyLength = strlen($key);
    for ($i = 0, $len = strlen($text); $i < $len; $i++) 
    {
        $shift = ord($key[$i % $keyLength]);
        $char = chr((ord($text[$i]) + $shift) % 256);
        $result .= $char;
    }
    return base64_encode($result);
}
function aiomatic_simpleEncrypt($text, $shift) 
{
    $result = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];
        $char = chr(ord($char) + $shift);
        $result .= $char;
    }
    return base64_encode($result);
}
function aiomatic_truncate_float( $number, $precision = 4 ) 
{
    $factor = pow( 10, $precision );
    return floor( $number * $factor ) / $factor;
}
function aiomatic_parse_pre_code_entities($htmlContent)
{
    $dom = new DOMDocument();
    $convenc = htmlentities($htmlContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if($convenc === false)
    {
        return $htmlContent;
    }
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $convenc, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_use_internal_errors($internalErrors);
    $xpath = new DOMXPath($dom);
    $preCodeElements = $xpath->query('//pre|//code');
    if($preCodeElements === false || $preCodeElements->length == 0)
    {
        return $htmlContent;
    }
    foreach ($preCodeElements as $element) 
    {
        $fragment = $dom->createDocumentFragment();
        $fragment->appendXML(html_entity_decode($element->textContent));
        $element->parentNode->replaceChild($fragment, $element);
    }
    $cleanedHtml = $dom->saveHTML();
    return $cleanedHtml;
}
function aiomatic_makeAbsoluteUrl($originalUrl, $relativeUrl) 
{
    if (parse_url($relativeUrl, PHP_URL_SCHEME) != '') {
        return $relativeUrl;
    }
    $parts = parse_url($originalUrl);
    if ($relativeUrl[0] == '/') {
        return $parts['scheme'] . '://' . $parts['host'] . $relativeUrl;
    }
    $basePath = isset($parts['path']) ? $parts['path'] : '/';
    if ($basePath[strlen($basePath) - 1] != '/') {
        $basePath = dirname($basePath) . '/';
    }
    return $parts['scheme'] . '://' . $parts['host'] . $basePath . $relativeUrl;
}
function aiomatic_fix_relative_links($htmlContent, $originalUrl)
{
    if((stristr($htmlContent,'src=') === false && stristr($htmlContent,'href=') === false) || strip_tags($htmlContent) == $htmlContent)
    {
        return $htmlContent;
    }
    $doc = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8" ?>' . $htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_use_internal_errors($internalErrors);
    $xpath = new DOMXPath($doc);
    $imgNodes = $xpath->query('//img[@src]');
    $aNodes = $xpath->query('//a[@href]');
    if($imgNodes !== false)
    {
        /** @var \DOMElement $node */
        foreach ($imgNodes as $node) 
        {
            $src = $node->getAttribute('src');
            $absoluteUrl = aiomatic_makeAbsoluteUrl($originalUrl, $src);
            $node->setAttribute('src', $absoluteUrl);
        }
    }
    if($aNodes !== false)
    {
        /** @var \DOMElement $node */
        foreach ($aNodes as $node) 
        {
            $href = $node->getAttribute('href');
            $absoluteUrl = aiomatic_makeAbsoluteUrl($originalUrl, $href);
            $node->setAttribute('href', $absoluteUrl);
        }
    }
    return $doc->saveHTML();
}
function aiomatic_rrmdir($dir) {
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
        wp_filesystem($creds);
    }
    if ($wp_filesystem->is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir") 
            aiomatic_rrmdir($dir."/".$object); 
        else $wp_filesystem->delete($dir."/".$object);
      }
    }
    reset($objects);
    $wp_filesystem->rmdir($dir);
  }
}
function aiomatic_extract_remote_xlsx($inputFile, $xlsx_sheet)
{
    if(strstr(trim($inputFile), ' ') !== false)
    {
        $inputFile = str_replace(' ', '%20', trim($inputFile));
    }
    $full_row = array();
    try
    {
        global $wp_filesystem;
        if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
            include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
            wp_filesystem($creds);
        }
        $dir = dirname(__FILE__) . "/tmp";
        if(!$wp_filesystem->is_dir( $dir )) {
            $wp_filesystem->mkdir($dir);
        }
        $newfile = $dir . '/tmpxlsx.xlsx';

        if ( $wp_filesystem->copy($inputFile, $newfile, true) ) {
            $inputFile = $newfile;
        }else{
            aiomatic_log_to_file('Failed to copy remote file locally: ' . $inputFile);
            aiomatic_rrmdir($dir);
            return false;
        }
        if(!class_exists('ZipArchive'))
        {
            aiomatic_log_to_file('ZipArchive class not found, please activate it on your server');
            aiomatic_rrmdir($dir);
            return false;
        }
        $zip = new ZipArchive();
        if($zip->open($inputFile) !== TRUE)
        {
            aiomatic_log_to_file('Failed to open archive: ' . $inputFile);
            aiomatic_rrmdir($dir);
            return false;
        }
        if($zip->extractTo($dir) !== TRUE)
        {
            aiomatic_log_to_file('Failed to extractTo archive to: ' . $dir);
            $zip->close();
            aiomatic_rrmdir($dir);
            return false;
        }
        
        $strings = simplexml_load_file($dir . '/xl/sharedStrings.xml');
        if($strings === false)
        {
            aiomatic_log_to_file('Unexpected error while simplexml_load_file sharedStrings.');
            $zip->close();
            aiomatic_rrmdir($dir);
            return false;
        }
        if($xlsx_sheet === '' || !is_numeric($xlsx_sheet))
        {
            $xlsx_sheet = '1';
        }
        $sheet   = simplexml_load_file($dir . '/xl/worksheets/sheet' . $xlsx_sheet . '.xml');
        if($sheet === false)
        {
            aiomatic_log_to_file('Unexpected error while simplexml_load_file sheet1.');
            $zip->close();
            aiomatic_rrmdir($dir);
            return false;
        }
        $xlrows = $sheet->sheetData->row;
        $headers = array();
        foreach ($xlrows as $xlrow) {
            $arr = array();
            foreach ($xlrow->c as $cell) {
                $v = (string) $cell->v;
                if (isset($cell['t']) && $cell['t'] == 's') {
                    $s  = array();
                    $si = $strings->si[(int) $v];
                    $si->registerXPathNamespace('n', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                    foreach($si->xpath('.//n:t') as $t) {
                        $s[] = (string) $t;
                    }
                    $v = implode('',$s);
                }
                $v = trim(preg_replace('/\s+/', ' ', $v));
                $arr[] = $v;
            }
            while ("" === end($arr))
            {
                array_pop($arr);
            }
            $full_row[] = $arr;
        }
        $zip->close();
        aiomatic_rrmdir($dir);
    }
    catch(Exception $e)
    {
        aiomatic_log_to_file('Unexpected error while parsing xlsx: ' . $e->getMessage());
        return false;
    }
    return $full_row;
}
function aiomatic_fix_links($str, $url)
{
    require_once (dirname(__FILE__) . '/res/Net_URL2.php');
    require_once (dirname(__FILE__) . "/res/simple_html_dom.php");
    $replaced_links = array();
    $extractok = false;
    $html_dom_original_html = aiomatic_str_get_html($str);
    if($html_dom_original_html !== false && method_exists($html_dom_original_html, 'find'))
    {
        foreach($html_dom_original_html->find('a') as $a) 
        {
            $extractok = true;
            if($a->href) 
            {
                if($a->href == '#') 
                {
                    continue;
                }
                if(preg_match("/^(about|javascript|magnet|mailto|sms|tel|geo):/i", $a->href))
                {
                    continue;
                }
                if(!in_array($a->href, $replaced_links))
                {
                    $replaced_links[] = $a->href;
                    try {
                        $relUrl = new Net_URL2($a->href);
                        if ($relUrl->isAbsolute()) {
                            continue;
                        }
                        $baseUrl = new Net_URL2($url);
                        $absUrl = $baseUrl->resolve($relUrl);
                        $full_url = $absUrl->getURL();
                        if($full_url != $a->href)
                        {
                            if($a->href == '/')
                            {
                                $str = str_replace('href="' . $a->href .'"', 'href="' . $full_url . '"', $str);
                                $str = str_replace("href='" . $a->href ."'", 'href="' . $full_url . '"', $str);
                            }
                            else
                            {
                                $str = str_replace($a->href, $full_url, $str);
                            }
                        }
                    } catch (Exception $e) {
                        aiomatic_log_to_file('Unable to resolve relative link "' . $a->href . '" against base "' . $url . '": ' . $e->getMessage());
                        continue;
                    }
                }
            }
        }
        foreach($html_dom_original_html->find('img') as $img) 
        {
            $extractok = true;
            if($img->src) 
            {
                if(aiomatic_starts_with($img->src, 'data:image'))
                {
                    continue;
                }
                if(!in_array($img->src, $replaced_links))
                {
                    $replaced_links[] = $img->src;
                    try {
                        $relUrl = new Net_URL2($img->src);
                        if ($relUrl->isAbsolute()) {
                            continue;
                        }
                        $baseUrl = new Net_URL2($url);
                        $absUrl = $baseUrl->resolve($relUrl);
                        $full_url = $absUrl->getURL();
                        if($full_url != $img->src)
                        {
                            $str = str_replace($img->src, $full_url, $str);
                        }
                    } catch (Exception $e) {
                        aiomatic_log_to_file('Unable to resolve relative image link "' . $img->src . '" against base "' . $url . '": ' . $e->getMessage());
                        continue;
                    }
                }
            }
        }
    }
    if($extractok == false)
    {
        $htmlDom = new DOMDocument;
        $internalErrors = libxml_use_internal_errors(true);
        $htmlDom->loadHTML('<?xml encoding="utf-8" ?>' . $str);
        libxml_use_internal_errors($internalErrors);
        $links = $htmlDom->getElementsByTagName('a');
        foreach($links as $link)
        {
            $linkHref = $link->getAttribute('href');
            if(strlen(trim($linkHref)) == 0){
                continue;
            }
            if($linkHref[0] == '#'){
                continue;
            }
            if(preg_match("/^(about|javascript|magnet|mailto|sms|tel|geo):/i", $linkHref))
            {
                continue;
            }
            if(!in_array($linkHref, $replaced_links))
            {
                $replaced_links[] = $linkHref;
                try {
                    $relUrl = new Net_URL2($linkHref);
                    if ($relUrl->isAbsolute()) {
                        continue;
                    }
                    $baseUrl = new Net_URL2($url);
                    $absUrl = $baseUrl->resolve($relUrl);
                    $full_url = $absUrl->getURL();
                    if($full_url != $linkHref)
                    {
                        $str = str_replace($linkHref, $full_url, $str);
                    }
                } catch (Exception $e) {
                    aiomatic_log_to_file('Unable to resolve (2) relative link "' . $linkHref . '" against base "' . $url . '": ' . $e->getMessage());
                    continue;
                }
            }
        }
        $links = $htmlDom->getElementsByTagName('img');
        foreach($links as $link)
        {
            $linkHref = $link->getAttribute('src');
            if(strlen(trim($linkHref)) == 0){
                continue;
            }
            if(aiomatic_starts_with($linkHref, 'data:image'))
            {
                continue;
            }
            if(!in_array($linkHref, $replaced_links))
            {
                $replaced_links[] = $linkHref;
                try {
                    $relUrl = new Net_URL2($linkHref);
                    if ($relUrl->isAbsolute()) {
                        continue;
                    }
                    $baseUrl = new Net_URL2($url);
                    $absUrl = $baseUrl->resolve($relUrl);
                    $full_url = $absUrl->getURL();
                    if($full_url != $linkHref)
                    {
                        if($linkHref == '/')
                        {
                            $str = str_replace('href="' . $linkHref .'"', 'href="' . $full_url . '"', $str);
                            $str = str_replace("href='" . $linkHref ."'", 'href="' . $full_url . '"', $str);
                        }
                        else
                        {
                            $str = str_replace($linkHref, $full_url, $str);
                        }
                    }
                } catch (Exception $e) {
                    aiomatic_log_to_file('Unable to resolve (2) relative image link "' . $linkHref . '" against base "' . $url . '": ' . $e->getMessage());
                    continue;
                }
            }
        }
    }
    return $str;
}
function aiomatic_testPhantom()
{
    if(!function_exists('shell' . '_exec')) {
        return -1;
    }
    $disabled = explode(',', ini_get('disable_functions'));
    if(in_array('shell' . '_exec', $disabled))
    {
        return -2;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['phantom_path']) && $aiomatic_Main_Settings['phantom_path'] != '') 
    {
        $phantomjs_comm = $aiomatic_Main_Settings['phantom_path'] . ' ';
    }
    else
    {
        $phantomjs_comm = 'phantomjs ';
    }
    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
        aiomatic_log_to_file('PhantomJS TEST command: ' . $phantomjs_comm);
    }
    $shefunc = trim(' s ') . trim(' h ') . 'ell' . '_exec';
    $cmdResult = $shefunc($phantomjs_comm . '-h 2>&1');
    if(stristr($cmdResult, 'Usage') !== false)
    {
        return 1;
    }
    return 0;
}
function aiomatic_testOllama()
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['ollama_url']) || $aiomatic_Main_Settings['ollama_url'] == '') 
    {
        return 0;
    }
    $ollama_url = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['ollama_url']));
    $ollama_url = array_filter($ollama_url);
    $ollama_url = $ollama_url[array_rand($ollama_url)];
    $ollama_url = rtrim(trim($ollama_url), '/');
    $cmdResult = aiomatic_get_web_page($ollama_url . '/api/tags');
    if($cmdResult == false)
    {
        return 0;
    }
    if(stristr($cmdResult, '"models"') !== false)
    {
        return 1;
    }
    return 0;
}
function aiomatic_get_extensions()
{
    if(!function_exists('is_plugin_active'))
    {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
	$list = array(
        'amazon_s3'		=> array(
            'label' => esc_html__('Amazon S3 Storage For Images', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=aiomatic_admin_settings#tab-28'),
            'getlink' => 'https://wpbay.com/product/aiomatic-extension-amazon-s3-storage/',
            'icon' => 'https://i.ibb.co/6ZHGv5S/4923041-aws-icon.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Store royalty-free or AI-generated images on Amazon S3', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('aiomatic-extension-amazon-s3-images/aiomatic-extension-amazon-s3-images.php') ? '1' : '0',
        ),
        'amazon_api'		=> array(
            'label' => esc_html__('Amazon API', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=aiomatic_admin_settings#tab-2'),
            'getlink' => 'https://wpbay.com/product/aiomatic-extension-amazon-api/',
            'icon' => 'https://i.ibb.co/6JrS8L7/aws-api-gateway-icon.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Use the official Amazon API instead of using web scraping, to get Amazon product details', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('aiomatic-extension-amazon-api/aiomatic-extension-amazon-api.php') ? '1' : '0',
        ),
        'toubiz_api'		=> array(
            'label' => esc_html__('Toubiz API', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=aiomatic_admin_settings#tab-2'),
            'getlink' => 'https://wpbay.com/product/aiomatic-extension-toubiz-api/',
            'icon' => 'https://i.postimg.cc/XYnLwcn5/images.jpg',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Integrate Aiomatic with the Toubiz API, to get events from this event provider and list them in the chatbot', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('aiomatic-extension-toubiz-api/aiomatic-extension-toubiz-api.php') ? '1' : '0',
        ),
        'pdf_files'		=> array(
            'label' => esc_html__('PDF File Storage And Parsing', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=aiomatic_admin_settings'),
            'getlink' => 'https://wpbay.com/product/aiomatic-extension-pdf-file-storage-and-parsing/',
            'icon' => 'https://i.ibb.co/47bc0JF/download.jpg',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('PDF File Parsing And Storage Using OmniBlocks', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('aiomatic-extension-pdf-files/aiomatic-extension-pdf-files.php') ? '1' : '0',
        ),
        'fbomatic'		=> array(
            'label' => esc_html__('F-omatic Post Generator', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=fbomatic_facebook_panel'),
            'getlink' => 'https://1.envato.market/fbomatic',
            'icon' => 'https://s3.envato.com/files/361820408/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Adds two chatbot extensions & OmniBlocks for direct Facebook sharing of text and images', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('fbomatic-facebook-post-generator/fbomatic-facebook-post-generator.php') ? '1' : '0',
        ),
        'twitomatic'		=> array(
            'label' => esc_html__('Twitomatic Post Generator', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=twitomatic_twitter_panel'),
            'getlink' => 'https://1.envato.market/twitomatic',
            'icon' => 'https://s3.envato.com/files/222528386/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Adds a chatbot extension & OmniBlock for direct X (Twitter) sharing of content', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('twitomatic-twitter-post-generator/twitomatic-twitter-post-generator.php') ? '1' : '0',
        ),
        'threadsomatic'		=> array(
            'label' => esc_html__('Threadsomatic Auto Poster', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=threadsomatic_admin_settings'),
            'getlink' => 'https://wpbay.com/product/threadsomatic-threads-auto-poster/',
            'icon' => 'https://i.ibb.co/ymBQtrfr/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Adds a chatbot extension & OmniBlock for direct Threads sharing of content', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('threadsomatic-threads-auto-poster/threadsomatic-threads-auto-poster.php') ? '1' : '0',
        ),
        'youtubomatic'		=> array(
            'label' => esc_html__('Youtubomatic Post Generator', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=youtubomatic_community_panel'),
            'getlink' => 'https://1.envato.market/youtubomatic',
            'icon' => 'https://s3.envato.com/files/276765063/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Adds a chatbot extension & OmniBlock for direct YouTube Community sharing of content', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('youtubomatic-youtube-post-generator/youtubomatic-youtube-post-generator.php') ? '1' : '0',
        ),
        'linkedinomatic'		=> array(
            'label' => esc_html__('Linkedinomatic Post Generator', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=linkedinomatic_linkedin_panel'),
            'getlink' => 'https://1.envato.market/linkedinomatic',
            'icon' => 'https://s3.envato.com/files/244423310/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Adds a chatbot extension & OmniBlock for direct LinkedIn sharing of content', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('linkedinomatic-linkedin-post-generator/linkedinomatic-linkedin-post-generator.php') ? '1' : '0',
        ),
        'redditomatic'		=> array(
            'label' => esc_html__('Redditomatic Post Generator', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=redditomatic_reddit_panel'),
            'getlink' => 'https://1.envato.market/redditomatic',
            'icon' => 'https://s3.envato.com/files/224590839/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Adds a chatbot extension & OmniBlock for direct Reddit sharing of content', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('redditomatic-reddit-post-generator/redditomatic-reddit-post-generator.php') ? '1' : '0',
        ),
        'instamatic'		=> array(
            'label' => esc_html__('iMediamatic Instagram Bot', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=instamatic_admin_settings'),
            'getlink' => 'https://1.envato.market/instamatic',
            'icon' => 'https://s3.envato.com/files/223152623/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Adds a chatbot extension & OmniBlock for direct Instagram sharing of content', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('instamatic-instagram-post-generator/instamatic-instagram-post-generator.php') ? '1' : '0',
        ),
        'pinterestomatic'		=> array(
            'label' => esc_html__('Pinterestomatic Post Generator', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=pinterestomatic_admin_settings'),
            'getlink' => 'https://1.envato.market/pinterestomatic',
            'icon' => 'https://s3.envato.com/files/223667806/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Adds a chatbot extension & OmniBlock for direct Pinterest sharing of content', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('pinterestomatic-pinterest-post-generator/pinterestomatic-pinterest-post-generator.php') ? '1' : '0',
        ),
        'helper'		=> array(
            'label' => esc_html__('Configuration Import/Export Helper', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=coderevolution_admin_settings'),
            'getlink' => 'https://1.envato.market/config',
            'icon' => 'https://s3.envato.com/files/232437363/avatar2.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Backup and restore plugin configuration or rule settings and move them between different websites', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('coderevolution-config-import-export-helper-plugin/coderevolution-config-import-export-helper-plugin.php') ? '1' : '0',
        ),
        'minute_run'		=> array(
            'label' => esc_html__('Schedule Minute/Hour Switcher', 'aiomatic-automatic-ai-content-writer'),
            'link' => admin_url('admin.php?page=cr_schedule_admin_settings'),
            'getlink' => 'https://coderevolution.ro/product/coderevolution-schedule-minute-hour-switcher/',
            'icon' => 'https://i.ibb.co/p2nJrR0/icon.png',
            'extra_class' => '',
            'pro' => false,
            'description' => esc_html__('Switch scheduling of the plugin from hour based running to minute based running', 'aiomatic-automatic-ai-content-writer'),
            'enabled' => is_plugin_active('coderevolution-schedule-minute-hour-switcher/coderevolution-schedule-minute-hour-switcher.php') ? '1' : '0',
        )
	);
    $list[ 'new_extension' ] = array(
        'label'						=> esc_html__( 'Add new Extensions', 'aiomatic-automatic-ai-content-writer' ),
        'link' 						=> 'https://wpbay.com/product-tag/aiomatic/',
        'getlink' 					=> 'https://wpbay.com/product-tag/aiomatic/',
        'icon'						=> 'https://i.ibb.co/bdpSQhT/392530-add-create-cross-new-plus-icon.png',
        'extra_class' 		        => 'aiomatic-new-extension-box',
        'description'			    => '',
        'enabled'					=> 1,
    );
    return $list;
}
function aiomatic_fetch_related_posts($post_id, $limit = 5, $similarity_threshold = 0.7) {
    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') {
        return [];
    }
    $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', []);
    $duplication_index = $aiomatic_Spinner_Settings['duplication_index'] ?? '_aiomatic_index';

    if(empty($duplication_index))
    {
        $duplication_index = '_aiomatic_index';
    }
    $current_embedding = get_post_meta($post_id, $duplication_index, true);
    if (!is_array($current_embedding)) {
        $current_embedding = aiomatic_index_single_post($post_id);
        if($current_embedding === false)
        {
            return [];
        }
    }

    $paged = 1;
    $posts_per_page = 100;
    $existing_posts = [];
    $to_index_ids = [];

    do {
        $args = [
            'post_type'      => $post->post_type,
            'posts_per_page' => $posts_per_page,
            'post_status'    => 'publish',
            'post__not_in'   => [$post_id],
            'paged'          => $paged,
        ];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            foreach ($query->posts as $single_post) {
                $id = $single_post->ID;
                $embedding = get_post_meta($id, $duplication_index, true);
                if (!is_array($embedding)) {
                    $to_index_ids[] = $id;
                } else {
                    $existing_posts[] = [
                        'post' => $single_post,
                        'embedding' => $embedding
                    ];
                }
            }
        }
        $paged++;
        wp_reset_postdata();
    } while ($query->have_posts());

    if (!empty($to_index_ids)) {
        $ids_to_process = array_slice($to_index_ids, 0, 10);
        $defer = 0;
        foreach ($ids_to_process as $id) {
            if (!wp_next_scheduled('aiomatic_index_single_post', [$id])) {
                wp_schedule_single_event(time() + $defer + 1, 'aiomatic_index_single_post', [$id]);
                $defer = $defer + 30;
            }
        }
    }

    $related = [];
    foreach ($existing_posts as $entry) {
        $similarity = aiomatic_cosine_similarity($current_embedding, $entry['embedding']);
        if ($similarity >= $similarity_threshold) {
            $related[] = [
                'post' => $entry['post'],
                'similarity' => $similarity
            ];
        }
    }

    usort($related, function ($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });

    return array_slice($related, 0, $limit);
}

add_action('aiomatic_index_single_post', 'aiomatic_index_single_post');
function aiomatic_index_single_post($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') {
        return false;
    }

    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', []);
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    $token = apply_filters('aiomatic_openai_api_key', $token);
    if(empty($token))
    {
        return false;
    }
    $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', []);
    $duplication_index = $aiomatic_Spinner_Settings['duplication_index'] ?? '_aiomatic_index';
    $duplication_template = $aiomatic_Spinner_Settings['duplication_template'] ?? '%%post_content_plain_text%%';
    if(empty($duplication_index))
    {
        $duplication_index = '_aiomatic_index';
    }
    if(empty($duplication_template))
    {
        $duplication_template = '%%post_content_plain_text%%';
    }
    $check_template = str_replace('%%post_title%%', $post->post_title, $duplication_template);
    $check_template = str_replace('%%post_content%%', $post->post_content, $check_template);
    $check_template = str_replace('%%post_excerpt%%', $post->post_excerpt, $check_template);
    $check_template = str_replace('%%post_content_plain_text%%', wp_strip_all_tags(strip_shortcodes($post->post_content)), $check_template);
    $check_template = aiomatic_replaceSynergyShortcodes($check_template);
    $new_embedding = get_post_meta($post->ID, $duplication_index, true);
    if (!$new_embedding) 
    {
        $new_embedding = aiomatic_generate_embeddings($check_template, $token);
        if (!$new_embedding) {
            return false;
        }
        else
        {
            if ($new_embedding['status'] == 'error') {
                return false;
            }
            else
            {
                $new_embedding = $new_embedding['data'];
                update_post_meta($post->ID, $duplication_index, $new_embedding);
                return $new_embedding;
            }
        }
    }
    return $new_embedding;
}

function aiomatic_index_unprocessed_posts($limit = 20) 
{
    $aiomatic_Spinner_Settings = get_option('aiomatic_Spinner_Settings', []);
    $duplication_index = $aiomatic_Spinner_Settings['duplication_index'] ?? '_aiomatic_index';
    $duplication_template = $aiomatic_Spinner_Settings['duplication_template'] ?? '%%post_content_plain_text%%';

    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];

    $token = apply_filters('aiomatic_openai_api_key', $token);
    if(empty($token))
    {
        return 0;
    }
    $args = [
        'post_type' => 'post',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => $duplication_index,
                'compare' => 'NOT EXISTS'
            ]
        ],
        'posts_per_page' => $limit,
        'fields' => 'ids',
    ];

    $posts = get_posts($args);
    $processed = 0;

    foreach ($posts as $post_id) {
        $post = get_post($post_id);

        if (!$post) continue;

        $template = str_replace([
            '%%post_title%%',
            '%%post_content%%',
            '%%post_excerpt%%',
            '%%post_content_plain_text%%'
        ], [
            $post->post_title,
            $post->post_content,
            $post->post_excerpt,
            wp_strip_all_tags(strip_shortcodes($post->post_content))
        ], $duplication_template);

        $template = aiomatic_replaceSynergyShortcodes($template);
        $embedding = aiomatic_generate_embeddings($template, $token);

        if (!empty($embedding['data'])) {
            update_post_meta($post_id, $duplication_index, $embedding['data']);
            $processed++;
        }
    }

    return $processed;
}
function aiomatic_replace_omniblocks_data($prompt, $current_keyword, $kiwis, $block_results, $load_variables)
{
    $matches = array();
    $i = 0;
    preg_match_all('~%regex\(\s*\"([^"]+?)\s*"\s*[,;]\s*\"([^"]*)\"\s*(?:[,;]\s*\"([^"]*?)\s*\")?(?:[,;]\s*\"([^"]*?)\s*\")?(?:[,;]\s*\"([^"]*?)\s*\")?\)%~si', $prompt, $matches);
    if (is_array($matches) && count($matches) && is_array($matches[0])) {
        for($i = 0; $i < count($matches[0]); $i++)
        {
            if (isset($matches[0][$i])) $fullmatch = $matches[0][$i];
            if (isset($matches[1][$i])) $search_in = aiomatic_replace_omniblocks_data($matches[1][$i], $current_keyword, $kiwis, $block_results, $load_variables);
            if (isset($matches[2][$i])) $matchpattern = $matches[2][$i];
            if (isset($matches[3][$i])) $element = $matches[3][$i];
            if (isset($matches[4][$i])) $delimeter = $matches[4][$i];if (isset($matches[5][$i])) $counter = $matches[5][$i];
            if (isset($matchpattern)) {
               if (preg_match('<^[\/#%+~[\]{}][\s\S]*[\/#%+~[\]{}]$>', $matchpattern, $z)) {
                  $ret = preg_match_all($matchpattern, $search_in, $submatches, PREG_PATTERN_ORDER);
               }
               else {
                  $ret = preg_match_all('~'.$matchpattern.'~si', $search_in, $submatches, PREG_PATTERN_ORDER);
               }
            }
            if (isset($submatches)) {
               if (is_array($submatches)) {
                  $empty_elements = array_keys($submatches[0], "");
                  foreach ($empty_elements as $e) {
                     unset($submatches[0][$e]);
                  }
                  $submatches[0] = array_unique($submatches[0]);
                  if (!is_numeric($element)) {
                     $element = 0;
                  }if (!is_numeric($counter)) {
                     $counter = 0;
                  }
                  if(isset($submatches[(int)($element)]))
                  {
                      $matched = $submatches[(int)($element)];
                  }
                  else
                  {
                      $matched = '';
                  }
                  $matched = array_unique((array)$matched);
                  if (empty($delimeter) || $delimeter == 'null') {
                     if (isset($matched[$counter])) $matched = $matched[$counter];
                  }
                  else {
                     $matched = implode($delimeter, $matched);
                  }
                  if (empty($matched)) {
                     $prompt = str_replace($fullmatch, '', $prompt);
                  } else {
                     $prompt = str_replace($fullmatch, $matched, $prompt);
                  }
               }
            }
        }
    }
    preg_match_all('~%regextext\(\s*\"([^"]+?)\s*"\s*,\s*\"([^"]*)\"\s*(?:,\s*\"([^"]*?)\s*\")?(?:,\s*\"([^"]*?)\s*\")?(?:,\s*\"([^"]*?)\s*\")?\)%~si', $prompt, $matches);
    if (is_array($matches) && count($matches) && is_array($matches[0])) {
        for($i = 0; $i < count($matches[0]); $i++)
        {
            if (isset($matches[0][$i])) $fullmatch = $matches[0][$i];
            if (isset($matches[1][$i])) $search_in = aiomatic_replace_omniblocks_data($matches[1][$i], $current_keyword, $kiwis, $block_results, $load_variables);
            if (isset($matches[2][$i])) $matchpattern = $matches[2][$i];
            if (isset($matches[3][$i])) $element = $matches[3][$i];
            if (isset($matches[4][$i])) $delimeter = $matches[4][$i];if (isset($matches[5][$i])) $counter = $matches[5][$i];
            $search_in = strip_tags($search_in, '<p><br>');
            $search_in1 = preg_replace("/<p[^>]*?>/", "", $search_in);
            if($search_in1 !== null)
            {
                $search_in = $search_in1;
            }
            $search_in = str_replace("</p>", "<br />", $search_in);
            $search_in1 = preg_replace('/\<br(\s*)?\/?\>/i', "\r\n\r\n", $search_in);
            if($search_in1 !== null)
            {
                $search_in = $search_in1;
            }
            $search_in1 = preg_replace('/^(?:\r|\n|\r\n)+/', '', $search_in);
            if($search_in1 !== null)
            {
                $search_in = $search_in1;
            }
            if (isset($matchpattern)) {
               if (preg_match('<^[\/#%+~[\]{}][\s\S]*[\/#%+~[\]{}]$>', $matchpattern, $z)) {
                  $ret = preg_match_all($matchpattern, $search_in, $submatches, PREG_PATTERN_ORDER);
               }
               else {
                  $ret = preg_match_all('~'.$matchpattern.'~si', $search_in, $submatches, PREG_PATTERN_ORDER);
               }
            }
            if (isset($submatches)) {
               if (is_array($submatches)) {
                  $empty_elements = array_keys($submatches[0], "");
                  foreach ($empty_elements as $e) {
                     unset($submatches[0][$e]);
                  }
                  $submatches[0] = array_unique($submatches[0]);
                  if (!is_numeric($element)) {
                     $element = 0;
                  }if (!is_numeric($counter)) {
                     $counter = 0;
                  }
                  if(isset($submatches[(int)($element)]))
                  {
                      $matched = $submatches[(int)($element)];
                  }
                  else
                  {
                      $matched = '';
                  }
                  $matched = array_unique((array)$matched);
                  if (empty($delimeter) || $delimeter == 'null') {
                     if (isset($matched[$counter])) $matched = $matched[$counter];
                  }
                  else {
                     $matched = implode($delimeter, $matched);
                  }
                  if (empty($matched)) {
                     $prompt = str_replace($fullmatch, '', $prompt);
                  } else {
                     $prompt = str_replace($fullmatch, $matched, $prompt);
                  }
               }
            }
        }
    }
    $spintax = new Aiomatic_Spintax();
    $prompt = $spintax->process($prompt);
    $pcxxx = explode('<!- template ->', $prompt);
    $prompt = $pcxxx[array_rand($pcxxx)];
    $prompt = str_replace('%%random_sentence%%', aiomatic_random_sentence_generator(), $prompt);
    $prompt = str_replace('%%random_sentence2%%', aiomatic_random_sentence_generator(false), $prompt);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['custom_html'])) {
        $xspintax = html_entity_decode($aiomatic_Main_Settings['custom_html']);
        $xspintax = $spintax->process($xspintax);
        $prompt = str_replace('%%custom_html%%', $xspintax, $prompt);
    }
    else
    {
        $prompt = str_replace('%%custom_html%%', '', $prompt);
    }
    if (isset($aiomatic_Main_Settings['custom_html2'])) {
        $xspintax2 = html_entity_decode($aiomatic_Main_Settings['custom_html2']);
        $xspintax2 = $spintax->process($xspintax2);
        $prompt = str_replace('%%custom_html2%%', $xspintax2, $prompt);
    }
    else
    {
        $prompt = str_replace('%%custom_html2%%', '', $prompt);
    }
    $prompt = str_replace('%%keyword%%', $current_keyword, $prompt);
    if(is_array($kiwis))
    {
        foreach($kiwis as $kws => $data)
        {
            $expdata = explode(',', $data);
            $expdata = trim($expdata[array_rand($expdata)]);
            $prompt = str_replace('%%' . $kws . '%%', $expdata, $prompt);
        }
    }
    if(is_array($load_variables))
    {
        foreach($load_variables as $variable_id => $variable_value)
        {
            $prompt = str_replace('%%' . $variable_id . '%%', $variable_value, $prompt);
        }
    }
    if(is_array($block_results))
    {
        foreach($block_results as $blid => $block)
        {
            if(is_array($block) && isset($block[1]))
            {
                if(isset($block[1]))
                {
                    if(is_string($block[1]))
                    {
                        $prompt = str_replace('%%output_' . $blid . '%%', $block[1], $prompt);
                        $prompt = str_replace('%%output-' . $blid . '%%', $block[1], $prompt);
                    }
                }
                else
                {
                    $prompt = str_replace('%%output_' . $blid . '%%', '', $prompt);
                    $prompt = str_replace('%%output-' . $blid . '%%', '', $prompt);
                }
                if($block[0] == 'ai_text')
                {
                    $prompt = str_replace('%%ai_text_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'ai_text_foreach')
                {
                    $prompt = str_replace('%%ai_text_foreach_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'dalle_ai_image')
                {
                    $prompt = str_replace('%%dalle_image_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'royalty_image')
                {
                    $prompt = str_replace('%%free_image_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'stable_ai_image')
                {
                    $prompt = str_replace('%%stability_image_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'google_ai_image')
                {
                    $prompt = str_replace('%%google_image_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'plain_text')
                {
                    $prompt = str_replace('%%plain_text_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'diy')
                {
                    $prompt = str_replace('%%diy_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'midjourney_ai_image')
                {
                    $prompt = str_replace('%%midjourney_image_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'replicate_ai_image')
                {
                    $prompt = str_replace('%%replicate_image_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'ideogram_ai_image')
                {
                    $prompt = str_replace('%%ideogram_image_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'stable_ai_video')
                {
                    $prompt = str_replace('%%stability_video_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'tts_openai')
                {
                    $prompt = str_replace('%%audio_url_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'stt_openai')
                {
                    $prompt = str_replace('%%audio_transcript_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'nlp_entities_neuron')
                {
                    $prompt = str_replace('%%entities_title_' . $blid . '%%', $block[1], $prompt);
                    $prompt = str_replace('%%entities_description_' . $blid . '%%', $block[2], $prompt);
                    $prompt = str_replace('%%entities_h1_' . $blid . '%%', $block[3], $prompt);
                    $prompt = str_replace('%%entities_h2_' . $blid . '%%', $block[4], $prompt);
                    $prompt = str_replace('%%entities_content_basic_' . $blid . '%%', $block[5], $prompt);
                    $prompt = str_replace('%%entities_content_basic_with_ranges_' . $blid . '%%', $block[6], $prompt);
                    $prompt = str_replace('%%entities_content_extended_' . $blid . '%%', $block[7], $prompt);
                    $prompt = str_replace('%%entities_content_extended_with_ranges_' . $blid . '%%', $block[8], $prompt);
                    $prompt = str_replace('%%entities_list_' . $blid . '%%', $block[8], $prompt);
                }
                elseif($block[0] == 'nlp_entities')
                {
                    $prompt = str_replace('%%entities_' . $blid . '%%', $block[1], $prompt);
                    $prompt = str_replace('%%entities_details_json_' . $blid . '%%', $block[2], $prompt);
                }
                elseif($block[0] == 'webhook_fire')
                {
                    if(is_array($block[1]))
                    {
                        foreach($block[1] as $wkey => $wdata)
                        {
                            if(is_string($wdata))
                            {
                                $prompt = str_replace('%%webhook_data_' . $blid . '_' . $wkey . '%%', $wdata, $prompt);
                            }
                            else
                            {
                                $prompt = str_replace('%%webhook_data_' . $blid . '_' . $wkey . '%%', '', $prompt);
                            }
                        }
                        $prompt = str_replace('%%webhook_data_' . $blid . '%%', json_encode($block[1]), $prompt);
                    }
                    else
                    {
                        $prompt = str_replace('%%webhook_data_' . $blid . '%%', $block[1], $prompt);
                    }
                }
                elseif($block[0] == 'ai_forms_omniblock')
                {
                    if(is_array($block[1]))
                    {
                        foreach($block[1] as $wkey => $wdata)
                        {
                            if(is_string($wdata))
                            {
                                $prompt = str_replace('%%ai_forms_full_input_' . $blid . '_' . $wkey . '%%', $wdata, $prompt);
                            }
                            else
                            {
                                $prompt = str_replace('%%ai_forms_full_input_' . $blid . '_' . $wkey . '%%', '', $prompt);
                            }
                        }
                        $prompt = str_replace('%%ai_forms_full_input_' . $blid . '%%', json_encode($block[1]), $prompt);
                    }
                    else
                    {
                        $prompt = str_replace('%%ai_forms_full_input_' . $blid . '%%', $block[1], $prompt);
                    }
                }
                elseif($block[0] == 'ai_chatbot_omniblock')
                {
                    if(is_array($block[1]))
                    {
                        foreach($block[1] as $wkey => $wdata)
                        {
                            if(is_string($wdata))
                            {
                                $prompt = str_replace('%%ai_chatbot_full_input_' . $blid . '_' . $wkey . '%%', $wdata, $prompt);
                            }
                            else
                            {
                                $prompt = str_replace('%%ai_chatbot_full_input_' . $blid . '_' . $wkey . '%%', '', $prompt);
                            }
                        }
                        $prompt = str_replace('%%ai_chatbot_full_input_' . $blid . '%%', json_encode($block[1]), $prompt);
                    }
                    else
                    {
                        $prompt = str_replace('%%ai_chatbot_full_input_' . $blid . '%%', $block[1], $prompt);
                    }
                }
                elseif($block[0] == 'wp_event_trigger')
                {
                    $prompt = str_replace('%%event_param1_' . $blid . '%%', $block[1]['event_param1_'], $prompt);
                    $prompt = str_replace('%%event_param2_' . $blid . '%%', $block[1]['event_param2_'], $prompt);
                    $prompt = str_replace('%%event_param3_' . $blid . '%%', $block[1]['event_param3_'], $prompt);
                    $prompt = str_replace('%%event_param4_' . $blid . '%%', $block[1]['event_param4_'], $prompt);
                    $prompt = str_replace('%%event_param5_' . $blid . '%%', $block[1]['event_param5_'], $prompt);
                    $prompt = str_replace('%%event_param6_' . $blid . '%%', $block[1]['event_param6_'], $prompt);
                    $prompt = str_replace('%%event_param7_' . $blid . '%%', $block[1]['event_param7_'], $prompt);
                    $prompt = str_replace('%%event_param8_' . $blid . '%%', $block[1]['event_param8_'], $prompt);
                    $prompt = str_replace('%%event_param9_' . $blid . '%%', $block[1]['event_param9_'], $prompt);
                    $prompt = str_replace('%%event_param10_' . $blid . '%%', $block[1]['event_param10_'], $prompt);
                }
                elseif($block[0] == 'crawl_sites')
                {
                    $prompt = str_replace('%%scraped_content_' . $blid . '%%', $block[1], $prompt);
                    $prompt = str_replace('%%scraped_content_plain_' . $blid . '%%', wp_strip_all_tags($block[1]), $prompt);
                }
                elseif($block[0] == 'crawl_rss')
                {
                    $prompt = str_replace('%%rss_content_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'google_search')
                {
                    $prompt = str_replace('%%search_result_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'text_translate')
                {
                    $prompt = str_replace('%%translated_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'text_spinner')
                {
                    $prompt = str_replace('%%spun_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'embeddings')
                {
                    $prompt = str_replace('%%embeddings_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'internet_access')
                {
                    $prompt = str_replace('%%internet_access_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'youtube_video')
                {
                    $prompt = str_replace('%%video_url_' . $blid . '%%', $block[1], $prompt);
                    $prompt = str_replace('%%video_embed_' . $blid . '%%', $block[2], $prompt);
                }
                elseif($block[0] == 'post_import')
                {
                    $prompt = str_replace('%%post_id_' . $blid . '%%', $block[1], $prompt);
                    $prompt = str_replace('%%post_url_' . $blid . '%%', $block[2], $prompt);
                    $prompt = str_replace('%%post_title_' . $blid . '%%', $block[3], $prompt);
                    $prompt = str_replace('%%post_content_' . $blid . '%%', $block[4], $prompt);
                    $prompt = str_replace('%%post_excerpt_' . $blid . '%%', $block[5], $prompt);
                    $prompt = str_replace('%%post_categories_' . $blid . '%%', $block[6], $prompt);
                    $prompt = str_replace('%%post_tags_' . $blid . '%%', $block[7], $prompt);
                    $prompt = str_replace('%%post_author_' . $blid . '%%', $block[8], $prompt);
                    $prompt = str_replace('%%post_date_' . $blid . '%%', $block[9], $prompt);
                    $prompt = str_replace('%%post_status_' . $blid . '%%', $block[10], $prompt);
                    $prompt = str_replace('%%post_type_' . $blid . '%%', $block[11], $prompt);
                    $prompt = str_replace('%%post_image_' . $blid . '%%', $block[12], $prompt);
                }
                elseif($block[0] == 'random_line')
                {
                    $prompt = str_replace('%%random_line_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'youtube_caption')
                {
                    $prompt = str_replace('%%video_caption_' . $blid . '%%', $block[1], $prompt);
                    $prompt = str_replace('%%video_title_' . $blid . '%%', $block[2], $prompt);
                    $prompt = str_replace('%%video_description_' . $blid . '%%', $block[3], $prompt);
                    $prompt = str_replace('%%video_thumb_' . $blid . '%%', $block[4], $prompt);
                }
                elseif($block[0] == 'amazon_product')
                {
                    $prompt = str_replace('%%product_title_' . $blid . '%%', $block[1], $prompt);
                    $prompt = str_replace('%%product_description_' . $blid . '%%', $block[2], $prompt);
                    $prompt = str_replace('%%product_url_' . $blid . '%%', $block[3], $prompt);
                    $prompt = str_replace('%%product_price_' . $blid . '%%', $block[4], $prompt);
                    $prompt = str_replace('%%product_list_price_' . $blid . '%%', $block[5], $prompt);
                    $prompt = str_replace('%%product_image_' . $blid . '%%', $block[6], $prompt);
                    $prompt = str_replace('%%product_cart_url_' . $blid . '%%', $block[7], $prompt);
                    $prompt = str_replace('%%product_images_urls_' . $blid . '%%', $block[8], $prompt);
                    $prompt = str_replace('%%product_images_' . $blid . '%%', $block[9], $prompt);
                    $prompt = str_replace('%%product_reviews_' . $blid . '%%', $block[10], $prompt);
                    $prompt = str_replace('%%product_reviews_' . $blid . '%%', $block[10], $prompt);
                    //new
                    $prompt = str_replace('%%product_score_' . $blid . '%%', $block[11], $prompt);
                    $prompt = str_replace('%%product_language_' . $blid . '%%', $block[12], $prompt);
                    $prompt = str_replace('%%product_edition_' . $blid . '%%', $block[13], $prompt);
                    $prompt = str_replace('%%product_pages_count_' . $blid . '%%', $block[14], $prompt);
                    $prompt = str_replace('%%product_publication_date_' . $blid . '%%', $block[15], $prompt);
                    $prompt = str_replace('%%product_contributors_' . $blid . '%%', $block[16], $prompt);
                    $prompt = str_replace('%%product_manufacturer_' . $blid . '%%', $block[17], $prompt);
                    $prompt = str_replace('%%product_binding_' . $blid . '%%', $block[18], $prompt);
                    $prompt = str_replace('%%product_product_group_' . $blid . '%%', $block[19], $prompt);
                    $prompt = str_replace('%%product_rating_' . $blid . '%%', $block[20], $prompt);
                    $prompt = str_replace('%%product_eans_' . $blid . '%%', $block[21], $prompt);
                    $prompt = str_replace('%%product_part_no_' . $blid . '%%', $block[22], $prompt);
                    $prompt = str_replace('%%product_model_' . $blid . '%%', $block[23], $prompt);
                    $prompt = str_replace('%%product_warranty_' . $blid . '%%', $block[24], $prompt);
                    $prompt = str_replace('%%product_color_' . $blid . '%%', $block[25], $prompt);
                    $prompt = str_replace('%%product_is_adult_' . $blid . '%%', $block[26], $prompt);
                    $prompt = str_replace('%%product_dimensions_' . $blid . '%%', $block[27], $prompt);
                    $prompt = str_replace('%%product_date_' . $blid . '%%', $block[28], $prompt);
                    $prompt = str_replace('%%product_size_' . $blid . '%%', $block[29], $prompt);
                    $prompt = str_replace('%%product_unit_count_' . $blid . '%%', $block[30], $prompt);
                }
                elseif($block[0] == 'amazon_listing')
                {
                    $prompt = str_replace('%%product_listing_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'load_file')
                {
                    if(is_array($block[1]))
                    {
                        preg_match_all('#%%xlsx_' . preg_quote($blid) . '_(\d+)_(\d+)%%#i', $prompt, $mmatch);
                        if(isset($mmatch[1][0]))
                        {
                            $cnt = 0;
                            for($cnt = 0; $cnt < count($mmatch[1]); $cnt++)
                            {
                                if(isset($mmatch[2][$cnt]))
                                {
                                    if(isset($block[1][$mmatch[1][$cnt]-1][$mmatch[2][$cnt]-1]))
                                    {
                                        $prompt = str_replace('%%xlsx_' . $blid . '_' . $mmatch[1][$cnt] . '_' . $mmatch[2][$cnt] . '%%', $block[1][$mmatch[1][$cnt]-1][$mmatch[2][$cnt]-1], $prompt);
                                    }
                                }
                                $prompt = str_replace('%%xlsx_' . $blid . '_' . $mmatch[1][$cnt] . '_' . $mmatch[2][$cnt] . '%%', '', $prompt);
                            }
                        }
                        preg_match_all('#%%xlsx_' . preg_quote($blid) . '_row_(\d+)%%#i', $prompt, $mmatch);
                        if(isset($mmatch[1][0]))
                        {
                            $cnt = 0;
                            for($cnt = 0; $cnt < count($mmatch[1]); $cnt++)
                            {
                                if(isset($block[1][$mmatch[1][$cnt]-1][0]))
                                {
                                    $row_data = '';
                                    foreach($block[1][$mmatch[1][$cnt]-1] as $rowme)
                                    {
                                        $row_data .= $rowme . ' ';
                                    }
                                    $prompt = str_replace('%%xlsx_' . $blid . '_row_' . $mmatch[1][$cnt] . '%%', $row_data, $prompt);
                                }
                                $prompt = str_replace('%%xlsx_' . $blid . '_row_' . $mmatch[1][$cnt] . '%%', '', $prompt);
                            }
                        }
                        preg_match_all('#%%xlsx_' . preg_quote($blid) . '_column_(\d+)%%#i', $prompt, $mmatch);
                        if(isset($mmatch[1][0]))
                        {
                            $cnt = 0;
                            for($cnt = 0; $cnt < count($mmatch[1]); $cnt++)
                            {
                                if(isset($block[1][0][$mmatch[1][$cnt]-1]))
                                {
                                    $column_data = '';
                                    for($xm = 0; $xm < count($block[1]); $xm++)
                                    {
                                        if(isset($block[1][$xm][$mmatch[1][$cnt]-1]))
                                        {
                                            $column_data .= $block[1][$xm][$mmatch[1][$cnt]-1] . ' ';
                                        }
                                    }
                                    $prompt = str_replace('%%xlsx_' . $blid . '_column_' . $mmatch[1][$cnt] . '%%', $column_data, $prompt);
                                }
                                $prompt = str_replace('%%xlsx_' . $blid . '_column_' . $mmatch[1][$cnt] . '%%', '', $prompt);
                            }
                        }
                        preg_match_all('#%%xlsx_' . preg_quote($blid) . '_row_random%%#i', $prompt, $mmatch);
                        if(isset($mmatch[0][0]))
                        {
                            $row_data = '';
                            $init_arr = $block[1];
                            while($row_data == '' && !empty($init_arr))
                            {
                                $cnt = array_rand($init_arr);
                                if(isset($init_arr[$cnt][0]))
                                {
                                    foreach($init_arr[$cnt] as $rowme)
                                    {
                                        $row_data .= $rowme . ' ';
                                    }
                                }
                                unset($init_arr[$cnt]);
                            }
                            $prompt = str_replace('%%xlsx_' . $blid . '_row_random%%', $row_data, $prompt);
                        }
                        preg_match_all('#%%xlsx_' . preg_quote($blid) . '_row_random_check%%#i', $prompt, $mmatch);
                        if(isset($mmatch[0][0]))
                        {
                            $row_data = '';
                            $init_arr = $block[1];
                            while($row_data == '' && !empty($init_arr))
                            {
                                $cnt = array_rand($init_arr);
                                if(isset($init_arr[$cnt][0]))
                                {
                                    foreach($init_arr[$cnt] as $rowme)
                                    {
                                        $row_data .= $rowme . ' ';
                                    }
                                }
                                unset($init_arr[$cnt]);
                                if($row_data !== '')
                                {
                                    $post_types = get_post_types(array('public' => true), 'names');
                                    $paged = 1; 
                                    $posts_per_page = 100; 
                                    $zsposts = array();
                                    do {
                                        $axargs = array(
                                            'post_type'      => $post_types,  
                                            'post_status'    => 'publish',   
                                            'posts_per_page' => $posts_per_page,
                                            'title'          => $row_data, 
                                            'fields'         => 'ids',    
                                            'paged'          => $paged,    
                                        );
                                        $query = new WP_Query($axargs);
                                        if ($query->have_posts()) {
                                            $zsposts = array_merge($zsposts, $query->posts); 
                                        }
                                        $paged++; 
                                        wp_reset_postdata(); 
                                    } while ($query->have_posts());
                                    if (!empty($zsposts)) {
                                        $row_data = '';
                                    }
                                }
                            }
                            $prompt = str_replace('%%xlsx_' . $blid . '_row_random_check%%', $row_data, $prompt);
                        }
                        $prompt = str_replace('%%file_' . $blid . '%%', '', $prompt);
                    }
                    else
                    {
                        $prompt = str_replace('%%file_' . $blid . '%%', $block[1], $prompt);
                    }
                }
                elseif($block[0] == 'save_post')
                {
                    $prompt = str_replace('%%created_post_id_' . $blid . '%%', $block[1], $prompt);
                    $prompt = str_replace('%%created_post_url_' . $blid . '%%', $block[2], $prompt);
                }
                elseif($block[0] == 'send_email')
                {
                }
                elseif($block[0] == 'send_facebook')
                {
                }
                elseif($block[0] == 'save_file')
                {
                }
                elseif($block[0] == 'send_twitter')
                {
                }
                elseif($block[0] == 'send_threads')
                {
                }
                elseif($block[0] == 'send_gmb')
                {
                }
                elseif($block[0] == 'send_community_youtube')
                {
                }
                elseif($block[0] == 'send_linkedin')
                {
                }
                elseif($block[0] == 'send_reddit')
                {
                }
                elseif($block[0] == 'send_webhook')
                {
                }
                elseif($block[0] == 'god_mode')
                {
                    $prompt = str_replace('%%god_mode_' . $blid . '%%', $block[1], $prompt);
                }
                elseif($block[0] == 'send_image_facebook')
                {
                }
                elseif($block[0] == 'send_image_instagram')
                {
                }
                elseif($block[0] == 'send_image_pinterest')
                {
                }
                elseif($block[0] == 'if_block')
                {
                }
                elseif($block[0] == 'wait_block')
                {
                }
                elseif($block[0] == 'exit_block')
                {
                }
                elseif($block[0] == 'jump_block')
                {
                }
                else
                {
                    aiomatic_log_to_file('Unknown OmniBlock type submitted: ' . $block[0]);
                }
            }
        }
    }
    if ( is_user_logged_in() ) 
    {
        $user_id = get_current_user_id();
        if($user_id !== 0)
        {
            preg_match_all('#%%~([^!]*?)~%%#', $prompt, $matched_content);
            if(isset($matched_content[1][0]))
            {
                foreach($matched_content[1] as $mc)
                {
                    $post_custom_data = get_user_meta($user_id, $mc, true);
                    if($post_custom_data != '')
                    {
                        $prompt = str_replace('%%~' . $mc . '~%%', $post_custom_data, $prompt);
                    }
                    else
                    {
                        $prompt = str_replace('%%~' . $mc . '~%%', '', $prompt);
                    }
                }
            }
        }
        else
        {
            preg_match_all('#%%~([^!]*?)~%%#', $prompt, $matched_content);
            if(isset($matched_content[1][0]))
            {
                foreach($matched_content[1] as $mc)
                {
                    $prompt = str_replace('%%~' . $mc . '~%%', '', $prompt);
                }
            }
        }
    } 
    else 
    {
        preg_match_all('#%%~([^!]*?)~%%#', $prompt, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $prompt = str_replace('%%~' . $mc . '~%%', '', $prompt);
            }
        }
    }
    $prompt = preg_replace_callback('#%%random_image_url\[([^\]]*?)\]%%#', function ($matches) {
        $arv = array();
        $my_img = aiomatic_get_random_image_google($matches[1], 0, 0, '', $arv);
        return $my_img;
    }, $prompt);
    $prompt = preg_replace_callback('#%%random_image\[([^\]]*?)\](\[\d+\])?%%#', function ($matches) {
        if(isset($matches[2]))
        {
            $chance = trim($matches[2], '[]');
        }
        else
        {
            $chance = '';
        }
        $arv = array();
        $my_img = aiomatic_get_random_image_google($matches[1], 0, 0, $chance, $arv);
        return '<img src="' . $my_img . '">';
    }, $prompt);
    $prompt = preg_replace_callback('#%%random_video\[([^\]]*?)\](\[\d+\])?%%#', function ($matches) {
        if(isset($matches[2]))
        {
            $chance = trim($matches[2], '[]');
        }
        else
        {
            $chance = '';
        }
        $my_vid = aiomatic_get_video($matches[1], $chance, array());
        return $my_vid;
    }, $prompt);
    $prompt = str_replace('%%current_date_time%%', date('Y/m/d H:i:s'), $prompt);
    $prompt = aiomatic_replaceSynergyShortcodes($prompt);
    $prompt = apply_filters('aiomatic_replace_aicontent_shortcode', $prompt);
    preg_match_all('#%%related_questions_([^%]*?)%%#i', $prompt, $mxatches);
    if(isset($mxatches[1][0]))
    {
        foreach($mxatches[1] as $googlematch)
        {
            $mtchres = aiomatic_scrape_related_questions($googlematch, 5, '', 1, 1, 0, 0, 2000, '', '');
            if(is_array($mtchres) && !empty($mtchres))
            {
                $quests = array();
                foreach($mtchres as $mra)
                {
                    if(isset($mra['q']))
                    {
                        $quests[] = $mra['q'];
                    }
                }
                $mtchres = implode(',', $quests);
            }
            $prompt = str_ireplace('%%related_questions_' . $googlematch . '%%', $mtchres, $prompt);
        }
    }
    if (!isset($aiomatic_Main_Settings['no_omni_shortcode_render']) || $aiomatic_Main_Settings['no_omni_shortcode_render'] != 'on')
    {
        if(stristr($prompt, 'aiomatic_charts') === false)
        {
            $prompt = do_shortcode($prompt);
        }
    }
    return trim($prompt);
}
function aiomatic_removeDuplicateNewLines($string) 
{
    return preg_replace("/[\r\n]+/", "\n", $string);
}
function aiomatic_get_assistant_models()
{
    return AIOMATIC_ASSISTANT_MODELS;
}
function aiomatic_scrape_page($url, $use_phantom, $type, $getname)
{
    require_once (dirname(__FILE__) . "/aiomatic-scraper.php");
    $custom_user_agent = aiomatic_get_random_user_agent();
    $custom_cookies = '';
    $use_proxy = '1';
    $user_pass = '';
    $phantom_wait = '';
    $request_delay = '';
    $scripter = '';
    $local_storage = '';
    $auto_captcha = '';
    $enable_adblock = '';
    $clickelement = '';
    $post_fields = '';
    $html_cont = false;
    $got_phantom = false;
    if($use_phantom == '1')
    {
        $html_cont = aiomatic_get_page_PhantomJS($url, $custom_cookies, $custom_user_agent, $use_proxy, $user_pass, $phantom_wait, $request_delay, $scripter, $local_storage);
        if($html_cont !== false)
        {
            $got_phantom = true;
        }
    }
    elseif($use_phantom == '2')
    {
        $html_cont = aiomatic_get_page_Puppeteer($url, $custom_cookies, $custom_user_agent, $use_proxy, $user_pass, $phantom_wait, $request_delay, $scripter, $local_storage);
        if($html_cont !== false)
        {
            $got_phantom = true;
        }
    }
    elseif($use_phantom == '3')
    {
        $html_cont = aiomatic_get_page_Tor($url, $custom_cookies, $custom_user_agent, $use_proxy, $user_pass, $phantom_wait, $request_delay, $scripter, $local_storage);
        if($html_cont !== false)
        {
            $got_phantom = true;
        }
    }
    elseif($use_phantom == '4')
    {
        $html_cont = aiomatic_get_page_PuppeteerAPI($url, $custom_cookies, $custom_user_agent, $use_proxy, $user_pass, $phantom_wait, $request_delay, $scripter, $local_storage, $auto_captcha, $enable_adblock, $clickelement);
        if($html_cont !== false)
        {
            $got_phantom = true;
        }
    }
    elseif($use_phantom == '5')
    {
        $html_cont = aiomatic_get_page_TorAPI($url, $custom_cookies, $custom_user_agent, $use_proxy, $user_pass, $phantom_wait, $request_delay, $scripter, $local_storage, $auto_captcha, $enable_adblock, $clickelement);
        if($html_cont !== false)
        {
            $got_phantom = true;
        }
    }
    elseif($use_phantom == '6')
    {
        $html_cont = aiomatic_get_page_PhantomJSAPI($url, $custom_cookies, $custom_user_agent, $use_proxy, $user_pass, $phantom_wait, $request_delay, $scripter, $local_storage);
        if($html_cont !== false)
        {
            $got_phantom = true;
        }
    }
    if($got_phantom === false)
    {
        $html_cont = aiomatic_get_web_page($url, $custom_cookies, $custom_user_agent, $use_proxy, $user_pass, '', $post_fields, $request_delay);
    }
    if($html_cont === false)
    {
        aiomatic_log_to_file('Failed to scrape content for: ' . $url); 
        return false;
    }
    if($type == 'raw')
    {
        return $html_cont;
    }
    $ret_cont = '';
    if($getname == '' || $type == 'auto')
    {
        $extract = aiomatic_convert_readable_html($html_cont);
        if($extract == '' || !isset($extract[1]))
        {
            aiomatic_log_to_file('Empty string returned: ' . $url); 
            return false;
        }
        else
        {
            $ret_cont = $extract[1];
        }
    }
    else
    {
        $extractorstr = '';
        $list_getname = preg_split('/\r\n|\r|\n/', $getname);
        foreach($list_getname as $my_getname)
        {
            $extractorstr .= aiomatic_get_content($type, $my_getname, $html_cont, false, false);
            if(!empty($extractorstr))
            {
                $extractorstr .= ' ';
            }
        }
        $ret_cont = $extractorstr;
        if($ret_cont == '')
        {
            $extract = aiomatic_convert_readable_html($html_cont);
            if($extract != '')
            {
                $ret_cont = $extract[1];
            }
        }
    }
    return $ret_cont;
}
use fivefilters\Readability\Readability;
use fivefilters\Readability\Configuration;
function aiomatic_convert_readable_html($html_string) 
{
    try 
    {
        if (version_compare(PHP_VERSION, '7.3.0', '<'))
        {
            throw new Exception('PHP is older than 7.3');
        }
        require_once (dirname(__FILE__) . "/res/readability/ReadabilityExtension.php");
        if(!class_exists('\fivefilters\Readability\Readability'))
        {
            require_once (dirname(__FILE__) . '/res/readability/vendor/autoload.php');

            require_once (dirname(__FILE__) . "/res/readability/Readability.php");
            require_once (dirname(__FILE__) . "/res/readability/ParseException.php");
            require_once (dirname(__FILE__) . "/res/readability/Configuration.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/NodeUtility.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/NodeTrait.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMAttr.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMNodeList.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMCdataSection.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMCharacterData.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMComment.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMDocument.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMDocumentFragment.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMDocumentType.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMElement.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMEntity.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMEntityReference.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMNode.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMNotation.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMProcessingInstruction.php");
            require_once (dirname(__FILE__) . "/res/readability/Nodes/DOM/DOMText.php");
        }
        $readConf = new Configuration();
        $readConf->setSummonCthulhu(true);
        $readability = new Readability($readConf);
        $readability->parse($html_string);
        $return_me[0] = $readability->getTitle();
        $return_me[1] = $readability->getContent();
        if($return_me[0] == '' || $return_me[0] == null || $return_me[1] == '' || $return_me[1] == null)
        {
            throw new Exception('Content/title blank ' . print_r($return_me, true));
        }
        $return_me[1] = str_replace('</article>', '', $return_me[1]);
        $return_me[1] = str_replace('<article>', '', $return_me[1]);
        return $return_me;
    } catch (Exception $e) {
        try
        {
            require_once (dirname(__FILE__) . "/res/aiomatic-readability.php");
            $readability = new Readability2($html_string);
            $readability->debug = false;
            $readability->convertLinksToFootnotes = false;
            $result = $readability->init();
            if ($result) {
                $return_me[0] = $readability->getTitle()->innerHTML;
                $return_me[1] = $readability->getContent()->innerHTML;
                $return_me[1] = str_replace('</article>', '', $return_me[1]);
                $return_me[1] = str_replace('<article>', '', $return_me[1]);
                return $return_me;
            } else {
                return '';
            }
        }
        catch(Exception $e2)
        {
            aiomatic_log_to_file('Readability failed: ' . sprintf('Error processing text: %s', $e2->getMessage()));
            return '';
        }
    }
}
function aiomatic_increment(&$string)
{
    $last_char = substr($string, -1);
    $rest = substr($string, 0, -1);
    switch ($last_char) {
    case '':
        $next = 'a';
        break;
    case 'z':
        $next = 'A';
        break;
    case 'Z':
        $next = '0';
        break;
    case '9':
        aiomatic_increment($rest);
        $next = 'a';
        break;
    default:
        $next = ++$last_char;
    }
    $string = $rest . $next;
}
function aiomatic_insert_attachment_by($value) {
    global $wpdb;
    $data = [
        'post_author'           => $value['post_author'],
        'guid'                  => $value['guid'],
        'post_title'            => $value['post_title'],
        'post_mime_type'        => $value['post_mime_type'],
        'post_type'             => $value['post_type'],
        'post_status'           => $value['post_status'],
        'post_parent'           => $value['post_parent'],
        'post_date'             => $value['post_date'],
        'post_date_gmt'         => $value['post_date_gmt'],
        'post_modified'         => $value['post_modified'],
        'post_modified_gmt'     => $value['post_modified_gmt'],
        'post_content'          => $value['post_content'],
        'post_excerpt'          => $value['post_excerpt'],
        'to_ping'               => $value['to_ping'],
        'pinged'                => $value['pinged'],
        'post_content_filtered' => $value['post_content_filtered'],
    ];
    $format = [
        '%d', // post_author
        '%s', // guid
        '%s', // post_title
        '%s', // post_mime_type
        '%s', // post_type
        '%s', // post_status
        '%d', // post_parent
        '%s', // post_date
        '%s', // post_date_gmt
        '%s', // post_modified
        '%s', // post_modified_gmt
        '%s', // post_content
        '%s', // post_excerpt
        '%s', // to_ping
        '%s', // pinged
        '%s', // post_content_filtered
    ];
    $wpdb->insert("{$wpdb->prefix}posts", $data, $format);
    return $wpdb->insert_id;
}
function aiomatic_get_formatted_value($url, $alt, $post_parent) {
    return "(77777, '" . $url . "', '" . str_replace("'", "", $alt) . "', 'image/jpeg', 'attachment', 'inherit', '" . $post_parent . "', now(), now(), now(), now(), '', '', '', '', '')";
}

function aiomatic_url_is_image($url) 
{
    $url = str_replace(' ', '%20', $url);

    $parsed_url = parse_url($url);
    if ($parsed_url === false || !isset($parsed_url['scheme'], $parsed_url['host'])) {
        return false;
    }

    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $encoded_path = implode('/', array_map('rawurlencode', explode('/', $path)));
    $encoded_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $encoded_path;
    if (isset($parsed_url['query'])) {
        $encoded_url .= '?' . $parsed_url['query'];
    }

    if (!filter_var($encoded_url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $host = strtolower($parsed_url['host']);
    $path_lower = strtolower($path);
    if ($host === 'drive.google.com') {
        parse_str($parsed_url['query'] ?? '', $q);
        if (!empty($q['id']) && ($path_lower === '/uc' || $path_lower === '/thumbnail')) {
            return true;
        }
    }

    $ext = ['jpeg','jpg','gif','png','jpe','tif','tiff','svg','ico','webp','dds','heic','psd','pspimage','tga','thm','yuv','ai','eps','bmp'];
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['php_images']) && $aiomatic_Main_Settings['php_images'] === 'on') {
        $ext[] = 'php';
    }

    $decoded_path = urldecode($path);
    setlocale(LC_ALL, 'en_US.UTF-8');
    $info = (array) pathinfo($decoded_path);

    if (!isset($info['extension'])) {
        return false;
    }

    return in_array(strtolower($info['extension']), $ext, true);
}
function aiomatic_custom_vision_upload_dir( $dir ) {
    return array(
        'path'   => $dir['basedir'] . '/ai-vision-images',
        'url'    => $dir['baseurl'] . '/ai-vision-images',
        'subdir' => '/ai-vision-images',
    ) + $dir;
}
function aiomatic_fatal_clear_job($job_id)
{
    $error = error_get_last();
    if ($error !== null && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR || $error['type'] === E_USER_ERROR)) 
    {
        aiomatic_job_set_status_failed($job_id, 'Job halted because of PHP error!');
    }
}
function aiomatic_job_set_status_pending($job_id, $data = array())
{
    if(strstr($job_id, 'job_') !== false)
    {
        delete_transient("aiomatic_job_" . $job_id . "_status");
        set_transient("aiomatic_job_" . $job_id . "_status", array('status' => 'pending', 'data' => $data), 24 * 60 * 60);
    }
}
function aiomatic_job_set_status_completed($job_id, $data)
{
    delete_transient("aiomatic_job_" . $job_id . "_status");
    set_transient("aiomatic_job_" . $job_id . "_status", array('status' => 'completed', 'data' => $data), 24 * 60 * 60);
}
function aiomatic_job_set_status_failed($job_id, $error)
{
    delete_transient("aiomatic_job_" . $job_id . "_status");
    set_transient("aiomatic_job_" . $job_id . "_status", array('status' => 'failed', 'data' => $error), 24 * 60 * 60);
}
function aiomatic_job_get_status($job_id)
{
    $status = get_transient("aiomatic_job_" . $job_id . "_status");
    if(isset($status['status']) && $status['status'] == 'completed')
    {
        delete_transient("aiomatic_job_" . $job_id . "_status");
    }
    return $status;
}
function aiomatic_extract_text_chars($text, $getLast = true, $limit = 500) 
{
    $textLength = strlen($text);
    if ($textLength <= $limit) 
    {
        return $text;
    } 
    else 
    {
        if ($getLast) 
        {
            $last = substr($text, -$limit);
            return $last;
        }
        else
        {
            $first = substr($text, 0, $limit);
            return $first;
        }
    }
}
function aiomatic_extract_paragraph($html, $getLast = true, $maxChars = 500) 
{
    $text = trim(strip_tags($html));
    if ($getLast) 
    {
        if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) 
        {
            $lastParagraph = end($matches[1]);
            $lastParagraph = trim(strip_tags($lastParagraph));
            $lastParagraph = substr($lastParagraph, 0, $maxChars);
            return $lastParagraph;
        }
    } 
    else 
    {
        if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) 
        {
            $firstParagraph = reset($matches[1]);
            $firstParagraph = trim(strip_tags($firstParagraph));
            $firstParagraph = substr($firstParagraph, 0, $maxChars);
            return $firstParagraph;
        }
    }
    if ($getLast) 
    {
        return substr($text, -min($maxChars, strlen($text)));
    } 
    else 
    {
        return substr($text, 0, min($maxChars, strlen($text)));
    }
}
function aiomatic_format_function_params($params) 
{
    $formattedParams = array();
    if(!is_array($params))
    {
        $params = array($params);
    }
    foreach ($params as $key => $value) 
    {
        if (is_string($value)) {
            $formattedParams[] = "$key => '" . addslashes($value) . "'";
        } elseif (is_array($value)) {
            $formattedParams[] = "$key => [" . aiomatic_format_function_params($value) . "]";
        } elseif (is_object($value)) {
            $formattedParams[] = "$key => '" . json_encode($value) . "'";
        } elseif (is_null($value)) {
            $formattedParams[] = "$key => null";
        } elseif (is_bool($value)) {
            $formattedParams[] = $value ? "$key => true" : "$key => false";
        } else {
            $formattedParams[] = "$key => $value";
        }
    }
    return implode(', ', $formattedParams);
}
class Aiomatic_Query_Parameter implements JsonSerializable 
{
    public $name;
    public $description;
    public $type;
    public $required;
    
    public function __construct( $name, $description, $type = "string", $required = false ) 
    {
        if ( !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $name) ) 
        {
            throw new InvalidArgumentException( "Invalid function name." );
        }
        $this->name = $name;
        $this->description = $description;
        $this->type = $type;
        $this->required = $required;
    }
    #[\ReturnTypeWillChange]
    public function jsonSerialize() 
    {
        $retz = [
            'type' => $this->type,
            'description' => $this->description
        ];
        if($this->type === 'array')
        {
            $retz['items'] = array('type' => 'string');
        }
        return $retz;
    }
}
class Aiomatic_Query_Function implements JsonSerializable 
{
    public $name;
    public $description;
    public $parameters;
    public $is_realtime;
    public function __construct( $name, $description, $parameters = [], $is_realtime = false) 
    {
        if ( !preg_match( '/^[a-zA-Z0-9_-]{1,64}$/', $name ) ) 
        {
            throw new InvalidArgumentException( "Invalid function name (" . esc_html($name) . "). It must be a-z, A-Z, 0-9, or contain underscores and dashes, with a maximum length of 64." );
        }
        foreach ( $parameters as $parameter ) 
        {
            if ( !( $parameter instanceof Aiomatic_Query_Parameter ) ) {
                throw new InvalidArgumentException( "Invalid parameter." );
            }
        }
        $this->name = $name;
        $this->description = $description;
        $this->parameters = $parameters;
        $this->is_realtime = $is_realtime;
    }
    #[\ReturnTypeWillChange]
    public function jsonSerialize() 
    {
        $params = [];
        foreach( $this->parameters as $parameter ) 
        {
            $params[ $parameter->name ] = $parameter;
        }
        $required = array_filter( $this->parameters, function( $param ) { return $param->required; } );
        $required = array_map( function( $param ) { return $param->name; }, $required );
        $json = [
            'name' => $this->name,
            'description' => $this->description,
            'parameters' => [
                'type' => 'object',
                'properties' => $params,
                'required' => $required
            ]
        ];
        if($this->is_realtime === true)
        {
            $json['type'] = 'function';
        }
        return $json;
    }
}
function aiomatic_gen_uid($l=6){
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $l);
}
function aiomatic_get_amazon_sorts()
{
    return array(
        'none' => esc_html__('None', 'aiomatic-automatic-ai-content-writer') , 
        'Relevance' => esc_html__('Relevance', 'aiomatic-automatic-ai-content-writer') , 
        'Price:LowToHigh' => esc_html__('Price:LowToHigh', 'aiomatic-automatic-ai-content-writer') ,  
        'Price:HighToLow' => esc_html__('Price:HighToLow', 'aiomatic-automatic-ai-content-writer') , 
        'NewestArrivals' => esc_html__('NewestArrivals', 'aiomatic-automatic-ai-content-writer') ,
        'Featured' => esc_html__('Featured', 'aiomatic-automatic-ai-content-writer') ,
        'AvgCustomerReviews' => esc_html__('AvgCustomerReviews', 'aiomatic-automatic-ai-content-writer')
    );
}
function aiomatic_get_amazon_codes()
{
    return array(
        'com' => esc_html__('United States', 'aiomatic-automatic-ai-content-writer') , 
        'co.uk' => esc_html__('United Kingdom', 'aiomatic-automatic-ai-content-writer') , 
        'ae' => esc_html__('United Arab Emirates', 'aiomatic-automatic-ai-content-writer') ,  
        'com.tr' => esc_html__('Turkey', 'aiomatic-automatic-ai-content-writer') , 
        'es' => esc_html__('Spain', 'aiomatic-automatic-ai-content-writer') ,
        'sg' => esc_html__('Singapore', 'aiomatic-automatic-ai-content-writer') ,
        'com.mx' => esc_html__('Mexico', 'aiomatic-automatic-ai-content-writer') , 
        'co.jp' => esc_html__('Japan', 'aiomatic-automatic-ai-content-writer') , 
        'it' => esc_html__('Italy', 'aiomatic-automatic-ai-content-writer') , 
        'ca' => esc_html__('Canada', 'aiomatic-automatic-ai-content-writer') , 
        'de' => esc_html__('Germany', 'aiomatic-automatic-ai-content-writer') , 
        'fr' => esc_html__('France', 'aiomatic-automatic-ai-content-writer') ,  
        'com.br' => esc_html__('Brasil', 'aiomatic-automatic-ai-content-writer') ,  
        'in' => esc_html__('India', 'aiomatic-automatic-ai-content-writer') , 
        'com.au' => esc_html__('Australia', 'aiomatic-automatic-ai-content-writer') , 
        'eg' => esc_html__('Egypt', 'aiomatic-automatic-ai-content-writer') , 
        'pl' => esc_html__('Poland', 'aiomatic-automatic-ai-content-writer') , 
        'sa' => esc_html__('Saudi Arabia', 'aiomatic-automatic-ai-content-writer') , 
        'se' => esc_html__('Sweden', 'aiomatic-automatic-ai-content-writer'), 
        'nl' => esc_html__('Netherlands', 'aiomatic-automatic-ai-content-writer')
    );
}
function aiomatic_addCharacterToVisibleText($html, $character) {
    $dom = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_use_internal_errors($internalErrors);
    $xpath = new DOMXPath($dom);
    $textNodes = $xpath->query('//text()');
    if($textNodes !== false)
    {
        foreach ($textNodes as $textNode) {
            $nodeValue = $textNode->nodeValue;
            $visibleText = trim($nodeValue);
            if (!empty($visibleText)) {
                $randomPosition = rand(0, 1);
                $newText = str_replace(' ', $character . ' ', $visibleText);
                if ($randomPosition == 0) {
                $newText = $character . $newText;
                } else {
                $newText .= $character;
                }
                $textNode->nodeValue = $newText;
            }
        }
    }
    $modifiedHtml = $dom->saveHTML();
    $modifiedHtml = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $modifiedHtml);
    if(!empty($modifiedHtml))
    {
        return $modifiedHtml;
    }
    return $html;
}
function aiomatic_compare_fill(&$measure, &$fill) 
{
    $zarr = [];
    foreach($measure as $mej)
    {
        $xmej = explode(',', $mej);
        foreach($xmej as $newm)
        {
            $zarr[] = $newm;
        }
    }
	if (count($zarr) != count($fill)) {
        while (count($fill) < count($zarr) ) {
		    $fill = array_merge( $fill, array_values($fill) );
		}
		$fill = array_slice($fill, 0, count($zarr));
	}
}

function aiomatic_my_user_by_rand( $ua ) {
    remove_action('pre_user_query', 'aiomatic_my_user_by_rand');
    $ua->query_orderby = str_replace( 'user_login ASC', 'RAND()', $ua->query_orderby );
}
function aiomatic_stringContainsArrayChars($string, $charsArray) {
    foreach ($charsArray as $char) {
        if (strpos($string, $char) !== false) {
            return true;
        }
    }
    return false;
}
function aiomatic_limitStringTo($input, $maxLength) 
{
    if (strlen($input) <= $maxLength) 
    {
        return $input;
    }
    return substr($input, -$maxLength);
}
function aiomatic_display_random_user(){
    add_action('pre_user_query', 'aiomatic_my_user_by_rand');
    $args = array(
      'orderby' => 'user_login', 'order' => 'ASC', 'number' => 1, 'role__in' => array( 'contributor','author','editor','administrator','super-admin' )
    );
    $user_query = new WP_User_Query( $args );
    $user_query->query();
    $results = $user_query->results;
    if(empty($results))
    {
        return false;
    }
    shuffle($results);
    return array_pop($results);
}
function aiomatic_replace_regional_variants($content, $toBritish = true, $retainOriginalProbability = 30) 
{
    $regional_variants = [
        'color' => 'colour',
        'flavor' => 'flavour',
        'honor' => 'honour',
        'neighbor' => 'neighbour',
        'labor' => 'labour',
        'behavior' => 'behaviour',
        'analyze' => 'analyse',
        'organize' => 'organise',
        'realize' => 'realise',
        'recognize' => 'recognise',
        'favorite' => 'favourite',
        'dialog' => 'dialogue',
        'catalog' => 'catalogue',
        'theater' => 'theatre',
        'meter' => 'metre',
        'center' => 'centre',
        'defense' => 'defence',
        'license' => 'licence',
        'offense' => 'offence',
        'practice' => 'practise', 
        'program' => 'programme',
        'jewelry' => 'jewellery',
        'gray' => 'grey',
        'tire' => 'tyre'
    ];
    foreach ($regional_variants as $us => $uk) {
        if ($toBritish) 
        {
            $replacement = (rand(1, 100) <= $retainOriginalProbability) ? $us : $uk;
            $content = aiomatic_replace_with_matched_case($us, $replacement, $content, 1);
        } else {
            $replacement = (rand(1, 100) <= $retainOriginalProbability) ? $uk : $us;
            $content = aiomatic_replace_with_matched_case($uk, $replacement, $content, 1);
        }
    }
    return $content;
}
function aiomatic_fetchDuckSERPResults($query, $numResults = 10) 
{
    $encodedQuery = urlencode($query);
    $url = "https://html.duckduckgo.com/html?q=$encodedQuery";
    $ch = curl_init();
    if($ch == false)
    {
        return array();
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.5735.199 Safari/537.36");
    $html = curl_exec($ch);
    curl_close($ch);
    if (!$html) 
    {
        return array();
    }
    $dom = new DOMDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_use_internal_errors($internalErrors);
    $xpath = new DOMXPath($dom);
    $results = [];
    $resultNodes = $xpath->query("//div[contains(@class, 'result results_links results_links_deep web-result')]");
    foreach ($resultNodes as $index => $resultNode) 
    {
        if ($index >= $numResults) 
        {
            break;
        }
        $titleNode = $xpath->query(".//h2[@class='result__title']/a", $resultNode);
        $title = ($titleNode->length > 0 && $titleNode->item(0) instanceof DOMElement) ? $titleNode->item(0)->nodeValue : '';
        $urlNode = $xpath->query(".//h2[@class='result__title']/a", $resultNode);
        /** @disregard P1013 because it is ok */
        $link = ($urlNode->length > 0 && $urlNode->item(0) instanceof DOMElement) ? $urlNode->item(0)->getAttribute('href') : '';
        if (strpos($link, '/l/?uddg=') !== false) 
        {
            parse_str(parse_url($link, PHP_URL_QUERY), $queryParams);
            $link = isset($queryParams['uddg']) ? urldecode($queryParams['uddg']) : $link;
        }
        $snippetNode = $xpath->query(".//a[@class='result__snippet']", $resultNode);
        $snippet = ($snippetNode->length > 0 && $snippetNode->item(0) instanceof DOMElement) ? $snippetNode->item(0)->nodeValue : '';
        $results[] = [
            'title'   => $title,
            'link'    => $link,
            'snippet' => $snippet,
        ];
    }
    return $results;
}
function aiomatic_make_unique($text, $characters, $percentage) 
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['no_synonym']) || $aiomatic_Main_Settings['no_synonym'] != 'on') 
    {
        $text = aiomatic_replace_synonyms($text, 30);
        $text = aiomatic_randomly_paraphrase_sentences($text, 30);
        $text = aiomatic_randomly_adjust_sentence_flow($text, 30);
        $text = aiomatic_randomly_nice_commas($text, 10);
    }
    if (!isset($aiomatic_Main_Settings['no_parasite']) || $aiomatic_Main_Settings['no_parasite'] != 'on') 
    {
        $text = aiomatic_remove_parasite_phrases($text);
    }
    if (!isset($aiomatic_Main_Settings['no_regional']) || $aiomatic_Main_Settings['no_regional'] != 'on') 
    {
        $text = aiomatic_replace_regional_variants($text, true, 60);
        $text = aiomatic_replace_regional_variants($text, false, 20);
    }
    if (!isset($aiomatic_Main_Settings['no_typing_error']) || $aiomatic_Main_Settings['no_typing_error'] != 'on') 
    {
        $text = aiomatic_simulate_typing_errors($text, 5);
        $text = aiomatic_add_lowercase_errors($text, 5);
    }
    if (!isset($aiomatic_Main_Settings['no_add_chars']) || $aiomatic_Main_Settings['no_add_chars'] != 'on') 
    {
        if(!is_array($characters) || empty($characters))
        {
            return $text;
        }
        $result = '';
        $htmlfounds = array();
        $pre_tags_matches = array();
        $pre_tags_matches_s = array();
        $conseqMatchs = array();
        $final_content_pre = aiomatic_replaceExcludes($text, $htmlfounds, $pre_tags_matches, $pre_tags_matches_s, $conseqMatchs);
        $textLength = strlen($final_content_pre);
        for ($i = 0; $i < $textLength; $i++) 
        {
            if ($final_content_pre[$i] == ' ' && rand(1, 100) <= $percentage) 
            {
                $character = $characters[array_rand($characters)];
                $randomPosition = rand(0, 1);
                if ($randomPosition == 0) {
                    $result .= $character . ' ';
                } 
                else 
                {
                    $result .= ' ' . $character;
                }
            } 
            else 
            {
                $result .= $final_content_pre[$i];
            }
        }
        if(empty($result))
        {
            return $text;
        }
        $result = aiomatic_restoreExcludes($result, $htmlfounds, $pre_tags_matches, $pre_tags_matches_s, $conseqMatchs);
        if(empty(trim($result)))
        {
            return $text;
        }
        $text = $result;
    }
    return $text;
}
function aiomatic_randomly_paraphrase_sentences($content, $probability) 
{
    $sentences = explode('.', $content);
    foreach ($sentences as &$sentence) {
        if (rand(1, 100) <= $probability) {
            $sentence = aiomatic_paraphrase_sentence($sentence);
        }
    }
    return implode('.', $sentences);
}
function aiomatic_randomly_adjust_sentence_flow($content, $probability) 
{
    $sentences = explode('.', $content);
    foreach ($sentences as &$sentence) {
        if (rand(1, 100) <= $probability) {
            $sentence = aiomatic_adjust_sentence_flow($sentence);
        }
    }
    return implode('.', $sentences);
}
function aiomatic_randomly_nice_commas($content, $probability) 
{
    $sentences = explode('.', $content);
    foreach ($sentences as &$sentence) {
        if (rand(1, 100) <= $probability) {
            $sentence = aiomatic_remove_nice_commas($sentence);
        }
        if (rand(1, 100) <= $probability) {
            $sentence = aiomatic_spaces_after_points($sentence);
        }
    }
    return implode('.', $sentences);
}
function aiomatic_remove_nice_commas($content) 
{
    return str_replace(', ', ',', $content);
}
function aiomatic_spaces_after_points($content) 
{
    return trim($content);
}
function aiomatic_adjust_sentence_flow($content) 
{
    $patterns = [
        "/\b(because)\b/i" => "as",
        "/\b(however)\b/i" => "though",
        "/\b(therefore)\b/i" => "thus",
        "/\b(although)\b/i" => "even though",
        "/\b(furthermore)\b/i" => "moreover",
        "/\b(nevertheless)\b/i" => "still",
        "/\b(instead)\b/i" => "rather",
        "/\b(otherwise)\b/i" => "or else",
        "/\b(since)\b/i" => "as",
        "/\b(hence)\b/i" => "so",
        "/\b(as a result)\b/i" => "consequently",
        "/\b(in contrast)\b/i" => "on the contrary",
        "/\b(meanwhile)\b/i" => "at the same time",
        "/\b(in other words)\b/i" => "put simply",
        "/\b(to sum up)\b/i" => "in summary",
        "/\b(instead of)\b/i" => "rather than",
        "/\b(on the contrary)\b/i" => "in contrast",
        "/\b(for this reason)\b/i" => "because of this",
        "/\b(as long as)\b/i" => "provided that",
        "/\b(in fact)\b/i" => "actually",
        "/\b(to this end)\b/i" => "for this purpose",
        "/\b(it is)\b/i" => "it is indeed",
        "/\b(we know)\b/i" => "we certainly know",
        "/\b(important)\b/i" => "critically important",
        "/\b(a good idea)\b/i" => "an excellent idea",
        "/\b(can help)\b/i" => "can definitely help",
        "/\b(may be)\b/i" => "might potentially be",
        "/\b(is possible)\b/i" => "is absolutely possible",
        "/\b(it seems)\b/i" => "it truly seems",
        "/\b(often)\b/i" => "frequently enough",
        "/\b(it works)\b/i" => "it effectively works",
        "/\b(for sure)\b/i" => "without a doubt",
        "/\b(as we know)\b/i" => "as we are well aware",
        "/\b(it could be)\b/i" => "it might very well be",
        "/\b(is likely)\b/i" => "is highly likely",
        "/\b(as a result)\b/i" => "as an inevitable result",
        "/\b(for instance)\b/i" => "as a notable example",
        "/\b(by the way)\b/i" => "incidentally speaking",
        "/\b(sooner or later)\b/i" => "eventually, sooner or later",
        "/\b(as far as I know)\b/i" => "to the best of my knowledge",
        "/\b(that said)\b/i" => "having mentioned that",
        "/\b(it's worth noting)\b/i" => "it's certainly worth noting",
        "/\b(in my opinion)\b/i" => "from my personal perspective",
        "/\b(as expected)\b/i" => "as was to be expected",
        "/\b(no doubt)\b/i" => "there's no doubt whatsoever",
        "/\b(in some cases)\b/i" => "in certain specific cases",
        "/\b(on occasion)\b/i" => "from time to time",
        "/\b(that being said)\b/i" => "with that in mind",
        "/\b(as such)\b/i" => "in this very way",
        "/\b(for this reason)\b/i" => "because of this very reason",
        "/\b(to a certain extent)\b/i" => "to some measurable degree",
        "/\b(in other words)\b/i" => "simply put",
        "/\b(needless to say)\b/i" => "it goes without saying",
        "/\b(at the same time)\b/i" => "simultaneously occurring",
        "/\b(for the most part)\b/i" => "generally speaking",
    ];

    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace_callback($pattern, function ($matches) use ($replacement) 
        {
            return aiomatic_match_case($matches[0], $replacement);
        }, $content, 1); 
    }

    return $content;
}
function aiomatic_paraphrase_sentence($content) {
    $patterns = [
        "/\b(for example)\b/i" => "such as",
        "/\b(in addition to)\b/i" => "along with",
        "/\b(it is important to note that)\b/i" => "notably",
        "/\b(as a result)\b/i" => "consequently",
        "/\b(on the other hand)\b/i" => "conversely",
        "/\b(in order to)\b/i" => "to",
        "/\b(due to the fact that)\b/i" => "because",
        "/\b(as well as)\b/i" => "and also",
        "/\b(in light of)\b/i" => "considering",
        "/\b(at the end of the day)\b/i" => "ultimately",
        "/\b(for the purpose of)\b/i" => "to",
        "/\b(in spite of)\b/i" => "despite",
        "/\b(as a matter of fact)\b/i" => "actually",
        "/\b(with regard to)\b/i" => "regarding",
        "/\b(it goes without saying that)\b/i" => "obviously",
        "/\b(in other words)\b/i" => "simply put",
        "/\b(for instance)\b/i" => "as an example",
        "/\b(in conclusion)\b/i" => "to sum up",
        "/\b(to put it another way)\b/i" => "in other terms",
        "/\b(in the event that)\b/i" => "if",
        "/\b(as soon as possible)\b/i" => "quickly",
        "/\b(on account of)\b/i" => "because of",
        "/\b(as far as I am concerned)\b/i" => "in my opinion",
        "/\b(sooner or later)\b/i" => "eventually",
        "/\b(to a certain extent)\b/i" => "partially",
        "/\b(to make a long story short)\b/i" => "in summary",
        "/\b(by means of)\b/i" => "through",
        "/\b(in the same way)\b/i" => "likewise",
        "/\b(under no circumstances)\b/i" => "never",
        "/\b(for the time being)\b/i" => "temporarily",
        "/\b(by the way)\b/i" => "incidentally",
        "/\b(in the meantime)\b/i" => "meanwhile",
        "/\b(it appears that)\b/i" => "it seems",
        "/\b(to the best of my knowledge)\b/i" => "as far as I know",
    ];
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace_callback($pattern, function ($matches) use ($replacement) 
        {
            return aiomatic_match_case($matches[0], $replacement);
        }, $content, 1); 
    }
    return $content;
}
function aiomatic_replace_synonyms($content, $retainOriginalProbability = 30) 
{
    $synonyms = [
        'quick' => ['fast', 'speedy', 'swift', 'rapid'],
        'beautiful' => ['gorgeous', 'stunning', 'lovely'],
        'friendly' => ['kind', 'amiable', 'pleasant'],
        'bright' => ['luminous', 'radiant', 'shining'],
        'intelligent' => ['smart', 'clever', 'bright'],
        'wonderful' => ['marvelous', 'fantastic', 'splendid'],
        'difficult' => ['challenging', 'arduous', 'tough'],
        'important' => ['significant', 'crucial', 'vital'],
        'delicious' => ['tasty', 'appetizing', 'flavorful'],
        'magnificent' => ['grand', 'majestic', 'splendid'],
        'enthusiastic' => ['eager', 'keen', 'excited'],
        'interesting' => ['fascinating', 'engaging', 'captivating'],
        'complicated' => ['complex', 'intricate', 'elaborate'],
        'successful' => ['accomplished', 'triumphant', 'prosperous'],
        'exhausted' => ['tired', 'weary', 'fatigued'],
        'fortunate' => ['lucky', 'blessed'],
        'immediately' => ['instantly', 'promptly'],
        'extraordinary' => ['remarkable', 'exceptional', 'unusual'],
        'fascinating' => ['interesting', 'captivating', 'engaging'],
        'spectacular' => ['impressive', 'stunning', 'breathtaking'],
        'unbelievable' => ['incredible', 'astonishing', 'amazing'],
        'courageous' => ['brave', 'valiant', 'heroic'],
        'comprehensive' => ['thorough', 'complete', 'extensive'],
        'significant' => ['important', 'notable', 'meaningful'],
        'unnecessary' => ['needless', 'unneeded'],
        'impossible' => ['unfeasible', 'unachievable', 'impractical'],
        'incredible' => ['unbelievable', 'astonishing', 'amazing'],
        'comfortable' => ['cozy', 'pleasant', 'agreeable'],
        'determined' => ['resolute', 'steadfast', 'persistent'],
        'impressive' => ['remarkable', 'notable', 'extraordinary'],
        'knowledgeable' => ['informed', 'educated', 'learned'],
        'substantial' => ['significant', 'considerable', 'ample'],
        'unfortunately' => ['regrettably', 'sadly', 'unluckily'],
        'improvement' => ['enhancement', 'advancement', 'betterment'],
        'astonishing' => ['amazing', 'astounding', 'remarkable'],
        'environment' => ['surroundings', 'habitat'],
        'application' => ['request', 'submission'],
        'communication' => ['interaction', 'dialogue'],
        'information' => ['data', 'facts', 'details'],
        'explanation' => ['clarification', 'description'],
        'opportunity' => ['chance', 'possibility', 'prospect'],
        'investigation' => ['inquiry', 'examination'],
        'demonstration' => ['exhibition', 'presentation'],
        'development' => ['growth', 'progress', 'advancement'],
        'appreciation' => ['gratitude', 'thankfulness', 'recognition'],
        'organization' => ['association', 'institution'],
        'recommendation' => ['suggestion', 'advice', 'proposal'],
        'representation' => ['depiction', 'portrayal'],
        'administration' => ['management', 'governance'],
        'transformation' => ['conversion', 'change'],
        'announcement' => ['declaration', 'proclamation'],
        'responsibility' => ['duty', 'obligation'],
        'advantageous' => ['beneficial', 'favorable'],
        'sophisticated' => ['refined', 'elegant', 'complex'],
        'subsequently' => ['afterward', 'later'],
        'independent' => ['autonomous', 'self-reliant'],
        'significantly' => ['considerably', 'substantially'],
        'particularly' => ['especially', 'notably'],
        'extraordinarily' => ['remarkably', 'exceptionally'],
        'fundamental' => ['basic', 'essential'],
        'imagination' => ['creativity', 'inventiveness'],
        'completely' => ['entirely', 'wholly', 'fully'],
        'universal' => ['global', 'worldwide'],
        'historical' => ['past', 'ancient'],
        'traditional' => ['conventional', 'customary'],
        'simultaneously' => ['concurrently', 'together'],
        'ambitious' => ['aspiring', 'enterprising'],
        'deliberate' => ['intentional', 'purposeful'],
        'transparent' => ['clear', 'obvious'],
        'consequence' => ['result', 'outcome'],
        'transparency' => ['clarity', 'openness'],
        'inevitable' => ['unavoidable', 'certain'],
        'philosophy' => ['ideology', 'beliefs'],
        'fascination' => ['interest', 'captivation'],
        'preparation' => ['readiness', 'planning'],
        'significance' => ['importance', 'meaning'],
        'alternative' => ['option', 'choice'],
        'perspective' => ['viewpoint', 'outlook'],
        'discovery' => ['finding', 'revelation'],
        'flexibility' => ['adaptability', 'versatility'],
        'celebration' => ['festivity', 'party'],
        'exceptional' => ['extraordinary', 'remarkable'],
        'regardless' => ['irrespective', 'nonetheless'],
        'sustainable' => ['enduring', 'lasting'],
        'potentially' => ['possibly', 'perhaps'],
        'dangerous' => ['hazardous', 'perilous', 'risky'],
        'cowardly' => ['timid', 'fearful', 'fainthearted'],
        'delicious' => ['tasty', 'appetizing', 'flavorful'],
        'disgusting' => ['revolting', 'repulsive', 'nauseating'],
        'hostile' => ['unfriendly', 'antagonistic', 'opposed'],
        'unimportant' => ['insignificant', 'trivial', 'minor'],
    ];
    
    foreach ($synonyms as $word => $replacements) {
        if (!in_array($word, $replacements)) {
            $replacements[] = $word;
        }
        if (rand(1, 100) <= $retainOriginalProbability) {
            continue;
        }
        $replacement = $replacements[array_rand($replacements)];
        $content = aiomatic_replace_with_matched_case($word, $replacement, $content, 1);
    }
    return $content;
}
function aiomatic_match_case($original, $replacement) {
    if (ctype_upper($original)) {
        return strtoupper($replacement);
    } elseif (ctype_lower($original)) {
        return strtolower($replacement);
    } elseif (ucfirst(strtolower($original)) === $original) {
        return ucfirst(strtolower($replacement));
    } else {
        return $replacement;
    }
}
function aiomatic_add_lowercase_errors($content, $errorProbability = 10) 
{
    $pattern = "/\b[A-Z][a-zA-Z]*[a-z][a-zA-Z]*\b/";

    return preg_replace_callback($pattern, function ($matches) use ($errorProbability) 
    {
        if (rand(1, 100) <= $errorProbability) {
            return strtolower($matches[0]);
        }
        return $matches[0];
    }, $content);
}
function aiomatic_simulate_typing_errors($content, $errorProbability = 5)
{
    $typos = [
        'the' => 'teh',
        'and' => 'adn',
        'receive' => 'recieve',
        'definitely' => 'definately',
        'separate' => 'seperate',
        'occurred' => 'occured',
        'accommodation' => 'accomodation',
        'believe' => 'beleive',
        'friend' => 'freind',
        'immediately' => 'immediatly',
        'necessary' => 'neccessary',
        'tomorrow' => 'tommorow',
        'business' => 'buisness',
        'beginning' => 'begining',
        'experience' => 'experiance',
        'grammar' => 'grammer',
        'independent' => 'independant',
        'definite' => 'definate',
        'seize' => 'sieze',
        'weird' => 'wierd',
        'unfortunately' => 'unfortunatly',
        'February' => 'Febuary',
        'library' => 'libary',
        'government' => 'goverment',
        'restaurant' => 'resturant',
        'maintenance' => 'maintainance',
        'embarrass' => 'embarass',
        'privilege' => 'priviledge',
        'conscience' => 'concious',
        'rhythm' => 'rythm',
        'occurrence' => 'occurence',
        'committee' => 'commitee',
        'misspell' => 'mispell',
        'harass' => 'harrass',
        'possession' => 'posession',
        'recommend' => 'reccommend',
        'their' => 'thier',
        'there' => 'ther',
        'they' => 'tehy',
        'could' => 'coudl',
        'would' => 'woudl',
        'should' => 'shoudl',
        'which' => 'wich',
        'with' => 'wiht',
        'your' => 'yoru',
        'you\'re' => 'your',
        'it\'s' => 'its',
        'its' => "it's",
        'than' => 'then',
        'then' => 'than',
        'to' => 'too',
        'too' => 'to',
        'lose' => 'loose',
        'loose' => 'lose',
        'effect' => 'affect',
        'affect' => 'effect',
        'accept' => 'except',
        'except' => 'accept',
        'alot' => 'a lot',
        'weather' => 'whether',
        'whether' => 'weather',
        'quiet' => 'quite',
        'quite' => 'quiet',
        'lead' => 'led',
        'led' => 'lead',
        'supposed' => 'suppose',
        'suppose' => 'supposed',
        'through' => 'thru',
        'though' => 'tho',
        'thought' => 'thoght',
        'because' => 'becuase',
        'people' => 'peopel',
        'really' => 'realy',
        'probably' => 'problably',
        'does' => 'dose',
        'different' => 'diffrent',
        'important' => 'importent',
        'know' => 'no',
        'knowledge' => 'knowlege',
        'college' => 'collage',
        'beautiful' => 'beatiful',
        'country' => 'contry',
        'example' => 'exmaple',
        'finally' => 'finaly',
        'first' => 'frist',
        'great' => 'grate',
        'hear' => 'here',
        'here' => 'hear',
        'many' => 'manny',
        'mean' => 'meen',
        'most' => 'moast',
        'necessary' => 'necesary',
        'often' => 'ofen',
        'once' => 'onc',
        'only' => 'onyl',
        'other' => 'othre',
        'said' => 'saeid',
        'sentence' => 'sentance',
        'since' => 'sence',
        'something' => 'somthing',
        'sometimes' => 'sometiems',
        'these' => 'thes',
        'they' => 'thay',
        'until' => 'untill',
        'very' => 'vrey',
        'want' => 'wont',
        'went' => 'whent',
        'were' => 'where',
        'where' => 'were',
        'write' => 'wriet',
        'writing' => 'writng',
        'written' => 'writen',
        'unknown' => 'unkown',
        '\'s' => 's',
    ];
    foreach ($typos as $correct => $typo) 
    {
        if (rand(1, 100) <= $errorProbability) 
        {
            $content = aiomatic_replace_with_matched_case($correct, $typo, $content, 1);
        }
    }
    return $content;
}

function aiomatic_replace_with_matched_case($searchWord, $replacementWord, $content, $limit = -1) 
{
    $pattern = "/\b" . preg_quote($searchWord, '/') . "\b/i";
    $content = preg_replace_callback($pattern, function($matches) use ($replacementWord) {
        $originalWord = $matches[0];
        $newWord = aiomatic_match_case($originalWord, $replacementWord);
        return $newWord;
    }, $content, $limit);
    return $content;
}

function aiomatic_randomName() {
    $firstname = array(
        'Johnathon',
        'Anthony',
        'Erasmo',
        'Raleigh',
        'Nancie',
        'Tama',
        'Camellia',
        'Augustine',
        'Christeen',
        'Luz',
        'Diego',
        'Lyndia',
        'Thomas',
        'Georgianna',
        'Leigha',
        'Alejandro',
        'Marquis',
        'Joan',
        'Stephania',
        'Elroy',
        'Zonia',
        'Buffy',
        'Sharie',
        'Blythe',
        'Gaylene',
        'Elida',
        'Randy',
        'Margarete',
        'Margarett',
        'Dion',
        'Tomi',
        'Arden',
        'Clora',
        'Laine',
        'Becki',
        'Margherita',
        'Bong',
        'Jeanice',
        'Qiana',
        'Lawanda',
        'Rebecka',
        'Maribel',
        'Tami',
        'Yuri',
        'Michele',
        'Rubi',
        'Larisa',
        'Lloyd',
        'Tyisha',
        'Samatha',
    );

    $lastname = array(
        'Mischke',
        'Serna',
        'Pingree',
        'Mcnaught',
        'Pepper',
        'Schildgen',
        'Mongold',
        'Wrona',
        'Geddes',
        'Lanz',
        'Fetzer',
        'Schroeder',
        'Block',
        'Mayoral',
        'Fleishman',
        'Roberie',
        'Latson',
        'Lupo',
        'Motsinger',
        'Drews',
        'Coby',
        'Redner',
        'Culton',
        'Howe',
        'Stoval',
        'Michaud',
        'Mote',
        'Menjivar',
        'Wiers',
        'Paris',
        'Grisby',
        'Noren',
        'Damron',
        'Kazmierczak',
        'Haslett',
        'Guillemette',
        'Buresh',
        'Center',
        'Kucera',
        'Catt',
        'Badon',
        'Grumbles',
        'Antes',
        'Byron',
        'Volkman',
        'Klemp',
        'Pekar',
        'Pecora',
        'Schewe',
        'Ramage',
    );

    $name = $firstname[rand ( 0 , count($firstname) -1)];
    $name .= ' ';
    $name .= $lastname[rand ( 0 , count($lastname) -1)];

    return $name;
}
function aiomatic_get_transients_by_regex($pattern, $users_per_page, &$trnsi_cnt) 
{
    $transi_count = 0;
    global $wpdb;
    $pattern = $wpdb->esc_like($pattern);
    $sql = $wpdb->prepare(
        "SELECT option_name
        FROM $wpdb->options
        WHERE option_name REGEXP %s",
        '^_transient_' . $pattern
    );

    $transients = $wpdb->get_col($sql);

    $transient_values = array();
    foreach ($transients as $transient) 
    {
        $transient_name = str_replace('_transient_', '', $transient);
        $transient_value = get_transient($transient_name);
        if ($transient_value !== false) 
        {
            if(count($transient_values) < $users_per_page)
            {
                $transient_values[$transient_name] = $transient_value;
            }
            $transi_count++;
        }
    }
    $trnsi_cnt = $transi_count;
    return $transient_values;
}
function aiomatic_log_exec_time($context)
{
    if(function_exists('phpinfo'))
    {
        ob_start();
        phpinfo();
        $phpinfoOutput = ob_get_clean();
        $timeoutPattern = '#max_execution_time<\/td><td class="v">([^<]+)<#i';
        if (preg_match_all($timeoutPattern, $phpinfoOutput, $matches)) {
            $maxExecutionTime = intval($matches[1][0]);
            aiomatic_log_to_file("[" . $context . "] Starting execution, setting max_execution_time: " . $maxExecutionTime . " seconds");
        } else {
            $timeoutPattern = '#max_execution_time\s*=>\s*(?:\d+)\s*=>\s*(\d+)#i';
            if (preg_match_all($timeoutPattern, $phpinfoOutput, $matches)) {
                $maxExecutionTime = intval($matches[1][0]);
                aiomatic_log_to_file("[" . $context . "] Starting scheduled execution, setting max_execution_time: " . $maxExecutionTime . " seconds");
            } else {
                aiomatic_log_to_file("[" . $context . "] Max Execution Time (ini_set) not found");
            }
        }
    }
}
function aiomatic_removeUrlParameter($url, $paramKey) {
    $parsedUrl = parse_url($url);

    if (isset($parsedUrl['query'])) 
    {
        parse_str($parsedUrl['query'], $params);
        if(isset($params[$paramKey]))
        {
            unset($params[$paramKey]);
        }
        $query = http_build_query($params);
        $updatedUrl = $parsedUrl['path'] . '?' . $query;
    } 
    else 
    {
        $updatedUrl = $url;
    }

    return $updatedUrl;
}
function aiomatic_get_the_user_ip() {

    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) 
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) 
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else 
    {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return apply_filters( 'aiomatic_get_ip', $ip );
}
function aiomatic_test_post_reponse() {

	$post_response = wp_safe_remote_post(
		'https://www.paypal.com/cgi-bin/webscr',
		array(
			'timeout'     => 60,
			'httpversion' => '1.1',
			'body'        => array(
				'cmd' => '_notify-validate',
			),
		)
	);


	if ( ! is_wp_error( $post_response ) && $post_response['response']['code'] >= 200 && $post_response['response']['code'] < 300 ) {
		return true;
	}

	return false;
}

function aiomatic_test_get_reponse() {

	$get_response = wp_safe_remote_get( 'https://woocommerce.com/wc-api/product-key-api?request=ping&network=' . ( is_multisite() ? '1' : '0' ) );

	if ( ! is_wp_error( $get_response ) && $get_response['response']['code'] >= 200 && $get_response['response']['code'] < 300 ) {
		return true;
	}

	return false;
}
function aiomatic_check_cron_status() {
	global $wp_version;

	if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
		/* translators: 1: The name of the PHP constant that is set. */
		return new WP_Error( 'crontrol_info', sprintf( __( 'The %s constant is set to true. WP-Cron spawning is disabled.', 'aiomatic-automatic-ai-content-writer' ), 'DISABLE_WP_CRON' ) );
	}

	if ( defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON ) {
		/* translators: 1: The name of the PHP constant that is set. */
		return new WP_Error( 'crontrol_info', sprintf( __( 'The %s constant is set to true.', 'aiomatic-automatic-ai-content-writer' ), 'ALTERNATE_WP_CRON' ) );
	}

	$cached_status = get_transient( 'wpcp-cron-test-ok' );

	if ( $cached_status ) {
		return true;
	}

	$sslverify     = version_compare( $wp_version, 4.0, '<' );
	$doing_wp_cron = sprintf( '%.22F', microtime( true ) );

	$cron_request = apply_filters( 'cron_request', array(
		'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
		'key'  => $doing_wp_cron,
		'args' => array(
			'timeout'   => 30,
			'blocking'  => true,
			'sslverify' => apply_filters( 'https_local_ssl_verify', $sslverify ),
		),
	) );

	$cron_request['args']['blocking'] = true;

	$result = wp_remote_post( $cron_request['url'], $cron_request['args'] );

	if ( is_wp_error( $result ) ) {
		return $result;
	} elseif ( wp_remote_retrieve_response_code( $result ) >= 300 ) {
		return new WP_Error( 'unexpected_http_response_code', sprintf(
		/* translators: 1: The HTTP response code. */
			__( 'Unexpected HTTP response code: %s', 'aiomatic-automatic-ai-content-writer' ),
			intval( wp_remote_retrieve_response_code( $result ) )
		) );
	} else {
		set_transient( 'wpcp-cron-test-ok', 1, 3600 );

		return true;
	}

}
function aiomatic_let_to_num( $size ) {
    $l   = substr( $size, - 1 );
    $ret = substr( $size, 0, - 1 );
    switch ( strtoupper( $l ) ) {
        case 'P':
            $ret *= 1024;
        case 'T':
            $ret *= 1024;
        case 'G':
            $ret *= 1024;
        case 'M':
            $ret *= 1024;
        case 'K':
            $ret *= 1024;
    }

    return $ret;
}
function aiomatic_substr($prompt, $start, $len = null)
{
    if(function_exists('mb_substr'))
    {
        $prompt = mb_substr($prompt, $start, $len, 'UTF-8');
    }
    else
    {
        $prompt = substr($prompt, $start, $len);
    }
    return $prompt;
}
function aiomatic_seo_plugins_active()
{
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $seo_plugin_activated = false;
    if(is_plugin_active('wordpress-seo/wp-seo.php')){
        $seo_plugin_activated = '_yoast_wpseo_metadesc';
    }
    elseif(is_plugin_active('all-in-one-seo-pack/all_in_one_seo_pack.php')){
        $seo_plugin_activated = '_aioseo_description';
    }
    elseif(is_plugin_active('seo-by-rank-math/rank-math.php')){
        $seo_plugin_activated = 'rank_math_description';
    }
    elseif(is_plugin_active('autodescription/autodescription.php')){
        $seo_plugin_activated = '_genesis_description';
    }
    elseif(is_plugin_active('wp-seopress/seopress.php')){
        $seo_plugin_activated = '_seopress_titles_desc';
    }
    elseif(is_plugin_active('squirrly-seo/squirrly.php')){
        $seo_plugin_activated = '_sq_description';
    }
    return $seo_plugin_activated;
}
function aiomatic_save_seo_description($post_id, $description, $aiomatic_focus_keyword = '')
{
    global $wpdb;
    if(empty($description) && empty($aiomatic_focus_keyword))
    {
        return;
    }
    $seo_plugin_activated = aiomatic_seo_plugins_active();
    if($seo_plugin_activated == '_yoast_wpseo_metadesc')
    {
        if(!empty($description))
        {
            update_post_meta($post_id, $seo_plugin_activated, $description);
        }
        if(!empty($aiomatic_focus_keyword))
        {
            update_post_meta($post_id, '_yoast_wpseo_focuskw', $aiomatic_focus_keyword);
        }
    }
    elseif($seo_plugin_activated == '_sq_description')
    {
        if(!empty($description))
        {
            update_post_meta($post_id, $seo_plugin_activated, $description);
        }
        if(!empty($aiomatic_focus_keyword))
        {
            update_post_meta($post_id, '_sq_keywords', $aiomatic_focus_keyword);
        }
    }
    elseif($seo_plugin_activated == '_aioseo_description')
    {
        if(!empty($description))
        {
            update_post_meta($post_id, $seo_plugin_activated, $description);
            $check = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."aioseo_posts WHERE post_id=%d",$post_id));
            if($check)
            {
                $wpdb->update($wpdb->prefix.'aioseo_posts',array(
                    'description' => sanitize_text_field($description)
                ), array(
                    'post_id' => $post_id
                ));
            }
            else{
                $wpdb->insert($wpdb->prefix.'aioseo_posts',array(
                    'post_id' => $post_id,
                    'description' => sanitize_text_field($description),
                    'created' => date('Y-m-d H:i:s'),
                    'updated' => date('Y-m-d H:i:s')
                ));
            }
        }
        if(!empty($aiomatic_focus_keyword))
        {
            update_post_meta($post_id, '_aioseop_keywords', $aiomatic_focus_keyword);
        }
    }
    elseif($seo_plugin_activated == 'rank_math_description')
    {
        if(!empty($description))
        {
            update_post_meta($post_id, $seo_plugin_activated, $description);
        }
        if(!empty($aiomatic_focus_keyword))
        {
            update_post_meta($post_id, 'rank_math_focus_keyword', $aiomatic_focus_keyword);
        }
    }
    elseif($seo_plugin_activated == '_genesis_description')
    {
        if(!empty($description))
        {
            update_post_meta($post_id, $seo_plugin_activated, $description);
        }
    }
    elseif($seo_plugin_activated == '_seopress_titles_desc')
    {
        if(!empty($description))
        {
            update_post_meta($post_id, $seo_plugin_activated, $description);
        }
        if(!empty($aiomatic_focus_keyword))
        {
            update_post_meta($post_id, '_seopress_analysis_target_kw', $aiomatic_focus_keyword);
        }
    }
    elseif($seo_plugin_activated == false)
    {
        if(!empty($description))
        {
            $seo_plugin_activated = 'aiomatic_html_meta';
            update_post_meta($post_id, $seo_plugin_activated, $description);
        }
    }
}
function aiomatic_check_seo_description($post_id)
{
    $seo_plugin_activated = aiomatic_seo_plugins_active();
    if($seo_plugin_activated == '_yoast_wpseo_metadesc' || $seo_plugin_activated == '_sq_description' || $seo_plugin_activated == '_aioseo_description' || $seo_plugin_activated == 'rank_math_description' || $seo_plugin_activated == '_genesis_description' || $seo_plugin_activated == '_seopress_titles_desc')
    {
        $seo_description = get_post_meta($post_id, $seo_plugin_activated, true);
        if(!empty($seo_description))
        {
            return true;
        }
    }
    elseif($seo_plugin_activated == false)
    {
        $seo_plugin_activated = 'aiomatic_html_meta';
        $seo_description = get_post_meta($post_id, $seo_plugin_activated, true);
        if(!empty($seo_description))
        {
            return true;
        }
    }
    return false;
}
function aiomatic_change_post_status($post_id, $status){
    $current_post = get_post( $post_id, 'ARRAY_A' );
    $current_post['post_status'] = $status;
    remove_filter('content_save_pre', 'wp_filter_post_kses');
    remove_filter('content_filtered_save_pre', 'wp_filter_post_kses');remove_filter('title_save_pre', 'wp_filter_kses');
    wp_update_post($current_post);
    add_filter('content_save_pre', 'wp_filter_post_kses');
    add_filter('content_filtered_save_pre', 'wp_filter_post_kses');add_filter('title_save_pre', 'wp_filter_kses');
}
function aiomatic_save_term_seo_description($term_id, $description, $taxonomy)
{
    if(empty($description))
    {
        return;
    }
    $seo_plugin_activated = aiomatic_seo_plugins_active();
    if($seo_plugin_activated == '_yoast_wpseo_metadesc')
    {
        $yoast_options = get_option('wpseo_taxonomy_meta');
        $yoast_options[$taxonomy][$term_id]['wpseo_desc'] = $description;
        aiomatic_update_option('wpseo_taxonomy_meta', $yoast_options);
    }
    elseif($seo_plugin_activated == '_sq_description')
    {
        update_term_meta( $term_id, '_sq_description', $description );
    }
    elseif($seo_plugin_activated == '_aioseo_description')
    {
        update_term_meta($term_id, '_aioseop_description', $description);
    }
    elseif($seo_plugin_activated == 'rank_math_description')
    {
        update_term_meta($term_id, 'rank_math_description', $description);
    }
    elseif($seo_plugin_activated == '_genesis_description')
    {
        update_term_meta($term_id, $seo_plugin_activated, $description);
    }
    elseif($seo_plugin_activated == '_seopress_titles_desc')
    {
        update_term_meta($term_id, $seo_plugin_activated, $description);
    }
    elseif($seo_plugin_activated == false)
    {
        $seo_plugin_activated = 'aiomatic_html_meta';
        update_term_meta($term_id, $seo_plugin_activated, $description);
    }
}
function aiomatic_save_seo_title($post_id, $title)
{
    if (empty($title)) {
        return;
    }
    $seo_plugin_activated = aiomatic_seo_plugins_active();

    if ($seo_plugin_activated == '_yoast_wpseo_metadesc') {
        update_post_meta($post_id, '_yoast_wpseo_title', $title);
    } 
    elseif ($seo_plugin_activated == '_aioseo_description') {
        update_post_meta($post_id, '_aioseo_title', $title);
    } 
    elseif ($seo_plugin_activated == '_sq_description') {
        update_post_meta($post_id, '_sq_title', $title);
    } 
    elseif ($seo_plugin_activated == 'rank_math_description') {
        update_post_meta($post_id, 'rank_math_title', $title);
    } 
    elseif ($seo_plugin_activated == '_genesis_description') {
        update_post_meta($post_id, '_genesis_title', $title);
    } 
    elseif ($seo_plugin_activated == '_seopress_titles_desc') {
        update_post_meta($post_id, '_seopress_titles_title', $title);
    } 
    elseif ($seo_plugin_activated == false) {
        update_post_meta($post_id, 'aiomatic_html_meta_title', $title);
    }
}
function aiomatic_get_random_word($min = 4, $max = 10) 
{
    $word = array_merge(range('a', 'z'), range('A', 'Z'));
    shuffle($word);
    $len = rand($min, $max);
    return substr(implode($word), 0, $len);
}
function aiomatic_save_lead_data($email, $name = '', $phone_number = '', $job_title = '', $company_name = '', $location = '', $birth_date = '', $how_you_found_us = '', $website_url = '', $preferred_contact_method = '') 
{
    if ( empty( $email ) ) 
    {
        return false; 
    }
    $args = array(
        'post_type'  => 'aiomatic_lead',
        'title'      => $email,
        'post_status'=> 'publish',
        'numberposts'=> 1,
        'fields'     => 'ids',
    );
    $existing_leads = get_posts( $args );
    $meta_fields = array(
        'name'                     => $name,
        'phone_number'             => $phone_number,
        'job_title'                => $job_title,
        'company_name'             => $company_name,
        'location'                 => $location,
        'birth_date'               => $birth_date,
        'how_you_found_us'         => $how_you_found_us,
        'website_url'              => $website_url,
        'preferred_contact_method' => $preferred_contact_method,
    );

    if ( !empty( $existing_leads ) ) 
    {
        $lead_id = $existing_leads[0];
        foreach ( $meta_fields as $key => $value ) 
        {
            update_post_meta( $lead_id, $key, $value );
        }
    } 
    else 
    {
        $lead_id = wp_insert_post( array(
            'post_type'   => 'aiomatic_lead',
            'post_title'  => $email,
            'post_status' => 'publish',
        ) );
        if(is_wp_error($lead_id) || $lead_id === 0)
        {
            return false;
        }
        foreach ( $meta_fields as $key => $value ) 
        {
            add_post_meta( $lead_id, $key, $value );
        }
    }
    return true;
}
function aiomatic_display_leads_table() 
{
    if ( !current_user_can( 'manage_options' ) ) 
    {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'aiomatic-automatic-ai-content-writer' ) );
    }
    $leads_per_page = 20;
    $current_page = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
    $args = array(
        'post_type'      => 'aiomatic_lead',
        'post_status'    => 'publish',
        'posts_per_page' => $leads_per_page,
        'paged'          => $current_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $args = array(
        'post_type'      => 'aiomatic_lead',
        'post_status'    => 'publish',
        'posts_per_page' => $leads_per_page,
        'paged'          => $current_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $leads_query = new WP_Query( $args );
    $total_pages = $leads_query->max_num_pages;
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Collected Leads', 'aiomatic-automatic-ai-content-writer' ) . '</h1>';
    echo '<p>' . esc_html__( 'This feature will work only if you enable the \'Lead Capture\' chatbot extension from the \'Extensions\' tab, or if you use an AI Assistant which has the \'Lead Capture\' extension enabled.', 'aiomatic-automatic-ai-content-writer' ) . '</p>';
    echo '<button type="button" id="aiomatic-export-leads-csv"';
    if ( ! $leads_query->have_posts() ) 
    {
        echo ' disabled';
    }
    echo ' class="button button-primary">' . esc_html__( 'Export All To CSV', 'aiomatic-automatic-ai-content-writer' ) . '</button><br/><br/>';

    echo '<div class="tablenav top">';
    echo '<div class="alignleft actions">';
    echo '<select id="aiomatic-bulk-action-selector">';
    echo '<option value="">' . esc_html__( 'Bulk Actions', 'aiomatic-automatic-ai-content-writer' ) . '</option>';
    echo '<option value="delete">' . esc_html__( 'Delete', 'aiomatic-automatic-ai-content-writer' ) . '</option>';
    echo '</select>';
    echo '<input type="button" id="aiomatic-lead-doaction" class="button action" value="' . esc_attr__( 'Apply', 'aiomatic-automatic-ai-content-writer' ) . '">';
    echo '</div>';
    aiomatic_leads_pagination( $total_pages, $current_page );
    echo '</div><br/><br/>';
    echo '<table class="wp-list-leads-table widefat fixed striped">';
    echo '<thead>
    <tr>
        <td id="cb" class="manage-column column-cb check-column"><input type="checkbox" /></td>
        <th>' . esc_html__( 'Email', 'aiomatic-automatic-ai-content-writer' ) . '</th>
        <th>' . esc_html__( 'Name', 'aiomatic-automatic-ai-content-writer' ) . '</th>
        <th>' . esc_html__( 'Phone Number', 'aiomatic-automatic-ai-content-writer' ) . '</th>
        <th>' . esc_html__( 'Job Title', 'aiomatic-automatic-ai-content-writer' ) . '</th>
        <th>' . esc_html__( 'Company', 'aiomatic-automatic-ai-content-writer' ) . '</th>
        <th>' . esc_html__( 'Location', 'aiomatic-automatic-ai-content-writer' ) . '</th>
        <th>' . esc_html__( 'Date Collected', 'aiomatic-automatic-ai-content-writer' ) . '</th>
        <th>' . esc_html__( 'Actions', 'aiomatic-automatic-ai-content-writer' ) . '</th>
    </tr>
    </thead>';
    echo '<tbody>';
    if ( $leads_query->have_posts() ) 
    {
        $leads = $leads_query->posts;
        foreach ( $leads as $lead ) 
        {
            echo '<tr>';
            
            echo '<th scope="row" class="check-column">';
            echo '<input type="checkbox" name="lead_ids[]" value="' . esc_attr( $lead->ID ) . '" />';
            echo '</th>';

            $editlnk = get_edit_post_link($lead->ID, 'edit');
            if(empty($editlnk))
            {
                $editlnk = '';
            }
            echo '<td><a href="' . esc_url($editlnk) . '">' . esc_html( $lead->post_title ) . '</a></td>';
            $name = get_post_meta( $lead->ID, 'name', true );
            if(empty($name))
            {
                $name = '-';
            }
            echo '<td>' . esc_html( $name ) . '</td>';
            $phone_number = get_post_meta( $lead->ID, 'phone_number', true );
            if(empty($phone_number))
            {
                $phone_number = '-';
            }
            echo '<td>' . esc_html( $phone_number ) . '</td>';
            $job_title = get_post_meta( $lead->ID, 'job_title', true );
            if(empty($job_title))
            {
                $job_title = '-';
            }
            echo '<td>' . esc_html( $job_title ) . '</td>';
            $company_name = get_post_meta( $lead->ID, 'company_name', true );
            if(empty($company_name))
            {
                $company_name = '-';
            }
            echo '<td>' . esc_html( $company_name ) . '</td>';
            $location = get_post_meta( $lead->ID, 'location', true );
            if(empty($location))
            {
                $location = '-';
            }
            echo '<td>' . esc_html( $location ) . '</td>';
            echo '<td>' . esc_html( get_the_date( '', $lead->ID ) ) . '</td>';

            echo '<td><a href="#" class="aiomatic-delete-lead" data-lead-id="' . esc_attr( $lead->ID ) . '">' . esc_html__( 'Delete', 'aiomatic-automatic-ai-content-writer' ) . '</a></td>';
            echo '</tr>';
        }
    } 
    else 
    {
        echo '<tr><td colspan="9">' . esc_html__( 'No leads found.', 'aiomatic-automatic-ai-content-writer' ) . '</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '<div class="tablenav bottom">';
    aiomatic_leads_pagination( $total_pages, $current_page );
    echo '</div>';
    echo '</div>';
}
function aiomatic_leads_pagination( $total_pages, $current_page ) 
{
    if ( $total_pages <= 1 ) {
        return;
    }
    $base_url = esc_url( remove_query_arg( 'paged', $_SERVER['REQUEST_URI'] ) );
    $pagination_args = array(
        'base'      => $base_url . '%_%',
        'format'    => '&paged=%#%',
        'current'   => $current_page,
        'total'     => $total_pages,
        'prev_text' => __( '&laquo; Previous', 'aiomatic-automatic-ai-content-writer' ),
        'next_text' => __( 'Next &raquo;', 'aiomatic-automatic-ai-content-writer' ),
        'add_args'  => false,
    );
    echo '<div class="tablenav-pages">';
    echo paginate_links( $pagination_args );
    echo '</div>';
}
function aiomatic_delete_leads( $lead_ids ) 
{
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'aiomatic-automatic-ai-content-writer' ) );
    }
    if ( ! isset( $_POST['aiomatic_leads_nonce'] ) || ! wp_verify_nonce( $_POST['aiomatic_leads_nonce'], 'aiomatic_leads_nonce_action' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'aiomatic-automatic-ai-content-writer' ) );
    }
    foreach ( $lead_ids as $lead_id ) {
        wp_delete_post( intval( $lead_id ), true );
    }
}
function aiomatic_extract_keywords_from_prompt( $prompt ) 
{
    $stop_words = array( 'the', 'is', 'at', 'of', 'on', 'and', 'a', 'to', 'in', 'with' );
    $words = explode( ' ', strtolower( $prompt ) );
    $keywords = array_diff( $words, $stop_words );
    $keywords = array_slice( $keywords, 0, 5 );
    return implode( '-', $keywords );
}
function aiomatic_media_sideload_image( $file, $post_id = 0, $desc = null, $return_type = 'html', $file_name = '' ) {
	if ( ! empty( $file ) ) 
    {
		$allowed_extensions = array( 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp' );
		$allowed_extensions = apply_filters( 'image_sideload_extensions', $allowed_extensions, $file );
		$allowed_extensions = array_map( 'preg_quote', $allowed_extensions );
		preg_match( '/[^\?]+\.(' . implode( '|', $allowed_extensions ) . ')\b/i', $file, $matches );
		if ( ! $matches ) {
			return new WP_Error( 'image_sideload_failed', esc_html__( 'Invalid image URL.', 'aiomatic-automatic-ai-content-writer' ) );
		}
		$file_array         = array();
        if(empty($file_name))
        {
		    $file_array['name'] = wp_basename( $matches[0] );
        }
        else
        {
            $file_info = pathinfo( $matches[0] );
            $file_extension = isset($file_info['extension']) ? $file_info['extension'] : '';
            $file_array['name'] = $file_name . ($file_extension ? '.' . $file_extension : '');
        }
        add_filter('http_request_args', function ($args, $url) 
        {
            $args['sslverify'] = false;
            return $args;
        }, 10, 2);
		$file_array['tmp_name'] = download_url( $file );
        remove_filter('http_request_args', function ($args, $url) {
            $args['sslverify'] = false;
            return $args;
        }, 10, 2);
        if(is_wp_error($file_array['tmp_name']))
        {
            return new WP_Error( 'File download error: ' . $file . ' error: ' . $file_array['tmp_name']->get_error_message());
        }
		$id = media_handle_sideload( $file_array, $post_id, $desc );
		if ( is_wp_error( $id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $id;
		}
		add_post_meta( $id, '_source_url', $file );
		if ( 'id' === $return_type ) {
			return $id;
		}
		$src = wp_get_attachment_url( $id );
	}
	if ( ! empty( $src ) ) {
		if ( 'src' === $return_type ) {
			return $src;
		}
		$alt  = isset( $desc ) ? esc_attr( $desc ) : '';
		$html = "<img src='$src' alt='$alt' />";
		return $html;
	} 
    else 
    {
		return new WP_Error( 'image_sideload_failed' );
	}
}
function aiomatic_is_gutenberg_page() 
{
    if ( function_exists( 'is_gutenberg_page' ) &&
            is_gutenberg_page()
    ) 
    {
        return true;
    }
    $current_screen = get_current_screen();
    if ( method_exists( $current_screen, 'is_block_editor' ) &&
            $current_screen->is_block_editor()
    ) 
    {
        return true;
    }
    return false;
}
function aiomatic_get_api_service($token, $aimodel)
{
    if(aiomatic_is_perplexity_model($aimodel))
    {
        $api_service = 'PerplexityAI';
    }
    elseif(aiomatic_is_groq_model($aimodel))
    {
        $api_service = 'Groq';
    }
    elseif(aiomatic_is_nvidia_model($aimodel))
    {
        $api_service = 'Nvidia';
    }
    elseif(aiomatic_is_xai_model($aimodel))
    {
        $api_service = 'xAI';
    }
    elseif(aiomatic_is_claude_model($aimodel))
    {
        $api_service = 'Anthropic';
    }
    elseif(aiomatic_is_google_model($aimodel))
    {
        $api_service = 'GoogleAI';
    }
    elseif(aiomatic_check_if_midjourney($aimodel))
    {
        $api_service = 'Midjourney';
    }
    elseif(aiomatic_is_openrouter_model($aimodel))
    {
        $api_service = 'OpenRouter';
    }
    elseif(aiomatic_is_huggingface_model($aimodel))
    {
        $api_service = 'HuggingFace';
    }
    elseif(aiomatic_is_ollama_model($aimodel))
    {
        $api_service = 'Ollama';
    }
    elseif(aiomatic_check_if_stable($aimodel))
    {
        $api_service = 'StableDiffusion';
    }
    elseif(aiomatic_check_if_replicate($aimodel))
    {
        $api_service = 'Replicate';
    }
    elseif(aiomatic_check_if_ideogram($aimodel))
    {
        $api_service = 'Ideogram';
    }
    else
    {
        if(aiomatic_is_aiomaticapi_key($token))
        {
            $api_service = 'AiomaticAPI';
        }
        else
        {
            $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
            if (aiomatic_check_if_azure($aiomatic_Main_Settings))
            {
                $api_service = 'Microsoft Azure OpenAI';
            }
            else
            {
                if(aiomatic_is_custom_model($aimodel, $aiomatic_Main_Settings))
                {
                    $api_service = 'OpenAI Compatible';
                }
                else
                {
                    $api_service = 'OpenAI';
                }
            }
        }
    }
    return $api_service;
}
function aiomatic_check_is_elementor($postid)
{
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    if (!is_plugin_active( 'elementor/elementor.php' )) 
    {
		return false;
	}
    if(!isset(\Elementor\Plugin::$instance))
    {
        return false;
    }
    /** @var \Elementor\Plugin $plugin */
    $plugin = \Elementor\Plugin::$instance ?? null;

    if (!$plugin) {
        return false;
    }

    return $plugin->db->is_built_with_elementor($postid);
}
function aiomatic_upload_base64_image($base64_img, $title, $post_id)
{
    $upload_dir = wp_upload_dir();
    $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
    $image_parts = explode(";base64,", $base64_img);
    $decoded = base64_decode($image_parts[1]);
    $filename = sanitize_title($title) . '.png';
    $hashed_filename = md5( $filename . microtime() ) . '_' . $filename;
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
        wp_filesystem($creds);
    }
    $ret = $wp_filesystem->put_contents( $upload_path . $hashed_filename, $decoded ); 
    if ($ret === FALSE) 
    {
        aiomatic_log_to_file('Failed to copy image locally ' . $upload_path . $hashed_filename);
        return false;
    }
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $file             = array();
    $file['error']    = '';
    $file['tmp_name'] = $upload_path . $hashed_filename;
    $file['name']     = $hashed_filename;
    $file['type']     = 'image/png';
    $file['size']     = $wp_filesystem->size( $upload_path . $hashed_filename );
    $file_return = wp_handle_sideload($file, array( 'test_form' => false ));
    if(!isset($file_return['file']))
    {
        aiomatic_log_to_file('Failed to copy image file locally ' . $upload_path . $hashed_filename . ': ' . print_r($file_return, true));
        return false;
    }
    $filename = $file_return['file'];
    $attachment = array(
        'post_mime_type' => $file_return['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content' => '',
        'post_status' => 'inherit',
        'guid' => $upload_dir['url'] . '/' . basename($filename)
    );
    $attach_id = wp_insert_attachment( $attachment, $filename, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
    wp_update_attachment_metadata($attach_id, $attach_data);
    return $attach_id;
}
function aiomatic_my_get_current_user_roles() 
{
    
    if( is_user_logged_in() ) {
  
      $user = wp_get_current_user();
  
      $roles = ( array ) $user->roles;
  
      return $roles;
  
    } else {
  
      return array();
  
    }
  
}
function aiomatic_my_get_current_user_subscriptions() 
{
    $levels = array();
    if(class_exists('Ihc_Db'))
    {
        if( is_user_logged_in() ) 
        {
            $current_user = wp_get_current_user();
            if(isset($current_user->ID))
            {
                $user_sub_data = Ihc_Db::get_user_levels($current_user->ID, true);
                if(is_array($user_sub_data))
                {
                    foreach($user_sub_data as $udata)
                    {
                        if(isset($udata['level_id']))
                        {
                            $levels[] = $udata['level_id'];
                        }
                    }
                }
            }  
        }
    }
    if (function_exists('rcp_get_member_levels')) {
        if (is_user_logged_in()) 
        {
            $current_user = wp_get_current_user();
            if(isset($current_user->ID))
            {
                $customer    = rcp_get_customer();
                if ( is_object( $customer ) )
                {
                    $memberships = rcp_get_member_levels($customer);
                    if (!empty($memberships)) 
                    {
                        foreach ($memberships as $membership) 
                        {
                            $levels[] = $membership;
                        }
                    }
                }
            }
        }
    }
    if (function_exists('pmpro_getMembershipLevelsForUser')) {
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if(isset($current_user->ID)) {
                $pmpro_levels = pmpro_getMembershipLevelsForUser($current_user->ID);
                if (!empty($pmpro_levels)) {
                    foreach ($pmpro_levels as $level) {
                        if (isset($level->id)) {
                            $levels[] = $level->id;
                        }
                    }
                }
            }
        }
    }
    return $levels;
}
function aiomatic_extractYouTubeVideoId($url) 
{
    $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?.*?[&]?v=|embed\/|v\/|live\/|user\/[^\/?]+.*?[?&]v=|channel\/[^\/?]+\/videos.*?[?&]v=|.+?\?v=)|youtu\.be\/|m\.youtube\.com\/watch\?.*?[&]?v=|gaming\.youtube\.com\/watch\?.*?[&]?v=)([a-zA-Z0-9_-]{11})/';
    if (preg_match($pattern, $url, $matches)) {
        return 'https://www.youtube.com/watch?v=' . $matches[1];
    }
    return $url;
}
function aiomatic_get_assistant()
{
    $aiomatic_assistant_defaults = array("Write a paragraph on this" => array(
"0" => 'Write a paragraph on this topic:

%%selected_text%%
    
----
Written paragraph:
',
        "1" => 'text'
),

"Continue this text" => Array
    (
        "0" => 'Continue this text:

%%selected_text%%

----
Continued text:
',
"1" => 'text'
    ),

"Generate ideas on this" => Array
    (
        "0"=> 'Write a few ideas on that as bullet points:

%%selected_text%%

----
Generated ideas in bullet points:
',
"1" => 'text'
    ),

"Write an article about this" => Array
    (
        "0" => 'Write a complete article about this:

%%selected_text%%

----
Written article:
',
"1" => 'text'
    ),

"Turn this into an Ad" => Array
    (
"0" => 'Turn the following text into a creative advertisement:

%%selected_text%%

----
Advertisement:
',
"1" => 'text'
    ),

"Explain this to a 5 year old" => Array
    (
"0" => 'Explain this to a 5 years old kid:

%%selected_text%%

----
Explanation:
',
"1" => 'text'
    ),

"Find a matching quote for this" => Array
    (
"0" => 'Find a matching quote for the following text:

%%selected_text%%

----
Matching quote:
',
"1" => 'text'
    ),

"Generate a subtitle for this" => Array
    (
        "0" => 'Generate a title for this text:

%%selected_text%%

----
Title:
',
"1" => 'text'
    ),

"Generate a TL;DR of this" => Array
    (
        "0" => 'Write a TL;DR for this text:

%%selected_text%%

----
TL;DR:
',
"1" => 'text'
    ),

"Generate a Call to Action fo this" => Array
    (
        "0" => 'Generate a call to action about this:

%%selected_text%%

----
Call to action:
',
"1" => 'text'
    ),

"Summarize this" => Array
    (
        "0" => 'Summarize this text:

%%selected_text%%

----
Summary:
',
"1" => 'text'
    ),

"Expand this" => Array
    (
        "0" => 'Expand this text:

%%selected_text%%

----
Expanded text:
',
"1" => 'text'
    ),

"Make a bulleted list for this" => Array
    (
        "0" => 'Make a C for this:

%%selected_text%%

----
Bulleted list:
',
"1" => 'text'
    ),

"Rewrite this" => Array
    (
        "0" => 'Rewrite this text:

%%selected_text%%

----
Rewritten text:
',
"1" => 'text'
    ),

"Paraphrase this" => Array
    (
        "0" => 'Paraphrase this text:

%%selected_text%%

----
Paraphrased text:
',
"1" => 'text'
    ),

"Fix grammar of this" => Array
    (
        "0" => 'Fix grammar of this text:

%%selected_text%%

----
Text with fixed grammar:
',
"1" => 'text'
    ),

"Generate a question of this" => Array
    (
        "0" => 'Generate a question about this text:

%%selected_text%%

----
Question:
',
"1" => 'text'
    ),

"Convert this to passive voice" => Array
    (
        "0" => 'Convert this text to passive voice:

%%selected_text%%

----
Converted text to passive voice:
',
"1" => 'text'
    ),

"Convert this to active voice" => Array
    (
        "0" => 'Convert this text to active voice:

%%selected_text%%

----
Converted text to active voice:
',
"1" => 'text'
    ),

"Write a conclusion for this" => Array
    (
        "0" => 'Write a conclusion for this text:

%%selected_text%%

----
Conclusion:
',
"1" => 'text'
    ),

"Write a counterargument for this" => Array
    (
        "0" => 'Wite a counterargument for this text:

%%selected_text%%

----
Counterargument:
',
"1" => 'text'
    ),

"Translate this to Spanish" => Array
    (
"0" => 'Translate this text to Spanish:

%%selected_text%%

----
Spanish translation:
',
"1" => 'text'
    ),

"Generate an image idea for this" => Array
    (
"0" => 'Describe an image that would match this text:

%%selected_text%%

----
Image description:
',
"1" => 'text'
    ),
    
"Execute this as a prompt" => Array
        (
"0" => '%%selected_text%%',
"1" => 'text'
        ),

"Generate an image of this" => Array
    (
"0" => 'A image of: %%selected_text%%',
"1" => 'image'
    )

);
    $rules  = get_option('aiomatic_assistant_list', array());
    if(!is_array($rules))
    {
        $rules = array();
    }
    if(empty($rules))
    {
        $rules = $aiomatic_assistant_defaults;
    }
    return $rules;
}
function aiomatic_hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
	    $g = hexdec(substr($hex,1,1).substr($hex,1,1));
	    $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
	    $r = hexdec(substr($hex,0,2));
	    $g = hexdec(substr($hex,2,2));
	    $b = hexdec(substr($hex,4,2));
	}
	$rgb = array($r, $g, $b);
	return implode(",", $rgb);
}
function aiomatic_array_unique($array, $keep_key_assoc = false){
    $duplicate_keys = array();
    $tmp = array();       
 
    foreach ($array as $key => $val){
        if (is_object($val))
            $val = (array)$val;
 
        if (!in_array($val, $tmp))
            $tmp[] = $val;
        else
            $duplicate_keys[] = $key;
    }
 
    foreach ($duplicate_keys as $key)
        unset($array[$key]);
 
    return $keep_key_assoc ? $array : array_values($array);
 }
function aiomatic_clean_language_model_texts($content)
{
    $content = preg_replace('#As an? (?:AI )?languau?ge model(?: AI)?,?\s?#i' , '', $content);
    return $content;
}

function aiomatic_trailing_comma($incrementor, $count, &$subject) {
    $stopper = $count - 1;
	if ($incrementor !== $stopper) {
		return $subject .= ',';
	}
}
function aiomatic_auto_clear_log()
{
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
       wp_filesystem($creds);
    }
    if ($wp_filesystem->exists(trailingslashit(get_temp_dir()) . 'aiomatic_info.log')) {
        $wp_filesystem->delete(trailingslashit(get_temp_dir()) . 'aiomatic_info.log');
    }
}
function aiomatic_isSecure() {
    return
      (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
      || $_SERVER['SERVER_PORT'] == 443;
}

function aiomatic_get_mime ($filename) {
    $mime_types = array(
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'mts' => 'video/mp2t',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',
        'wmv' => 'video/x-ms-wmv',
        'mp4' => 'video/mp4',
        'm4p' => 'video/m4p',
        'm4v' => 'video/m4v',
        'mpg' => 'video/mpg',
        'mp2' => 'video/mp2',
        'mpe' => 'video/mpe',
        'mpv' => 'video/mpv',
        'm2v' => 'video/m2v',
        'm4v' => 'video/m4v',
        '3g2' => 'video/3g2',
        '3gpp' => 'video/3gpp',
        'f4v' => 'video/f4v',
        'f4p' => 'video/f4p',
        'f4a' => 'video/f4a',
        'f4b' => 'video/f4b',
        '3gp' => 'video/3gp',
        'avi' => 'video/x-msvideo',
        'mpeg' => 'video/mpeg',
        'mpegps' => 'video/mpeg',
        'webm' => 'video/webm',
        'mpeg4' => 'video/mp4',
        'mkv' => 'video/mkv',
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'docx' => 'application/msword',
        'xlsx' => 'application/vnd.ms-excel',
        'pptx' => 'application/vnd.ms-powerpoint',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
       wp_filesystem($creds);
    }
    if (!$wp_filesystem->exists($filename)) 
    {
        return 'application/octet-stream';
    }
    if (!$wp_filesystem->is_readable($filename)) {
        return 'application/octet-stream';
    }
    $ext = array_values(array_slice(explode('.', $filename), -1));$ext = $ext[0];
    if(stristr($filename, 'dailymotion.com'))
    {
        return 'application/octet-stream';
    }
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) 
        {
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            if($mimetype === false)
            {
                if (array_key_exists($ext, $mime_types)) {
                    return $mime_types[$ext];
                } else {
                    return 'application/octet-stream';
                }
            }
            $mimetype = explode(';', $mimetype)[0];
            return $mimetype;
        }
        else
        {
            return 'application/octet-stream';
        }
    } 
    elseif (array_key_exists($ext, $mime_types)) 
    {
        return $mime_types[$ext];
    } 
    elseif (function_exists('mime_content_type')) 
    {
        error_reporting(0);
        $mimetype = mime_content_type($filename);
        error_reporting(E_ALL);
        if($mimetype == '')
        {
            if (array_key_exists($ext, $mime_types)) {
                return $mime_types[$ext];
            } else {
                return 'application/octet-stream';
            }
        }
        $mimetype = explode(';', $mimetype)[0];
        return $mimetype;
    }
    else 
    {
        return 'application/octet-stream';
    }
}
function aiomatic_get_items($query=array()){
     global $wpdb;
     $defaults = array(
       'post_hash'=>''
     );
 
    $query = wp_parse_args($query, $defaults);
    
    extract($query);
    $sql = $wpdb->prepare(
        "SELECT post_result FROM {$wpdb->aiomatict_shortcode_rez} WHERE post_hash = %s",
        $post_hash
    );
    $logs = $wpdb->get_results($sql);
    $logs = apply_filters('aiomatic_get_items', $logs, $query);
    return $logs;
}
function aiomatic_replaceAIPostShortcodes($content, $post_link, $post_title, $blog_title, $post_excerpt, $post_content, $user_name, $featured_image, $post_cats, $post_tagz, $post_id, $img_attr = '', $old_title = '', $post_title_keywords = '', $custom_shortcodes = '', $global_prepend = '', $global_append = '')
{
    $matches = array();
    $i = 0;
    preg_match_all('~%regex\(\s*\"([^"]+?)\s*"\s*[,;]\s*\"([^"]*)\"\s*(?:[,;]\s*\"([^"]*?)\s*\")?(?:[,;]\s*\"([^"]*?)\s*\")?(?:[,;]\s*\"([^"]*?)\s*\")?\)%~si', $content, $matches);
    if (is_array($matches) && count($matches) && is_array($matches[0])) 
    {
        for($i = 0; $i < count($matches[0]); $i++)
        {
            if (isset($matches[0][$i])) $fullmatch = $matches[0][$i];
            if (isset($matches[1][$i])) $search_in = aiomatic_replaceAIPostShortcodes($matches[1][$i], $post_link, $post_title, $blog_title, $post_excerpt, $post_content, $user_name, $featured_image, $post_cats, $post_tagz, $post_id, $img_attr, $old_title, $post_title_keywords, $custom_shortcodes, $global_prepend, $global_append);
            if (isset($matches[2][$i])) $matchpattern = $matches[2][$i];
            if (isset($matches[3][$i])) $element = $matches[3][$i];
            if (isset($matches[4][$i])) $delimeter = $matches[4][$i];if (isset($matches[5][$i])) $counter = $matches[5][$i];
            if (isset($matchpattern)) {
               if (preg_match('<^[\/#%+~[\]{}][\s\S]*[\/#%+~[\]{}]$>', $matchpattern, $z)) {
                  preg_match_all($matchpattern, $search_in, $submatches, PREG_PATTERN_ORDER);
               }
               else {
                  preg_match_all('~'.$matchpattern.'~si', $search_in, $submatches, PREG_PATTERN_ORDER);
               }
            }
            if (isset($submatches)) {
               if (is_array($submatches)) {
                  $empty_elements = array_keys($submatches[0], "");
                  foreach ($empty_elements as $e) {
                     unset($submatches[0][$e]);
                  }
                  $submatches[0] = array_unique($submatches[0]);
                  if (!is_numeric($element)) {
                     $element = 0;
                  }if (!is_numeric($counter)) {
                     $counter = 0;
                  }
                  if(isset($submatches[(int)($element)]))
                  {
                      $matched = $submatches[(int)($element)];
                  }
                  else
                  {
                      $matched = '';
                  }
                  $matched = array_unique((array)$matched);
                  if (empty($delimeter) || $delimeter == 'null') {
                     if (isset($matched[$counter])) $matched = $matched[$counter];
                  }
                  else {
                     $matched = implode($delimeter, $matched);
                  }
                  if (empty($matched)) {
                     $content = str_replace($fullmatch, '', $content);
                  } else {
                     $content = str_replace($fullmatch, $matched, $content);
                  }
               }
            }
        }
    }
    $spintax = new Aiomatic_Spintax();
    $content = $spintax->process($content);
    $pcxxx = explode('<!- template ->', $content);
    $content = $pcxxx[array_rand($pcxxx)];
    $content = str_replace('%%random_sentence%%', aiomatic_random_sentence_generator(), $content);
    $content = str_replace('%%random_sentence2%%', aiomatic_random_sentence_generator(false), $content);
    $content = aiomatic_replaceSynergyShortcodes($content);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['custom_html'])) {
        $content = str_replace('%%custom_html%%', $aiomatic_Main_Settings['custom_html'], $content);
    }
    if (isset($aiomatic_Main_Settings['custom_html2'])) {
        $content = str_replace('%%custom_html2%%', $aiomatic_Main_Settings['custom_html2'], $content);
    }
    $content = str_replace('%%post_link%%', $post_link, $content);
    $content = str_replace('%%post_title%%', $post_title, $content);
    $content = str_replace('%%post_title_keywords%%', $post_title_keywords, $content);
    $content = str_replace('%%post_original_title%%', $old_title, $content);
    $content = str_replace('%%blog_title%%', $blog_title, $content);
    $content = str_replace('%%post_excerpt%%', $post_excerpt, $content);
    $post_content = strip_shortcodes($post_content);
    $content = str_replace('%%post_content%%', $post_content, $content);
    $content = str_replace('%%post_content_plain_text%%', strip_tags($post_content), $content);
    $content = str_replace('%%author_name%%', $user_name, $content);
    $content = str_replace('%%current_date_time%%', date('Y/m/d H:i:s'), $content);
    $content = str_replace('%%featured_image%%', $featured_image, $content);
    $content = str_replace('%%post_cats%%', $post_cats, $content);
    $content = str_replace('%%post_tags%%', $post_tagz, $content);
    $img_attr = str_replace('%%image_source_name%%', '', $img_attr);
    $img_attr = str_replace('%%image_source_url%%', '', $img_attr);
    $img_attr = str_replace('%%image_source_website%%', '', $img_attr);
    $content = str_replace('%%royalty_free_image_attribution%%', $img_attr, $content);
    if($post_id != '')
    {
        preg_match_all('#%%!([^!]*?)!%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $post_custom_data = get_post_meta($post_id, $mc, true);
                if($post_custom_data != '')
                {
                    $content = str_replace('%%!' . $mc . '!%%', $post_custom_data, $content);
                }
                else
                {
                    $content = str_replace('%%!' . $mc . '!%%', '', $content);
                }
            }
        }
        preg_match_all('#%%!!([^!]*?)!!%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $ctaxs = '';
                $terms = get_the_terms( $post_id, $mc );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                {
                    $ctaxs_arr = array();
                    foreach ( $terms as $term ) {
                        $ctaxs_arr[] = $term->slug;
                    }
                    $ctaxs = implode(',', $ctaxs_arr);
                }
                if($post_custom_data != '')
                {
                    $content = str_replace('%%!!' . $mc . '!!%%', $ctaxs, $content);
                }
                else
                {
                    $content = str_replace('%%!!' . $mc . '!!%%', '', $content);
                }
            }
        }
    }
    else
    {
        preg_match_all('#%%!([^!]*?)!%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $content = str_replace('%%!' . $mc . '!%%', '', $content);
            }
        }
        preg_match_all('#%%!!([^!]*?)!!%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $content = str_replace('%%!!' . $mc . '!!%%', '', $content);
            }
        }
    }
    if ( is_user_logged_in() ) 
    {
        $user_id = get_current_user_id();
        if($user_id !== 0)
        {
            preg_match_all('#%%~([^!]*?)~%%#', $content, $matched_content);
            if(isset($matched_content[1][0]))
            {
                foreach($matched_content[1] as $mc)
                {
                    $post_custom_data = get_user_meta($user_id, $mc, true);
                    if($post_custom_data != '')
                    {
                        $content = str_replace('%%~' . $mc . '~%%', $post_custom_data, $content);
                    }
                    else
                    {
                        $content = str_replace('%%~' . $mc . '~%%', '', $content);
                    }
                }
            }
        }
        else
        {
            preg_match_all('#%%~([^!]*?)~%%#', $content, $matched_content);
            if(isset($matched_content[1][0]))
            {
                foreach($matched_content[1] as $mc)
                {
                    $content = str_replace('%%~' . $mc . '~%%', '', $content);
                }
            }
        }
    } 
    else 
    {
        preg_match_all('#%%~([^!]*?)~%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $content = str_replace('%%~' . $mc . '~%%', '', $content);
            }
        }
    }
    $content = preg_replace_callback('#%%random_image_url\[([^\]]*?)\]%%#', function ($matches) {
        $arv = array();
        $my_img = aiomatic_get_random_image_google($matches[1], 0, 0, '', $arv);
        return $my_img;
    }, $content);
    $content = preg_replace_callback('#%%random_image\[([^\]]*?)\](\[\d+\])?%%#', function ($matches) {
        if(isset($matches[2]))
        {
            $chance = trim($matches[2], '[]');
        }
        else
        {
            $chance = '';
        }
        $arv = array();
        $my_img = aiomatic_get_random_image_google($matches[1], 0, 0, $chance, $arv);
        return '<img src="' . $my_img . '">';
    }, $content);
    $content = preg_replace_callback('#%%random_video\[([^\]]*?)\](\[\d+\])?%%#', function ($matches) {
        if(isset($matches[2]))
        {
            $chance = trim($matches[2], '[]');
        }
        else
        {
            $chance = '';
        }
        $my_vid = aiomatic_get_video($matches[1], $chance, array());
        return $my_vid;
    }, $content);
    if(!empty($custom_shortcodes))
    {
        if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
        {
            aiomatic_log_to_file('You need to insert a valid OpenAI/AiomaticAPI API Key for the custom shortcode creator to work!');
        }
        else
        {
            $allmodels = aiomatic_get_all_models();
            $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
            $appids = array_filter($appids);
            $token = $appids[array_rand($appids)];
            if(!is_array($custom_shortcodes))
            {
                if($custom_shortcodes != '')
                {
                    $custom_shortcodes = aiomatic_replaceAIPostShortcodes($custom_shortcodes, $post_link, $post_title, $blog_title, $post_excerpt, $post_content, $user_name, $featured_image, $post_cats, $post_tagz, $post_id, $img_attr, $old_title, $post_title_keywords, '', $global_prepend, $global_append);
                }
                $custom_shortcodes = preg_split('/\r\n|\r|\n/', $custom_shortcodes);
            }
            foreach($custom_shortcodes as $my_short)
            {
                $name_part = explode('=>', $my_short);
                if(isset($name_part[1]) && !empty(trim($name_part[1])))
                {
                    $shortname = trim($name_part[0]);
                    if(strstr($content, '%%' . $shortname . '%%'))
                    {
                        $shortval = '';
                        $ai_part = explode('@@', $name_part[1]);
                        if(isset($ai_part[1]) && !empty(trim($ai_part[1])))
                        {
                            if(!in_array(trim($ai_part[0]), $allmodels))
                            {
                                $aimodel = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                            }
                            else
                            {
                                $aimodel = trim($ai_part[0]);
                            }
                            $ai_command = trim($ai_part[1]);

                            $ai_command = str_replace('%%random_sentence%%', aiomatic_random_sentence_generator(), $ai_command);
                            $ai_command = str_replace('%%random_sentence2%%', aiomatic_random_sentence_generator(false), $ai_command);
                            $ai_command = aiomatic_replaceSynergyShortcodes($ai_command);
                            $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
                            if (isset($aiomatic_Main_Settings['custom_html'])) {
                                $ai_command = str_replace('%%custom_html%%', $aiomatic_Main_Settings['custom_html'], $ai_command);
                            }
                            if (isset($aiomatic_Main_Settings['custom_html2'])) {
                                $ai_command = str_replace('%%custom_html2%%', $aiomatic_Main_Settings['custom_html2'], $ai_command);
                            }
                            $ai_command = str_replace('%%post_link%%', $post_link, $ai_command);
                            $ai_command = str_replace('%%post_title%%', $post_title, $ai_command);
                            $ai_command = str_replace('%%post_title_keywords%%', $post_title_keywords, $ai_command);
                            $ai_command = str_replace('%%post_original_title%%', $old_title, $ai_command);
                            $ai_command = str_replace('%%blog_title%%', $blog_title, $ai_command);
                            $ai_command = str_replace('%%post_excerpt%%', $post_excerpt, $ai_command);
                            $ai_command = str_replace('%%post_content%%', $post_content, $ai_command);
                            $ai_command = str_replace('%%post_content_plain_text%%', strip_tags($post_content), $ai_command);
                            $ai_command = str_replace('%%author_name%%', $user_name, $ai_command);
                            $ai_command = str_replace('%%current_date_time%%', date('Y/m/d H:i:s'), $ai_command);
                            $ai_command = str_replace('%%featured_image%%', $featured_image, $ai_command);
                            $ai_command = str_replace('%%post_cats%%', $post_cats, $ai_command);
                            $ai_command = str_replace('%%post_tags%%', $post_tagz, $ai_command);
                            $ai_command = str_replace('%%royalty_free_image_attribution%%', $img_attr, $ai_command);

                            $ai_command = apply_filters('aiomatic_replace_aicontent_shortcode', $ai_command);
                            foreach($custom_shortcodes as $my_check_short)
                            {
                                $check_name_part = explode('=>', $my_check_short);
                                if(isset($check_name_part[1]) && !empty(trim($check_name_part[1])))
                                {
                                    $check_shortname = trim($check_name_part[0]);
                                    if($shortname !== $check_shortname && strstr($ai_command, '%%' . $check_shortname . '%%') !== false)
                                    {
                                        $check_shortval = '';
                                        $check_ai_part = explode('@@', $check_name_part[1]);
                                        if(!in_array(trim($check_ai_part[0]), $allmodels))
                                        {
                                            $check_aimodel = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                                        }
                                        else
                                        {
                                            $check_aimodel = trim($check_ai_part[0]);
                                        }
                                        $check_ai_command = trim($check_ai_part[1]);
                                        $aierror = '';

                                        $check_ai_command = str_replace('%%random_sentence%%', aiomatic_random_sentence_generator(), $check_ai_command);
                                        $check_ai_command = str_replace('%%random_sentence2%%', aiomatic_random_sentence_generator(false), $check_ai_command);
                                        $check_ai_command = aiomatic_replaceSynergyShortcodes($check_ai_command);
                                        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
                                        if (isset($aiomatic_Main_Settings['custom_html'])) {
                                            $check_ai_command = str_replace('%%custom_html%%', $aiomatic_Main_Settings['custom_html'], $check_ai_command);
                                        }
                                        if (isset($aiomatic_Main_Settings['custom_html2'])) {
                                            $check_ai_command = str_replace('%%custom_html2%%', $aiomatic_Main_Settings['custom_html2'], $check_ai_command);
                                        }
                                        $check_ai_command = str_replace('%%post_link%%', $post_link, $check_ai_command);
                                        $check_ai_command = str_replace('%%post_title%%', $post_title, $check_ai_command);
                                        $check_ai_command = str_replace('%%post_title_keywords%%', $post_title_keywords, $check_ai_command);
                                        $check_ai_command = str_replace('%%post_original_title%%', $old_title, $check_ai_command);
                                        $check_ai_command = str_replace('%%blog_title%%', $blog_title, $check_ai_command);
                                        $check_ai_command = str_replace('%%post_excerpt%%', $post_excerpt, $check_ai_command);
                                        $check_ai_command = str_replace('%%post_content%%', $post_content, $check_ai_command);
                                        $check_ai_command = str_replace('%%post_content_plain_text%%', strip_tags($post_content), $check_ai_command);
                                        $check_ai_command = str_replace('%%author_name%%', $user_name, $check_ai_command);
                                        $check_ai_command = str_replace('%%current_date_time%%', date('Y/m/d H:i:s'), $check_ai_command);
                                        $check_ai_command = str_replace('%%featured_image%%', $featured_image, $check_ai_command);
                                        $check_ai_command = str_replace('%%post_cats%%', $post_cats, $check_ai_command);
                                        $check_ai_command = str_replace('%%post_tags%%', $post_tagz, $check_ai_command);
                                        $check_ai_command = str_replace('%%royalty_free_image_attribution%%', $img_attr, $check_ai_command);

                                        $check_ai_command = apply_filters('aiomatic_replace_aicontent_shortcode', $check_ai_command);
                                        $check_generated_text = aiomatic_generate_custom_shortcode_content($token, $check_aimodel, $check_ai_command, $global_prepend, $global_append, $aiomatic_Main_Settings, $aierror);
                                        if($check_generated_text === false)
                                        {
                                            aiomatic_log_to_file('Custom nested shortcode generator error: ' . $aierror);
                                        }
                                        else
                                        {
                                            $check_shortval = trim(trim(trim(trim($check_generated_text), '.'), ' “”‘’"\''));
                                        }
                                        $ai_command = str_replace('%%' . $check_shortname . '%%', $check_shortval, $ai_command);
                                    }
                                }
                            }
                            preg_match_all('#%%related_questions_([^%]*?)%%#i', $ai_command, $mxatches);
                            if(isset($mxatches[1][0]))
                            {
                                foreach($mxatches[1] as $googlematch)
                                {
                                    $mtchres = aiomatic_scrape_related_questions($googlematch, 5, '', 1, 1, 0, 0, 2000, '', '');
                                    if(is_array($mtchres) && !empty($mtchres))
                                    {
                                        $quests = array();
                                        foreach($mtchres as $mra)
                                        {
                                            if(isset($mra['q']))
                                            {
                                                $quests[] = $mra['q'];
                                            }
                                        }
                                        $mtchres = implode(',', $quests);
                                    }
                                    $ai_command = str_ireplace('%%related_questions_' . $googlematch . '%%', $mtchres, $ai_command);
                                }
                            }
                            $max_tokens = aiomatic_get_max_tokens($aimodel);
                            $query_token_count = count(aiomatic_encode($ai_command));
                            $available_tokens = aiomatic_compute_available_tokens($aimodel, $max_tokens, $ai_command, $query_token_count);
                            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                            {
                                $string_len = strlen($ai_command);
                                $string_len = $string_len / 2;
                                $string_len = intval(0 - $string_len);
                                $ai_command = aiomatic_substr($ai_command, 0, $string_len);
                                $ai_command = trim($ai_command);
                                $query_token_count = count(aiomatic_encode($ai_command));
                                $available_tokens = $max_tokens - $query_token_count;
                            }
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) 
                            {
                                $api_service = aiomatic_get_api_service($token, $aimodel);
                                aiomatic_log_to_file('Calling ' . $api_service . ' (' . $aimodel . ')(' . $available_tokens . ') for custom shortcode text: ' . $ai_command);
                            }
                            $thread_id = '';
                            $aierror = '';
                            $finish_reason = '';
                            $temperature = 1;
                            $top_p = 1;
                            $ai_command = aiomatic_replaceSynergyShortcodes($ai_command);
                            if(!empty($ai_command))
                            {
                                $ai_command = aiomatic_replaceAIPostShortcodes($ai_command, $post_link, $post_title, $blog_title, $post_excerpt, $post_content, $user_name, $featured_image, $post_cats, $post_tagz, $post_id, $img_attr, $old_title, $post_title_keywords, $custom_shortcodes, $global_prepend, $global_append);
                            }
                            $presence_penalty = 0;
                            $frequency_penalty = 0;
                            if(!empty($global_prepend))
                            {
                                $ai_command = $global_prepend . ' ' . $ai_command;
                            }
                            if(!empty($global_append))
                            {
                                $ai_command = $ai_command . ' ' . $global_append;
                            }
                            $generated_text = aiomatic_generate_text($token, $aimodel, $ai_command, $available_tokens, $temperature, $top_p, $presence_penalty, $frequency_penalty, false, 'customShortcode', 0, $finish_reason, $aierror, true, false, false, '', '', 'user', '', $thread_id, '', 'disabled', '', false, false, array());
                            if($generated_text === false)
                            {
                                aiomatic_log_to_file('Custom shortcode generator error: ' . $aierror);
                            }
                            else
                            {
                                $shortval = trim(trim(trim(trim($generated_text), '.'), ' "\''));
                            }
                        }
                        $content = str_replace('%%' . $shortname . '%%', $shortval, $content);
                    }
                }
            }
        }
    }
    $content = apply_filters('aiomatic_replace_aicontent_shortcode', $content);
    preg_match_all('#%%related_questions_([^%]*?)%%#i', $content, $mxatches);
    if(isset($mxatches[1][0]))
    {
        foreach($mxatches[1] as $googlematch)
        {
            $mtchres = aiomatic_scrape_related_questions($googlematch, 5, '', 1, 1, 0, 0, 2000, '', '');
            if(is_array($mtchres) && !empty($mtchres))
            {
                $quests = array();
                foreach($mtchres as $mra)
                {
                    if(isset($mra['q']))
                    {
                        $quests[] = $mra['q'];
                    }
                }
                $mtchres = implode(',', $quests);
            }
            $content = str_ireplace('%%related_questions_' . $googlematch . '%%', $mtchres, $content);
        }
    }
    return $content;
}
function aiomatic_replaceEmbeddingsAIPostShortcodes($content, $post_id)
{
    $matches = array();
    $img_attr = '';
    $i = 0;
    $post_wp = get_post( $post_id ); 
    if(!isset($post_wp->ID))
    {
        $post_title = '';
        $post_excerpt = '';
        $post_content = '';
        $post_link = '';
        $user_name = '';
        $featured_image = '';
        $post_cats = '';
        $post_tagz = '';
        $blog_title = html_entity_decode(get_bloginfo('title'));
    }
    else
    {
        $post_title = $post_wp->post_title;
        $post_excerpt = $post_wp->post_excerpt;
        $post_content = $post_wp->post_content;
        $post_link = get_permalink($post_id);
        $author_id = $post_wp->post_author;
        $user = new WP_User( $author_id ); 
        $user_name = $user->display_name;
        $featured_image = wp_get_attachment_url( get_post_thumbnail_id($post_id), 'thumbnail' );
        $post_cats = '';
        $category_detail = get_the_category($post_id);
        if ($category_detail) {
            foreach($category_detail as $cd){
                $post_cats .= $cd->cat_name . ',';
            }
            $post_cats = trim($post_cats, ',');
        }
        $post_tagz = '';
        $posttags = get_the_tags();
        if ($posttags) {
            foreach($posttags as $tag) {
                $post_tagz .= $tag->name . ','; 
            }
            $post_tagz = trim($post_tagz, ',');
        }
        $blog_title = html_entity_decode(get_bloginfo('title'));
    }
    preg_match_all('~%regex\(\s*\"([^"]+?)\s*"\s*[,;]\s*\"([^"]*)\"\s*(?:[,;]\s*\"([^"]*?)\s*\")?(?:[,;]\s*\"([^"]*?)\s*\")?(?:[,;]\s*\"([^"]*?)\s*\")?\)%~si', $content, $matches);
    if (is_array($matches) && count($matches) && is_array($matches[0])) 
    {
        for($i = 0; $i < count($matches[0]); $i++)
        {
            if (isset($matches[0][$i])) $fullmatch = $matches[0][$i];
            if (isset($matches[1][$i])) $search_in = aiomatic_replaceEmbeddingsAIPostShortcodes($matches[1][$i], $post_link, $post_title, $blog_title, $post_excerpt, $post_content, $user_name, $featured_image, $post_cats, $post_tagz, $post_id, $img_attr);
            if (isset($matches[2][$i])) $matchpattern = $matches[2][$i];
            if (isset($matches[3][$i])) $element = $matches[3][$i];
            if (isset($matches[4][$i])) $delimeter = $matches[4][$i];if (isset($matches[5][$i])) $counter = $matches[5][$i];
            if (isset($matchpattern)) {
               if (preg_match('<^[\/#%+~[\]{}][\s\S]*[\/#%+~[\]{}]$>', $matchpattern, $z)) {
                  preg_match_all($matchpattern, $search_in, $submatches, PREG_PATTERN_ORDER);
               }
               else {
                  preg_match_all('~'.$matchpattern.'~si', $search_in, $submatches, PREG_PATTERN_ORDER);
               }
            }
            if (isset($submatches)) {
               if (is_array($submatches)) {
                  $empty_elements = array_keys($submatches[0], "");
                  foreach ($empty_elements as $e) {
                     unset($submatches[0][$e]);
                  }
                  $submatches[0] = array_unique($submatches[0]);
                  if (!is_numeric($element)) {
                     $element = 0;
                  }if (!is_numeric($counter)) {
                     $counter = 0;
                  }
                  if(isset($submatches[(int)($element)]))
                  {
                      $matched = $submatches[(int)($element)];
                  }
                  else
                  {
                      $matched = '';
                  }
                  $matched = array_unique((array)$matched);
                  if (empty($delimeter) || $delimeter == 'null') {
                     if (isset($matched[$counter])) $matched = $matched[$counter];
                  }
                  else {
                     $matched = implode($delimeter, $matched);
                  }
                  if (empty($matched)) {
                     $content = str_replace($fullmatch, '', $content);
                  } else {
                     $content = str_replace($fullmatch, $matched, $content);
                  }
               }
            }
        }
    }
    $spintax = new Aiomatic_Spintax();
    $content = $spintax->process($content);
    $pcxxx = explode('<!- template ->', $content);
    $content = $pcxxx[array_rand($pcxxx)];
    $content = str_replace('%%random_sentence%%', aiomatic_random_sentence_generator(), $content);
    $content = str_replace('%%random_sentence2%%', aiomatic_random_sentence_generator(false), $content);
    $content = aiomatic_replaceSynergyShortcodes($content);
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['custom_html'])) {
        $content = str_replace('%%custom_html%%', $aiomatic_Main_Settings['custom_html'], $content);
    }
    if (isset($aiomatic_Main_Settings['custom_html2'])) {
        $content = str_replace('%%custom_html2%%', $aiomatic_Main_Settings['custom_html2'], $content);
    }
    $content = str_replace('%%post_link%%', $post_link, $content);
    $content = str_replace('%%post_title%%', $post_title, $content);
    $content = str_replace('%%post_title_keywords%%', $post_title, $content);
    $content = str_replace('%%post_original_title%%', $post_title, $content);
    $content = str_replace('%%blog_title%%', $blog_title, $content);
    $content = str_replace('%%post_excerpt%%', $post_excerpt, $content);
    $post_content = strip_shortcodes($post_content);
    $content = str_replace('%%post_content%%', $post_content, $content);
    $content = str_replace('%%post_content_plain_text%%', strip_tags($post_content), $content);
    $content = str_replace('%%author_name%%', $user_name, $content);
    $content = str_replace('%%current_date_time%%', date('Y/m/d H:i:s'), $content);
    $content = str_replace('%%featured_image%%', $featured_image, $content);
    $content = str_replace('%%post_cats%%', $post_cats, $content);
    $content = str_replace('%%post_tags%%', $post_tagz, $content);
    $img_attr = str_replace('%%image_source_name%%', '', $img_attr);
    $img_attr = str_replace('%%image_source_url%%', '', $img_attr);
    $img_attr = str_replace('%%image_source_website%%', '', $img_attr);
    $content = str_replace('%%royalty_free_image_attribution%%', $img_attr, $content);
    if($post_id != '')
    {
        preg_match_all('#%%!([^!]*?)!%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $post_custom_data = get_post_meta($post_id, $mc, true);
                if($post_custom_data != '')
                {
                    $content = str_replace('%%!' . $mc . '!%%', $post_custom_data, $content);
                }
                else
                {
                    $content = str_replace('%%!' . $mc . '!%%', '', $content);
                }
            }
        }
        preg_match_all('#%%!!([^!]*?)!!%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $ctaxs = '';
                $terms = get_the_terms( $post_id, $mc );
                if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                {
                    $ctaxs_arr = array();
                    foreach ( $terms as $term ) {
                        $ctaxs_arr[] = $term->slug;
                    }
                    $ctaxs = implode(',', $ctaxs_arr);
                }
                if($post_custom_data != '')
                {
                    $content = str_replace('%%!!' . $mc . '!!%%', $ctaxs, $content);
                }
                else
                {
                    $content = str_replace('%%!!' . $mc . '!!%%', '', $content);
                }
            }
        }
    }
    else
    {
        preg_match_all('#%%!([^!]*?)!%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $content = str_replace('%%!' . $mc . '!%%', '', $content);
            }
        }
        preg_match_all('#%%!!([^!]*?)!!%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $content = str_replace('%%!!' . $mc . '!!%%', '', $content);
            }
        }
    }
    if ( is_user_logged_in() ) 
    {
        $user_id = get_current_user_id();
        if($user_id !== 0)
        {
            preg_match_all('#%%~([^!]*?)~%%#', $content, $matched_content);
            if(isset($matched_content[1][0]))
            {
                foreach($matched_content[1] as $mc)
                {
                    $post_custom_data = get_user_meta($user_id, $mc, true);
                    if($post_custom_data != '')
                    {
                        $content = str_replace('%%~' . $mc . '~%%', $post_custom_data, $content);
                    }
                    else
                    {
                        $content = str_replace('%%~' . $mc . '~%%', '', $content);
                    }
                }
            }
        }
        else
        {
            preg_match_all('#%%~([^!]*?)~%%#', $content, $matched_content);
            if(isset($matched_content[1][0]))
            {
                foreach($matched_content[1] as $mc)
                {
                    $content = str_replace('%%~' . $mc . '~%%', '', $content);
                }
            }
        }
    } 
    else 
    {
        preg_match_all('#%%~([^!]*?)~%%#', $content, $matched_content);
        if(isset($matched_content[1][0]))
        {
            foreach($matched_content[1] as $mc)
            {
                $content = str_replace('%%~' . $mc . '~%%', '', $content);
            }
        }
    }
    $content = preg_replace_callback('#%%random_image_url\[([^\]]*?)\]%%#', function ($matches) {
        $arv = array();
        $my_img = aiomatic_get_random_image_google($matches[1], 0, 0, '', $arv);
        return $my_img;
    }, $content);
    $content = preg_replace_callback('#%%random_image\[([^\]]*?)\](\[\d+\])?%%#', function ($matches) {
        if(isset($matches[2]))
        {
            $chance = trim($matches[2], '[]');
        }
        else
        {
            $chance = '';
        }
        $arv = array();
        $my_img = aiomatic_get_random_image_google($matches[1], 0, 0, $chance, $arv);
        return '<img src="' . $my_img . '">';
    }, $content);
    $content = preg_replace_callback('#%%random_video\[([^\]]*?)\](\[\d+\])?%%#', function ($matches) {
        if(isset($matches[2]))
        {
            $chance = trim($matches[2], '[]');
        }
        else
        {
            $chance = '';
        }
        $my_vid = aiomatic_get_video($matches[1], $chance, array());
        return $my_vid;
    }, $content);
    $content = apply_filters('aiomatic_replace_aicontent_shortcode', $content);
    preg_match_all('#%%related_questions_([^%]*?)%%#i', $content, $mxatches);
    if(isset($mxatches[1][0]))
    {
        foreach($mxatches[1] as $googlematch)
        {
            $mtchres = aiomatic_scrape_related_questions($googlematch, 5, '', 1, 1, 0, 0, 2000, '', '');
            if(is_array($mtchres) && !empty($mtchres))
            {
                $quests = array();
                foreach($mtchres as $mra)
                {
                    if(isset($mra['q']))
                    {
                        $quests[] = $mra['q'];
                    }
                }
                $mtchres = implode(',', $quests);
            }
            $content = str_ireplace('%%related_questions_' . $googlematch . '%%', $mtchres, $content);
        }
    }
    return $content;
}
function aiomatic_preg_grep_keys( $pattern, $input, $flags = 0 )
{
    if(!is_array($input))
    {
        return array();
    }
    $keys = preg_grep( $pattern, array_keys( $input ), $flags );
    $vals = array();
    foreach ( $keys as $key )
    {
        $vals[$key] = $input[$key];
    }
    return $vals;
}
function aiomatic_select_ai_image($new_post_title, $image_url)
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['use_image_ai']) && $aiomatic_Main_Settings['use_image_ai'] === 'on') 
    {
        if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
        {
            aiomatic_log_to_file('You need to insert a valid OpenAI/AiomaticAPI API Key for this to work!');
        }
        else
        {
            if (isset($aiomatic_Main_Settings['image_ai_prompt']) && trim($aiomatic_Main_Settings['image_ai_prompt']) != '') 
            {
                $prompt = trim($aiomatic_Main_Settings['image_ai_prompt']);
            }
            else
            {
                $prompt = 'Select an image URL, based on its file name, which matches the best the a post, based on its title. If no matching image can be selected, pick a random one from the list. Respond only with the URL of the selected image and with nothing else. The title of the post is: \"%%post_title%%\" The image URL list is: %%image_list%%';
            }
            if (isset($aiomatic_Main_Settings['image_ai_model']) && trim($aiomatic_Main_Settings['image_ai_model']) != '') 
            {
                $model = trim($aiomatic_Main_Settings['image_ai_model']);
            }
            else
            {
                $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
            }
            if (isset($aiomatic_Main_Settings['img_assistant_id']) && trim($aiomatic_Main_Settings['img_assistant_id']) != '') 
            {
                $img_assistant_id = trim($aiomatic_Main_Settings['img_assistant_id']);
            }
            else
            {
                $img_assistant_id = '';
            }
            $all_models = aiomatic_get_all_models(true);
            if(!in_array($model, $all_models))
            {
                $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
            }
            $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
            $appids = array_filter($appids);
            $token = $appids[array_rand($appids)];
            $prompt = str_replace('%%post_title%%', $new_post_title, $prompt);
            $image_url_arr = explode(',', $image_url);
            $changemade = false;
            foreach($image_url_arr as $this_ind => $img_url_me)
            {
                $img_url_me = trim($img_url_me);
                if(is_numeric($img_url_me))
                {
                    $attachment_url = wp_get_attachment_url($img_url_me);
                    if($attachment_url !== false)
                    {
                        $image_url_arr[$this_ind] = $attachment_url . '(' . $img_url_me . ')';
                        $changemade = true;
                    }
                }
            }
            if($changemade == true)
            {
                $image_url = implode(',', $image_url_arr);
            }
            $prompt = str_replace('%%image_list%%', $image_url, $prompt);
            $query_token_count = count(aiomatic_encode($prompt));
            $max_tokens = aiomatic_get_max_tokens($model);
            $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
            if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
            {
                $string_len = strlen($prompt);
                $string_len = $string_len / 2;
                $string_len = intval(0 - $string_len);
                $prompt = aiomatic_substr($prompt, 0, $string_len);
                $prompt = trim($prompt);
                $query_token_count = count(aiomatic_encode($prompt));
                $available_tokens = $max_tokens - $query_token_count;
            }
            if(empty($prompt))
            {
                aiomatic_log_to_file('Incorrect AI Image Selector prompt provided: ' . print_r($prompt, true));
            }
            else
            {
                $thread_id = '';
                $aierror = '';
                $finish_reason = '';
                $generated_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, 1, 1, 0, 0, false, 'AIImageSelector', 0, $finish_reason, $aierror, false, false, false, '', '', 'user', $img_assistant_id, $thread_id, '', 'disabled', '', false, false, array());
                if($generated_text === false)
                {
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                        aiomatic_log_to_file('Failed to select the AI image for: ' . print_r($prompt, true) . ' - error: ' . $aierror);
                    }
                }
                else
                {
                    $selected_image = aiomatic_sanitize_ai_result($generated_text);
                    if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                        aiomatic_log_to_file('AI image selector result: ' . print_r($selected_image, true));
                    }
                    if(strstr($image_url, $selected_image) !== false)
                    {
                        preg_match_all('#^https?:\/\/.*\((\d+)\)$#i', $selected_image, $imatches);
                        if(isset($imatches[1][0]))
                        {
                            $selected_image = $imatches[1][0];
                        }
                        return $selected_image;
                    }
                    else
                    {
                        if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                            aiomatic_log_to_file('Returned content not found in input URL list: ' . print_r($selected_image, true));
                        }
                    }
                }
            }
        }
    }
    return false;
}
function aiomatic_get_video($new_post_title, $chance = '', $sources = array())
{
    if($chance != '' && is_numeric($chance))
    {
        $chance = intval($chance);
        if(mt_rand(0, 99) >= $chance)
        {
            return '';
        }
    }
    if(empty($sources))
    {
        $sources = array('youtube');
    }
    else
    {
        shuffle($sources);
    }
    $insta_kw = '';
    $retme = '';
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['improve_yt_kw']) && $aiomatic_Main_Settings['improve_yt_kw'] === 'on') 
    {
        if (!isset($aiomatic_Main_Settings['app_id']) || trim($aiomatic_Main_Settings['app_id']) == '') 
        {
            aiomatic_log_to_file('You need to insert a valid OpenAI/AiomaticAPI API Key for this to work!');
        }
        else
        {
            if(in_array('youtube', $sources))
            {
                if (isset($aiomatic_Main_Settings['yt_kw_prompt']) && trim($aiomatic_Main_Settings['yt_kw_prompt']) != '') 
                {
                    $prompt = trim($aiomatic_Main_Settings['yt_kw_prompt']);
                }
                else
                {
                    $prompt = 'Don\'t act as a virtual assistant. Using which keyword or search phrase should I search YouTube, to get the most relevant videos for this text: "%%aiomatic_query%%"';
                }
                if (isset($aiomatic_Main_Settings['yt_kw_model']) && trim($aiomatic_Main_Settings['yt_kw_model']) != '') 
                {
                    $model = trim($aiomatic_Main_Settings['yt_kw_model']);
                }
                else
                {
                    $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                }
                if (isset($aiomatic_Main_Settings['yt_assistant_id']) && trim($aiomatic_Main_Settings['yt_assistant_id']) != '') 
                {
                    $yt_assistant_id = trim($aiomatic_Main_Settings['yt_assistant_id']);
                }
                else
                {
                    $yt_assistant_id = '';
                }
                $all_models = aiomatic_get_all_models(true);
                if(!in_array($model, $all_models))
                {
                    $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                }
                $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                $appids = array_filter($appids);
                $token = $appids[array_rand($appids)];
                $prompt = str_replace('%%query%%', $new_post_title, $prompt);
                $prompt = str_replace('%%aiomatic_query%%', $new_post_title, $prompt);
                $query_token_count = count(aiomatic_encode($prompt));
                $max_tokens = aiomatic_get_max_tokens($model);
                $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                {
                    $string_len = strlen($prompt);
                    $string_len = $string_len / 2;
                    $string_len = intval(0 - $string_len);
                    $prompt = aiomatic_substr($prompt, 0, $string_len);
                    $prompt = trim($prompt);
                    $query_token_count = count(aiomatic_encode($prompt));
                    $available_tokens = $max_tokens - $query_token_count;
                }
                if(empty($prompt))
                {
                    aiomatic_log_to_file('Incorrect YouTube search keyword extractor prompt provided: ' . print_r($prompt, true));
                }
                else
                {
                    $thread_id = '';
                    $aierror = '';
                    $finish_reason = '';
                    $generated_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, 1, 1, 0, 0, false, 'YouTubeKeywordWriter', 0, $finish_reason, $aierror, false, false, false, '', '', 'user', $yt_assistant_id, $thread_id, '', 'disabled', '', false, false, array());
                    if($generated_text === false)
                    {
                        aiomatic_log_to_file('Failed to extract YouTube search keywords for: ' . print_r($prompt, true) . ' - error: ' . $aierror);
                    }
                    else
                    {
                        $new_post_title = aiomatic_sanitize_ai_result($generated_text);
                    }
                }
            }
            if(in_array('instagram', $sources))
            {
                if (isset($aiomatic_Main_Settings['insta_kw_prompt']) && trim($aiomatic_Main_Settings['insta_kw_prompt']) != '') 
                {
                    $prompt = trim($aiomatic_Main_Settings['insta_kw_prompt']);
                }
                else
                {
                    $prompt = 'Don\'t act as a virtual assistant, reply only with the hashtag, as it will be used automatically for search. Using which single hashtag should I search Instagram, to get the most relevant media for this text: "%%aiomatic_query%%"';
                }
                if (isset($aiomatic_Main_Settings['yt_kw_model']) && trim($aiomatic_Main_Settings['yt_kw_model']) != '') 
                {
                    $model = trim($aiomatic_Main_Settings['yt_kw_model']);
                }
                else
                {
                    $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                }
                if (isset($aiomatic_Main_Settings['yt_assistant_id']) && trim($aiomatic_Main_Settings['yt_assistant_id']) != '') 
                {
                    $yt_assistant_id = trim($aiomatic_Main_Settings['yt_assistant_id']);
                }
                else
                {
                    $yt_assistant_id = '';
                }
                $all_models = aiomatic_get_all_models(true);
                if(!in_array($model, $all_models))
                {
                    $model = aiomatic_get_default_model_name($aiomatic_Main_Settings);
                }
                $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
                $appids = array_filter($appids);
                $token = $appids[array_rand($appids)];
                $prompt = str_replace('%%query%%', $new_post_title, $prompt);
                $prompt = str_replace('%%aiomatic_query%%', $new_post_title, $prompt);
                $query_token_count = count(aiomatic_encode($prompt));
                $max_tokens = aiomatic_get_max_tokens($model);
                $available_tokens = aiomatic_compute_available_tokens($model, $max_tokens, $prompt, $query_token_count);
                if($available_tokens <= AIOMATIC_MINIMUM_TOKENS_FOR_COMPLETIONS)
                {
                    $string_len = strlen($prompt);
                    $string_len = $string_len / 2;
                    $string_len = intval(0 - $string_len);
                    $prompt = aiomatic_substr($prompt, 0, $string_len);
                    $prompt = trim($prompt);
                    $query_token_count = count(aiomatic_encode($prompt));
                    $available_tokens = $max_tokens - $query_token_count;
                }
                if(empty($prompt))
                {
                    aiomatic_log_to_file('Incorrect Instagram search keyword extractor prompt provided: ' . print_r($prompt, true));
                }
                else
                {
                    $thread_id = '';
                    $aierror = '';
                    $finish_reason = '';
                    $generated_text = aiomatic_generate_text($token, $model, $prompt, $available_tokens, 1, 1, 0, 0, false, 'InstagramHashtagWriter', 0, $finish_reason, $aierror, false, false, false, '', '', 'user', $yt_assistant_id, $thread_id, '', 'disabled', '', false, false, array());
                    if($generated_text === false)
                    {
                        aiomatic_log_to_file('Failed to extract Instagram search keywords for: ' . print_r($prompt, true) . ' - error: ' . $aierror);
                    }
                    else
                    {
                        $insta_kw = aiomatic_sanitize_ai_result($generated_text);
                    }
                }
            }
        }
    }
    require_once (dirname(__FILE__) . "/aiomatic-socials-query.php"); 
    while (empty($retme) && !empty($sources))
    {
        $current_source = array_shift($sources);
        if($current_source == 'youtube')
        {
            if (isset($aiomatic_Main_Settings['yt_app_id']) && trim($aiomatic_Main_Settings['yt_app_id']) != '') {
                $items = array();
                $za_app = explode(',', $aiomatic_Main_Settings['yt_app_id']);
                $za_app = trim($za_app[array_rand($za_app)]);
                $feed_uri = 'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&key=' . $za_app;
                $feed_uri .= '&maxResults=10';
                $feed_uri .= '&q='.urlencode(trim(stripslashes(str_replace('&quot;', '"', $new_post_title))));
                $ch  = curl_init();
                if ($ch !== FALSE) 
                {
                    if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                        $randomness = array_rand($prx);
                        curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
                        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                        {
                            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                            {
                                curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
                            }
                        }
                    }
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
                    {
                        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
                    }
                    else
                    {
                        $ztime = 300;
                    }
                    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                    curl_setopt($ch, CURLOPT_REFERER, get_site_url());
                    curl_setopt($ch, CURLOPT_URL, $feed_uri);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $exec = curl_exec($ch);
                    curl_close($ch);
                    if ($exec !== FALSE) {
                        $json  = json_decode($exec);
                        if(isset($json->items))
                        {
                            $items = $json->items;
                            if (count($items) == 0) 
                            {
                                $feed_uri = 'https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&key=' . $za_app;
                                $feed_uri .= '&maxResults=10';
                                $keyword_class = new Aiomatic_keywords();
                                $new_post_title = $keyword_class->keywords($new_post_title, 1);
                                $feed_uri .= '&q='.urlencode(trim(stripslashes(str_replace('&quot;', '"', $new_post_title))));
                                $ch  = curl_init();
                                if ($ch !== FALSE) 
                                {
                                    if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                                        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                                        $randomness = array_rand($prx);
                                        curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
                                        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                                        {
                                            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                                            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                                            {
                                                curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
                                            }
                                        }
                                    }
                                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
                                    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
                                    {
                                        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
                                    }
                                    else
                                    {
                                        $ztime = 300;
                                    }
                                    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
                                    curl_setopt($ch, CURLOPT_HTTPGET, 1);
                                    curl_setopt($ch, CURLOPT_REFERER, get_site_url());
                                    curl_setopt($ch, CURLOPT_URL, $feed_uri);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                                    $exec = curl_exec($ch);
                                    curl_close($ch);
                                    if ($exec === FALSE) {
                                        $json  = json_decode($exec);
                                        if(isset($json->items))
                                        {
                                            $items = $json->items;
                                        }
                                    }
                                }
                                else
                                {
                                    aiomatic_log_to_file('Failed to init curl in YouTube API listing x2: ' . $feed_uri);
                                }
                            }
                        }
                        else
                        {
                            if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                                aiomatic_log_to_file('YouTube API returned error: ' . $exec);
                            }
                        }
                    }
                }
                else
                {
                    aiomatic_log_to_file('Failed to init curl in YouTube API listing: ' . $feed_uri);
                }
                if(isset($items[0]->id->videoId))
                {
                    $rand_ind = array_rand($items);
                    if(isset($items[$rand_ind]->id->videoId))
                    {
                        $video_id = $items[$rand_ind]->id->videoId;
                    }
                    else
                    {
                        $video_id = $items[0]->id->videoId;
                    }
                    $video_id = $items[$rand_ind]->id->videoId;
                    if (isset($aiomatic_Main_Settings['player_width']) && $aiomatic_Main_Settings['player_width'] !== '') {
                        $width = esc_attr($aiomatic_Main_Settings['player_width']);
                    }
                    else
                    {
                        $width = 580;
                    }
                    if (isset($aiomatic_Main_Settings['player_height']) && $aiomatic_Main_Settings['player_height'] !== '') {
                        $height = esc_attr($aiomatic_Main_Settings['player_height']);
                    }
                    else
                    {
                        $height = 380;
                    }
                    $retme = '<br/><br/><div class="automaticx-video-container"><iframe allow="autoplay" width="' . $width . '" height="' . $height . '" src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                }
            }
            else
            {
                $retme = aiomatic_get_youtube_video(trim(stripslashes(str_replace('&quot;', '"', $new_post_title))), $chance);
            }
        }
        elseif($current_source == 'twitter')
        {
            $error = '';
            $rezultat = aiomatic_twitter_get_embed($new_post_title, $error);
            if($rezultat === false)
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                    aiomatic_log_to_file('X (Twitter) query returned error: ' . $error);
                }
            }
            else
            {
                $retme = $rezultat;
            }
        }
        elseif($current_source == 'instagram')
        {
            if(empty($insta_kw))
            {
                $insta_kw = $new_post_title;
            }
            $error = '';
            $rezultat = aiomatic_instagram_get_embed($insta_kw, $error);
            if($rezultat === false)
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                    aiomatic_log_to_file('Instagram query returned error: ' . $error);
                }
            }
            else
            {
                $retme = $rezultat;
            }
        }
        elseif($current_source == 'pinterest')
        {
            $error = '';
            $rezultat = aiomatic_pinterest_get_embed($new_post_title, $error);
            if($rezultat === false)
            {
                if (isset($aiomatic_Main_Settings['enable_detailed_logging'])) {
                    aiomatic_log_to_file('Pinterest query returned error: ' . $error);
                }
            }
            else
            {
                $retme = $rezultat;
            }
        }
    }
    return $retme;
}
function aiomatic_get_youtube_video($keyword, $chance = '')
{
    if($chance != '' && is_numeric($chance))
    {
        $chance = intval($chance);
        if(mt_rand(0, 99) >= $chance)
        {
            return '';
        }
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (isset($aiomatic_Main_Settings['player_width']) && $aiomatic_Main_Settings['player_width'] !== '') {
        $width = esc_attr($aiomatic_Main_Settings['player_width']);
    }
    else
    {
        $width = 580;
    }
    if (isset($aiomatic_Main_Settings['player_height']) && $aiomatic_Main_Settings['player_height'] !== '') {
        $height = esc_attr($aiomatic_Main_Settings['player_height']);
    }
    else
    {
        $height = 380;
    }
    $res = aiomatic_file_get_contents_advanced('https://www.youtube.com/results?search_query=' . urlencode($keyword), '', 'self', 'Mozilla/5.0 (Windows NT 10.0;WOW64;rv:97.0) Gecko/20100101 Firefox/97.0/3871tuT2p1u-81');
    preg_match_all('/"\/watch\?v=([^"&?\/\s]{11})"/', $res, $matches);
    if(isset($matches[1]))
    {
        $items = $matches[1];
        if (count($items) > 0) 
        {
            return '<br/><br/><div class="automaticx-video-container"><iframe allow="autoplay" width="' . $width . '" height="' . $height . '" src="https://www.youtube.com/embed/' . $items[rand(0, count($items) - 1)] . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
        }
    }
    return '';
}
function aiomatic_generate_thumbmail( $post_id )
{
    $post = get_post($post_id);
    $post_parent_id = $post->post_parent === 0 ? $post->ID : $post->post_parent;
    if ( has_post_thumbnail($post_parent_id) )
    {
        if ($id_attachment = get_post_thumbnail_id($post_parent_id)) {
            $the_image  = wp_get_attachment_url($id_attachment, false);
            return $the_image;
        }
    }
    $attachments = array_values(get_children(array(
        'post_parent' => $post_parent_id, 
        'post_status' => 'inherit', 
        'post_type' => 'attachment', 
        'post_mime_type' => 'image', 
        'order' => 'ASC', 
        'orderby' => 'menu_order ID') 
    ));
    if( sizeof($attachments) > 0 ) {
        $the_image  = wp_get_attachment_url($attachments[0]->ID, false);
        return $the_image;
    }
    $image_url = aiomatic_extractThumbnail($post->post_content);
    return $image_url;
}
function aiomatic_extractThumbnail($content) 
{
    $att = aiomatic_getUrls($content);
    if(count($att) > 0)
    {
        foreach($att as $link)
        {
            if(aiomatic_is_image_url($link))
            {
                $mime = aiomatic_get_mime($link);
                if(stristr($mime, "image/") !== FALSE){
                    return $link;
                }
            }
        }
    }
    else
    {
        return '';
    }
    return '';
}
function aiomatic_is_image_url($l) 
{
    $arr = explode("?", $l);
    return preg_match("#\.(jpg|jpeg|gif|png)$#i", $arr[0]);
}
function aiomatic_getUrls($string) {
    $regex = '/https?\:\/\/[^\"\' \n\s]+/i';
    preg_match_all($regex, $string, $matches);
    return ($matches[0]);
}

function aiomatic_strip_html_tags($str)
{
    $str = html_entity_decode($str);
    $str1 = preg_replace('/(<|>)\1{2}/is', '', $str);
    if($str1 !== null)
    {
        $str = $str1;
    }
    $str1 = preg_replace(array(
        '@<head[^>]*?>.*?</head>@siu',
        '@<style[^>]*?>.*?</style>@siu',
        '@<script[^>]*?.*?</script>@siu',
        '@<noscript[^>]*?.*?</noscript>@siu'
    ), "", $str);
    if($str1 !== null)
    {
        $str = $str1;
    }
    $str = str_replace('><', '> <', $str);
    $str = strip_tags($str);
    return $str;
}
function aiomatic_get_base64_from_url($image_url) 
{
    $image_content = aiomatic_get_web_page($image_url);
    if ($image_content == false) {
        return false;
    }
    $base64_image = base64_encode($image_content);
    return $base64_image;
}
function aiomatic_base64_to_jpeg($base64_string, $output_file, $ret_path) 
{
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
       wp_filesystem($creds);
    }
    if ($wp_filesystem->exists($output_file)) 
    {
        return array($output_file, $ret_path); 
    }
    $ifp = fopen($output_file, 'wb'); 
    if($ifp !== false)
    {
        $decoded = base64_decode($base64_string);
        if($ifp !== false)
        {
            $decoded_temp = aiomatic_string_to_string_compress($decoded);
            if($decoded_temp !== false)
            {
                $decoded = $decoded_temp;
            }
            $rez = fwrite($ifp, $decoded);
            if($rez === false)
            {
                aiomatic_log_to_file('Failed to write file: ' . $output_file);
                return false;
            }
        }
        else
        {
            aiomatic_log_to_file('Failed to decode response file: ' . $base64_string);
            return false;
        }
        fclose($ifp);
    }
    else
    {
        aiomatic_log_to_file('Failed to open file: ' . $output_file);
        return false;
    }
    return array($output_file, $ret_path); 
}
function aiomatic_base64_to_file($base64_string, $output_file, $ret_path) 
{
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
        include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
       wp_filesystem($creds);
    }
    if ($wp_filesystem->exists($output_file)) 
    {
        return array($output_file, $ret_path); 
    }
    $ifp = fopen($output_file, 'wb'); 
    if($ifp !== false)
    {
        $decoded = base64_decode($base64_string);
        if($ifp !== false)
        {
            $rez = fwrite($ifp, $decoded);
            if($rez === false)
            {
                aiomatic_log_to_file('Failed to write file: ' . $output_file);
                return false;
            }
        }
        else
        {
            aiomatic_log_to_file('Failed to decode response file: ' . $base64_string);
            return false;
        }
        fclose($ifp);
    }
    else
    {
        aiomatic_log_to_file('Failed to open file: ' . $output_file);
        return false;
    }
    return array($output_file, $ret_path); 
}
function aiomatic_random_sentence_generator($first = true)
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if ($first == false) {
        $r_sentences = $aiomatic_Main_Settings['sentence_list2'];
    } else {
        $r_sentences = $aiomatic_Main_Settings['sentence_list'];
    }
    $r_variables = $aiomatic_Main_Settings['variable_list'];
    $r_sentences = trim($r_sentences);
    $r_variables = trim($r_variables, ';');
    $r_variables = trim($r_variables);
    $r_sentences = str_replace("\r\n", "\n", $r_sentences);
    $r_sentences = str_replace("\r", "\n", $r_sentences);
    $r_sentences = explode("\n", $r_sentences);
    $r_variables = str_replace("\r\n", "\n", $r_variables);
    $r_variables = str_replace("\r", "\n", $r_variables);
    $r_variables = explode("\n", $r_variables);
    $r_vars      = array();
    for ($x = 0; $x < count($r_variables); $x++) {
        $var = explode("=>", trim($r_variables[$x]));
        if (isset($var[1])) {
            $key          = strtolower(trim($var[0]));
            $words        = explode(";", trim($var[1]));
            $r_vars[$key] = $words;
        }
    }
    $max_s    = count($r_sentences) - 1;
    $rand_s   = rand(0, $max_s);
    $sentence = $r_sentences[$rand_s];
    $sentence = str_replace(' ,', ',', ucfirst(aiomatic_replace_words($sentence, $r_vars)));
    $sentence = str_replace(' .', '.', $sentence);
    $sentence = str_replace(' !', '!', $sentence);
    $sentence = str_replace(' ?', '?', $sentence);
    $sentence = trim($sentence);
    return $sentence;
}
function aiomatic_get_random_user_agent() {
    $os_type = rand(1, 3);
    $chrome_version = rand(100, 108) . '.0.' . rand(1, 4044) . '.' . rand(0, 138);
    if ($os_type == 1) {
        $agent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";
    } elseif ($os_type == 2) {
        $agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";
        $mac_version = "10_" . rand(6, 15) . "_" . rand(1, 4);
        $agent = str_replace('10_15_4', $mac_version, $agent);
    } elseif ($os_type == 3) {
        $agent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36";
    }
    $agent = str_replace("81.0.4044.138", $chrome_version, $agent);
    return $agent;
}
function aiomatic_admin_footer()
{
?>
    <div class="aiomatic-overlay" style="display: none">
        <div class="aiomatic_modal">
            <div class="aiomatic_modal_head">
                <span class="aiomatic_modal_title"><?php echo esc_html__('Aiomatic', 'aiomatic-automatic-ai-content-writer');?></span>
                <span class="aiomatic_modal_close">&times;</span>
            </div>
            <div class="aiomatic_modal_content"></div>
        </div>
    </div>
    <div class="wpcgai_lds-ellipsis" style="display: none">
        <div class="aiomatic-generating-title"><?php echo esc_html__('Loading content...', 'aiomatic-automatic-ai-content-writer');?></div>
        <div class="aiomatic-generating-process"></div>
        <div class="aiomatic-timer"></div>
    </div>
<?php
}
function aiomatic_assign_var(&$target, $var, $root = false) {
	static $cnt = 0;
    $key = key($var);
    if(is_array($var[$key])) 
        aiomatic_assign_var($target[$key], $var[$key], false);
    else {
        if($key==0)
		{
			if($cnt == 0 && $root == true)
			{
				$target['_aiomaticr_nonce'] = $var[$key];
				$cnt++;
			}
			elseif($cnt == 1 && $root == true)
			{
				$target['_wp_http_referer'] = $var[$key];
				$cnt++;
			}
			else
			{
				$target[] = $var[$key];
			}
		}
        else
		{
            $target[$key] = $var[$key];
		}
    }   
}
function aiomatic_generate_filename_from_prompt($prompt, $maxKeywords = 5) 
{
    $cleanPrompt = preg_replace('/[^a-zA-Z0-9\s]/', '', $prompt);
    $words = explode(' ', strtolower($cleanPrompt)); 
    $stopWords = ['the', 'and', 'or', 'but', 'on', 'in', 'with', 'at', 'of', 'a', 'an', 'to', 'for', 'is', 'are'];
    $keywords = array_filter($words, function ($word) use ($stopWords) {
        return !in_array($word, $stopWords) && strlen($word) > 2; 
    });
    $selectedKeywords = array_slice($keywords, 0, $maxKeywords);
    if(empty($selectedKeywords))
    {
        return false;
    }
    $fileName = implode('-', $selectedKeywords);
    $fileName = substr($fileName, 0, 60);
    return $fileName;
}
function aiomatic_utf8ize($arr){
    if (is_array($arr)) {
        foreach ($arr as $k => $v) {
            $arr[$k] = aiomatic_utf8ize($v);
        }
    } else if (is_string ($arr)) {
        return aiomatic_utf8_encode($arr);
    }
    return $arr;
}
function aiomatic_safe_json_encode($value){
    $encoded = json_encode($value);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            return $encoded;
        case JSON_ERROR_DEPTH:
            throw new Exception('Maximum stack depth exceeded');
        case JSON_ERROR_STATE_MISMATCH:
            throw new Exception('Underflow or the modes mismatch');
        case JSON_ERROR_CTRL_CHAR:
            throw new Exception('Unexpected control character found');
        case JSON_ERROR_SYNTAX:
            throw new Exception('Syntax error, malformed JSON');
        case JSON_ERROR_UTF8:
            $clean = aiomatic_utf8ize($value);
            return aiomatic_safe_json_encode($clean);
        default:
            throw new Exception('Unknown error in json encoding');
    }
}
function aiomatic_split_to_token_len($tokens, $max_len)
{
    $ret_me = array();
    if(count($tokens) > $max_len)
    {
        $chunks = array_chunk($tokens, $max_len, true);
        foreach($chunks as $thisch)
        {
            $ret_me[] = aiomatic_decode($thisch);
        }
    }
    else
    {
        $ret_me[] = aiomatic_decode($tokens);
    }
    return $ret_me;
}
use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;
use Gioni06\Gpt3Tokenizer\Gpt3Tokenizer;
function aiomatic_decode($tokens) 
{
    if (version_compare(PHP_VERSION, '8.0.2', '>=') && extension_loaded('mbstring')) 
    {
        require_once (dirname(__FILE__) . "/res/tokenizer/Gpt3TokenizerConfig.php"); 
        require_once (dirname(__FILE__) . "/res/tokenizer/Merges.php"); 
        require_once (dirname(__FILE__) . "/res/tokenizer/Vocab.php"); 
        require_once (dirname(__FILE__) . "/res/tokenizer/Gpt3Tokenizer.php"); 
        $config = new Gpt3TokenizerConfig();
        $tokenizer = new Gpt3Tokenizer($config);
        $text = $tokenizer->decode($tokens);
        return $text;
    }
    else
    {
        return aiomatic_decode_old($tokens);
    }
}
function aiomatic_decode_old($tokens) 
{
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') )
    {
        include_once(ABSPATH . 'wp-admin/includes/file.php');
        $creds = request_filesystem_credentials( site_url() );
        wp_filesystem($creds);
    }
    $rencoder = $wp_filesystem->get_contents(dirname(__FILE__) . "/res/encoder.json");
    $encoder = json_decode($rencoder, true);
    if(empty($encoder))
    {
        aiomatic_log_to_file('Failed to load encoder.json: ' . $rencoder);
        return false;
    }
    $decoder = array();
    foreach($encoder as $index => $val)
    {
        $decoder[$val] = $index;
    }
    $raw_chars = $wp_filesystem->get_contents(dirname(__FILE__) . "/res/characters.json");
    $byte_encoder = json_decode($raw_chars, true);
    if(empty($byte_encoder))
    {
        aiomatic_log_to_file('Failed to load characters.json: ' . $raw_chars);
        return false;
    }
    $byte_decoder = array();
    foreach($byte_encoder as $index => $val)
    {
        $byte_decoder[$val] = $index;
    }
    $text = '';
    $mych_arr = [];
    foreach($tokens as $myt)
    {
        if(isset($decoder[$myt]))
        {
            $mych_arr[] = $decoder[$myt];
        }
        else
        {
            aiomatic_log_to_file('Character not found in decoder: ' . $myt);
        }
    }
    $text = implode('', $mych_arr);
    $text_arr = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $final_arr = array();
    foreach($text_arr as $txa)
    {
        if(isset($byte_decoder[$txa]))
        {
            $final_arr[] = $byte_decoder[$txa];
        }
        else
        {
            aiomatic_log_to_file('Character not found in byte_decoder: ' . $txa);
        }
    }
    $output = '';
    for ($i = 0, $j = count($final_arr); $i < $j; ++$i) {
        $output .= chr($final_arr[$i]);
    }
    return $output;
}
function aiomatic_encode($text) 
{
    if (version_compare(PHP_VERSION, '8.0.2', '>=') && extension_loaded('mbstring')) 
    {
        require_once (dirname(__FILE__) . "/res/tokenizer/Gpt3TokenizerConfig.php"); 
        require_once (dirname(__FILE__) . "/res/tokenizer/Merges.php"); 
        require_once (dirname(__FILE__) . "/res/tokenizer/Vocab.php"); 
        require_once (dirname(__FILE__) . "/res/tokenizer/Gpt3Tokenizer.php"); 
        $config = new Gpt3TokenizerConfig();
        $tokenizer = new Gpt3Tokenizer($config);
        $tokens = $tokenizer->encode($text);
        return $tokens;
    }
    else
    {
        return aiomatic_encode_old($text);
    }
}
function aiomatic_encode_old($text) 
{
    $bpe_tokens = array();
    if(empty($text))
    {
        return $bpe_tokens;
    }
    global $wp_filesystem;
    if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') )
    {
        include_once(ABSPATH . 'wp-admin/includes/file.php');
        $creds = request_filesystem_credentials( site_url() );
        wp_filesystem($creds);
    }
    $raw_chars = $wp_filesystem->get_contents(dirname(__FILE__) . "/res/characters.json");
    $byte_encoder = json_decode($raw_chars, true);
    if(empty($byte_encoder))
    {
        aiomatic_log_to_file('Failed to load characters.json: ' . $raw_chars);
        return $bpe_tokens;
    }
    $rencoder = $wp_filesystem->get_contents(dirname(__FILE__) . "/res/encoder.json");
    $encoder = json_decode($rencoder, true);
    if(empty($encoder))
    {
        aiomatic_log_to_file('Failed to load encoder.json: ' . $rencoder);
        return $bpe_tokens;
    }

    $bpe_file = $wp_filesystem->get_contents(dirname(__FILE__) . "/res/vocab.bpe");
    if(empty($bpe_file))
    {
        aiomatic_log_to_file('Failed to load vocab.bpe');
        return $bpe_tokens;
    }
    $text = str_replace ("\r\n", "\n", $text);
    preg_match_all("#'s|'t|'re|'ve|'m|'ll|'d| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+#u", $text, $matches);
    if(!isset($matches[0]) || count($matches[0]) == 0)
    {
        aiomatic_log_to_file('Failed to match string: ' . $text);
        return $bpe_tokens;
    }
    $lines = preg_split('/\r\n|\r|\n/', $bpe_file);
    $bpe_merges = array();
    $bpe_merges_temp = array_slice($lines, 1, count($lines), true);
    foreach($bpe_merges_temp as $bmt)
    {
        $split_bmt = preg_split('#(\s+)#', $bmt);
        $split_bmt = array_filter($split_bmt, 'aiomatic_myFilter');
        if(count($split_bmt) > 0)
        {
            $bpe_merges[] = $split_bmt;
        }
    }
    $bpe_ranks = aiomatic_dictZip($bpe_merges, range(0, count($bpe_merges) - 1));
    
    $cache = array();
    foreach($matches[0] as $token)
    {
        $new_tokens = array();
        $chars = array();
        $token = aiomatic_utf8_encode($token);
        if(function_exists('mb_strlen'))
        {
            $len = mb_strlen($token, 'UTF-8');
            for ($i = 0; $i < $len; $i++) {
                $chars[] = mb_substr($token, $i, 1, 'UTF-8');
            }
        }
        else
        {
            $chars = str_split($token);
        }
        $result_word = '';
        foreach($chars as $char)
        {
            if(isset($byte_encoder[aiomatic_unichr($char)]))
            {
                $result_word .= $byte_encoder[aiomatic_unichr($char)];
            }
        }
        $new_tokens_bpe = aiomatic_bpe($result_word, $bpe_ranks, $cache);
        $new_tokens_bpe = explode(' ', $new_tokens_bpe);
        foreach($new_tokens_bpe as $x)
        {
            if(isset($encoder[$x]))
            {
                if(isset($new_tokens[$x]))
                {
                    $new_tokens[rand() . '---' . $x] = $encoder[$x];
                }
                else
                {
                    $new_tokens[$x] = $encoder[$x];
                }
            }
            else
            {
                if(isset($new_tokens[$x]))
                {
                    $new_tokens[rand() . '---' . $x] = $x;
                }
                else
                {
                    $new_tokens[$x] = $x;
                }
            }
        }
        foreach($new_tokens as $ninx => $nval)
        {
            if(isset($bpe_tokens[$ninx]))
            {
                $bpe_tokens[rand() . '---' . $ninx] = $nval;
            }
            else
            {
                $bpe_tokens[$ninx] = $nval;
            }
        }
    }
    return $bpe_tokens;
}
function aiomatic_myFilter($var)
{
    return ($var !== NULL && $var !== FALSE && $var !== '');
}

function aiomatic_unichr($c) 
{
    if (ord($c[0]) >=0 && ord($c[0]) <= 127)
    {
        return ord($c[0]);
    }
    if (ord($c[0]) >= 192 && ord($c[0]) <= 223)
    {
        return (ord($c[0])-192)*64 + (ord($c[1])-128);
    }
    if (ord($c[0]) >= 224 && ord($c[0]) <= 239)
    {
        return (ord($c[0])-224)*4096 + (ord($c[1])-128)*64 + (ord($c[2])-128);
    }
    if (ord($c[0]) >= 240 && ord($c[0]) <= 247)
    {
        return (ord($c[0])-240)*262144 + (ord($c[1])-128)*4096 + (ord($c[2])-128)*64 + (ord($c[3])-128);
    }
    if (ord($c[0]) >= 248 && ord($c[0]) <= 251)
    {
        return (ord($c[0])-248)*16777216 + (ord($c[1])-128)*262144 + (ord($c[2])-128)*4096 + (ord($c[3])-128)*64 + (ord($c[4])-128);
    }
    if (ord($c[0]) >= 252 && ord($c[0]) <= 253)
    {
        return (ord($c[0])-252)*1073741824 + (ord($c[1])-128)*16777216 + (ord($c[2])-128)*262144 + (ord($c[3])-128)*4096 + (ord($c[4])-128)*64 + (ord($c[5])-128);
    }
    if (ord($c[0]) >= 254 && ord($c[0]) <= 255)
    {
        return 0;
    }
    return 0;
}
function aiomatic_dictZip($x, $y)
{
    $result = array();
    $cnt = 0;
    foreach($x as $i)
    {
        if(isset($i[1]) && isset($i[0]))
        {
            $result[$i[0] . ',' . $i[1]] = $cnt;
            $cnt++;
        }
    }
    return $result;
}
function aiomatic_get_pairs($word) 
{
    $pairs = array();
    $prev_char = $word[0];
    for ($i = 1; $i < count($word); $i++) 
    {
        $char = $word[$i];
        $pairs[] = array($prev_char, $char);
        $prev_char = $char;
    }
    return $pairs;
}
function aiomatic_split($str, $len = 1) 
{
    $arr		= [];
    if(function_exists('mb_strlen'))
    {
        $length 	= mb_strlen($str, 'UTF-8');
    }
    else
    {
        $length 	= strlen($str);
    }

    for ($i = 0; $i < $length; $i += $len) 
    {
        if(function_exists('mb_substr'))
        {
            $arr[] = mb_substr($str, $i, $len, 'UTF-8');
        }
        else
        {
            $arr[] = substr($str, $i, $len);
        }
    }
    return $arr;

}
function aiomatic_bpe($token, $bpe_ranks, &$cache)
{
    if(array_key_exists($token, $cache))
    {
        return $cache[$token];
    }
    $word = aiomatic_split($token);
    $init_len = count($word);
    $pairs = aiomatic_get_pairs($word);
    if(!$pairs)
    {
        return $token;
    }
    while (true) 
    {
        $minPairs = array();
        
        foreach($pairs as $pair)
        {
            if(array_key_exists($pair[0] . ','. $pair[1], $bpe_ranks))
            {
                $rank = $bpe_ranks[$pair[0] . ','. $pair[1]];
                $minPairs[$rank] = $pair;
            }
            else
            { 
                $minPairs[10e10] = $pair;
            }
        }
        ksort($minPairs);
        if(!function_exists('array_key_first'))
        {
            function array_key_first(array $array) { foreach ($array as $key => $value) { return $key; } }
        }
        $min_key = array_key_first($minPairs);
        foreach($minPairs as $mpi => $mp)
        {
            if($mpi < $min_key)
            {
                $min_key = $mpi;
            }
        }
        $bigram = $minPairs[$min_key];
        if(!array_key_exists($bigram[0] . ',' . $bigram[1], $bpe_ranks))
        {
            break;
        }
        $first = $bigram[0];
        $second = $bigram[1];
        $new_word = array();
        $i = 0;
        while ($i < count($word)) 
        {
            $j = aiomatic_indexOf($word, $first, $i);
            if ($j === -1) 
            {
                $new_word = array_merge($new_word, array_slice($word, $i, null, true));
                break;
            }
            if($i > $j)
            {
                $slicer = array();
            }
            elseif($j == 0)
            {
                $slicer = array();
            }
            else
            {
                $slicer = array_slice($word, $i, $j - $i, true);
            }
            $new_word = array_merge($new_word, $slicer);
            if(count($new_word) > $init_len)
            {
                break;
            }
            $i = $j;
            if ($word[$i] === $first && $i < count($word) - 1 && $word[$i + 1] === $second) 
            {
                array_push($new_word, $first . $second);
                $i = $i + 2;
            }
            else
            {
                array_push($new_word, $word[$i]);
                $i = $i + 1;
            }
        }
        if($word == $new_word)
        {
            break;
        }
        $word = $new_word;
        if (count($word) === 1) 
        {
            break;
        }
        else
        {
            $pairs = aiomatic_get_pairs($word);
        }
    }
    $word = implode(' ', $word);
    $cache[$token] = $word;
    return $word;
}
function aiomatic_get_web_page($url)
{
    if(empty($url))
    {
        return false;
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $content = false;
    if (!isset($aiomatic_Main_Settings['proxy_url']) || $aiomatic_Main_Settings['proxy_url'] == '' || $aiomatic_Main_Settings['proxy_url'] == 'disable' || $aiomatic_Main_Settings['proxy_url'] == 'disabled') 
    {
        $args = array(
        'timeout'     => 180,
        'redirection' => 10,
        'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
        'blocking'    => true,
        'headers'     => array(),
        'cookies'     => array(),
        'body'        => null,
        'compress'    => false,
        'decompress'  => true,
        'sslverify'   => false,
        'stream'      => false,
        'filename'    => null
        );
        $ret_data            = wp_remote_get(html_entity_decode($url), $args);  
        $response_code       = wp_remote_retrieve_response_code( $ret_data );      
        if ( 200 != $response_code ) {
        } else {
            $content = wp_remote_retrieve_body( $ret_data );
        }
    }
    if($content === false)
    {
        if(function_exists('curl_version') && filter_var($url, FILTER_VALIDATE_URL))
        {
            $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36';
            $options    = array(
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POST => false,
                CURLOPT_USERAGENT => $user_agent,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_ENCODING => "",
                CURLOPT_AUTOREFERER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0
            );
            $ch         = curl_init($url);
            if ($ch === FALSE) {
                return FALSE;
            }
            if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') {
                $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
                $randomness = array_rand($prx);
                $options[CURLOPT_PROXY] = trim($prx[$randomness]);
                if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
                {
                    $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
                    if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
                    {
                        $options[CURLOPT_PROXYUSERPWD] = trim($prx_auth[$randomness]);
                    }
                }
            }
            curl_setopt_array($ch, $options);
            $content = curl_exec($ch);
            curl_close($ch);
        }
        else
        {
            $allowUrlFopen = preg_match('/1|yes|on|true/i', ini_get('allow_url_fopen'));
            if ($allowUrlFopen) {
                global $wp_filesystem;
                if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
                    include_once(ABSPATH . 'wp-admin/includes/file.php');$creds = request_filesystem_credentials( site_url() );
                    wp_filesystem($creds);
                }
                return $wp_filesystem->get_contents($url);
            }
        }
    }
    return $content;
}
function aiomatic_png_to_jpg_compress($source, $destination, $quality)
{
    $image = imagecreatefrompng($source);
    if($image === false)
    {
        return false;
    }
    $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
    if($bg === false)
    {
        return false;
    }
    $fill = imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
    if($fill === false)
    {
        return false;
    }
    $blend = imagealphablending($bg, TRUE);
    if($blend === false)
    {
        return false;
    }
    $cp = imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
    if($cp === false)
    {
        return false;
    }
    imagedestroy($image);
    $quality = 75;
    $success = imagejpeg($bg, $destination, $quality);
    if($success === false)
    {
        return false;
    }
    imagedestroy($bg);
    return $destination;
}
function aiomatic_string_to_string_compress($image_string) 
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(isset($aiomatic_Main_Settings['disable_compress']) && $aiomatic_Main_Settings['disable_compress'] == 'on')
    {
        return false;
    }
    if(!function_exists('imagecreatefromstring'))
    {
        return false;
    }
    $image = imagecreatefromstring($image_string);
    if ($image === false) {
        return false;
    }
    $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
    if ($bg === false) {
        imagedestroy($image);
        return false;
    }
    $white_background = imagecolorallocate($bg, 255, 255, 255);
    if ($white_background === false) {
        imagedestroy($image);
        return false;
    }
    $fr = imagefilledrectangle($bg, 0, 0, imagesx($image), imagesy($image), $white_background);
    if ($fr === false) {
        imagedestroy($image);
        return false;
    }
    $cr = imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
    if ($cr === false) {
        imagedestroy($image);
        return false;
    }
    ob_start();
    if(isset($aiomatic_Main_Settings['compress_quality']) && $aiomatic_Main_Settings['compress_quality'] != '')
    {
        $quality = intval($aiomatic_Main_Settings['compress_quality']);
    }
    else
    {
        $quality = 75;
    }
    $jp = imagejpeg($bg, NULL, $quality);
    if ($jp === false) {
        imagedestroy($image);
        return false;
    }
    $jpg_image_string = ob_get_contents();
    ob_end_clean();
    imagedestroy($image);
    imagedestroy($bg);
    return $jpg_image_string;
}
function aiomatic_compress_image($source, $destination, $quality) 
{
    $info = getimagesize($source);
    if($info === false)
    {
        return false;
    }
    $image = false;
    if ($info['mime'] == 'image/jpeg') 
    {
        $image = imagecreatefromjpeg($source);
    }
    elseif ($info['mime'] == 'image/gif') 
    {
        $image = imagecreatefromgif($source);
    }
    elseif ($info['mime'] == 'image/png')
    { 
        $image = imagecreatefrompng($source);
    }
    if($image === false)
    {
        return false;
    }
    $success = imagejpeg($image, $destination, $quality);
    if($success === false)
    {
        return false;
    }
    return $destination;
}
function aiomatic_get_web_page_api($url, $post_args = array(), $headers = array())
{
    if(count($post_args) == 0)
    {
        $post_args = null;
    }
    $content = false;
    $args = array(
    'method'      => 'POST',
    'timeout'     => 999,
    'redirection' => 10,
    'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
    'blocking'    => true,
    'headers'     => $headers,
    'cookies'     => array(),
    'body'        => $post_args,
    'compress'    => false,
    'decompress'  => true,
    'sslverify'   => false,
    'stream'      => false,
    'filename'    => null
    );
    $ret_data            = wp_remote_post($url, $args);  
    $response_code       = wp_remote_retrieve_response_code( $ret_data );     
    if ( 200 != $response_code ) {
    } else {
        $content = wp_remote_retrieve_body( $ret_data );
    }
    if($content === false)
    {
        aiomatic_log_to_file('API response code is: ' . $response_code . ' - ' . $url . ' - ' . print_r($post_args, true));
    }
    return $content;
}

function aiomatic_get_web_page_post($url, $post_args = array(), $headers = array())
{
    if(is_array($post_args) && count($post_args) == 0)
    {
        $post_args = null;
    }
    $content = false;
    $args = array(
        'method'      => 'POST',
        'timeout'     => 999,
        'redirection' => 10,
        'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
        'blocking'    => true,
        'headers'     => $headers,
        'cookies'     => array(),
        'body'        => $post_args,
        'compress'    => false,
        'decompress'  => true,
        'sslverify'   => false,
        'stream'      => false,
        'filename'    => null
    );
    $ret_data            = wp_remote_post($url, $args);  
    $response_code       = wp_remote_retrieve_response_code( $ret_data );     
    if ( 200 != $response_code ) {
    } else {
        $content = wp_remote_retrieve_body( $ret_data );
    }
    if($content === false)
    {
        aiomatic_log_to_file('POST response code is: ' . $response_code . ' - ' . $url . ' - ' . print_r($post_args, true));
    }
    return $content;
}
function aiomatic_utf8_encode($str)
{
    $str .= $str;
    $len = \strlen($str);
    for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
        switch (true) {
            case $str[$i] < "\x80": $str[$j] = $str[$i]; break;
            case $str[$i] < "\xC0": $str[$j] = "\xC2"; $str[++$j] = $str[$i]; break;
            default: $str[$j] = "\xC3"; $str[++$j] = \chr(\ord($str[$i]) - 64); break;
        }
    }
    return substr($str, 0, $j);
}

function aiomatic_indexOf($arrax, $searchElement, $fromIndex)
{
    $index = 0;
    foreach($arrax as $index => $value)
    {
        if($index < $fromIndex)
        {
            $index++;
            continue;
        }
        if($value == $searchElement)
        {
            return $index;
        }
        $index++;
    }
    return -1;
}
function aiomatic_add_to_url($attr, $value, $origURL = '')
{
    if(empty($origURL))
    {
        $origURL = $_SERVER['REQUEST_URI'];
    }
    $url = parse_url($origURL);
    parse_str($url['query'], $q);
    $params = [$attr => $value];
    foreach ( $params as $k => $v ) $q[$k] = $v;
    $new_url = $url['path'] . '?' . http_build_query($q);
    return $new_url;
}
function aiomatic_compute_available_tokens($model, $max_tokens, &$prompt, &$query_token_count)
{
    if((aiomatic_is_chatgpt_turbo_model($model) || aiomatic_is_chatgpt_o_model($model) || aiomatic_is_o1_model($model)) && strstr($model, 'openai/') === false)
    {
        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
        $available_tokens = $max_tokens;
        if(isset($aiomatic_Main_Settings['gpt4_context_limit']) && $aiomatic_Main_Settings['gpt4_context_limit'] != '')
        {
            if($query_token_count > intval($aiomatic_Main_Settings['gpt4_context_limit']))
            {
                $prompt = aiomatic_strip_to_token_count($prompt, $aiomatic_Main_Settings['gpt4_context_limit'], true);
                $query_token_count = count(aiomatic_encode($prompt));
            }
        }
        elseif($query_token_count > aiomatic_get_max_input_tokens($model))
        {
            $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
            $query_token_count = count(aiomatic_encode($prompt));
        }
    }
    elseif(aiomatic_is_chatgpt35_16k_context_model($model))
    {
        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
        $available_tokens = $max_tokens;
        if(isset($aiomatic_Main_Settings['gpt35_context_limit']) && $aiomatic_Main_Settings['gpt35_context_limit'] != '')
        {
            if($query_token_count > intval($aiomatic_Main_Settings['gpt35_context_limit']))
            {
                $prompt = aiomatic_strip_to_token_count($prompt, $aiomatic_Main_Settings['gpt35_context_limit'], true);
                $query_token_count = count(aiomatic_encode($prompt));
            }
        }
        elseif($query_token_count > aiomatic_get_max_input_tokens($model))
        {
            $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
            $query_token_count = count(aiomatic_encode($prompt));
        }
    }
    elseif(aiomatic_is_claude_model($model))
    {
        $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
        $available_tokens = $max_tokens;
        if(aiomatic_is_claude_model_200k($model))
        {
            if(isset($aiomatic_Main_Settings['claude_context_limit_200k']) && $aiomatic_Main_Settings['claude_context_limit_200k'] != '')
            {
                if($query_token_count > intval($aiomatic_Main_Settings['claude_context_limit_200k']))
                {
                    $prompt = aiomatic_strip_to_token_count($prompt, $aiomatic_Main_Settings['claude_context_limit_200k'], true);
                    $query_token_count = count(aiomatic_encode($prompt));
                }
            }
            elseif($query_token_count > aiomatic_get_max_input_tokens($model))
            {
                $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
                $query_token_count = count(aiomatic_encode($prompt));
            }
        }
        else
        {
            if(isset($aiomatic_Main_Settings['claude_context_limit']) && $aiomatic_Main_Settings['claude_context_limit'] != '')
            {
                if($query_token_count > intval($aiomatic_Main_Settings['claude_context_limit']))
                {
                    $prompt = aiomatic_strip_to_token_count($prompt, $aiomatic_Main_Settings['claude_context_limit'], true);
                    $query_token_count = count(aiomatic_encode($prompt));
                }
            }
            elseif($query_token_count > aiomatic_get_max_input_tokens($model))
            {
                $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
                $query_token_count = count(aiomatic_encode($prompt));
            }
        }
    }
    elseif(aiomatic_is_perplexity_model($model))
    {
        $available_tokens = $max_tokens - $query_token_count;
        if($available_tokens < 10)
        {
            $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
            $query_token_count = count(aiomatic_encode($prompt));
            $available_tokens = $max_tokens - $query_token_count;
        }
    }
    elseif(aiomatic_is_groq_model($model))
    {
        $available_tokens = $max_tokens - $query_token_count;
        if($available_tokens < 10)
        {
            $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
            $query_token_count = count(aiomatic_encode($prompt));
            $available_tokens = $max_tokens - $query_token_count;
        }
    }
    elseif(aiomatic_is_nvidia_model($model))
    {
        $available_tokens = $max_tokens - $query_token_count;
        if($available_tokens < 10)
        {
            $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
            $query_token_count = count(aiomatic_encode($prompt));
            $available_tokens = $max_tokens - $query_token_count;
        }
    }
    elseif(aiomatic_is_xai_model($model))
    {
        $available_tokens = $max_tokens - $query_token_count;
        if($available_tokens < 10)
        {
            $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
            $query_token_count = count(aiomatic_encode($prompt));
            $available_tokens = $max_tokens - $query_token_count;
        }
    }
    elseif(aiomatic_is_openrouter_model($model))
    {
        $available_tokens = $max_tokens - $query_token_count;
        if($available_tokens < 10)
        {
            $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens_openrouter($model), true);
            $query_token_count = count(aiomatic_encode($prompt));
            $available_tokens = $max_tokens - $query_token_count;
        }
    }
    else
    {
        if($query_token_count > aiomatic_get_max_input_tokens($model))
        {
            $prompt = aiomatic_strip_to_token_count($prompt, aiomatic_get_max_input_tokens($model), true);
            $query_token_count = count(aiomatic_encode($prompt));
        }
        $available_tokens = $max_tokens;
    }
    return $available_tokens;
}
function aiomatic_assign_featured_image($attach_id, $post_id)
{
    if ($attach_id === 0 || !is_numeric($attach_id)) {
        return false;
    }
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    $res2 = set_post_thumbnail($post_id, $attach_id);
    if ($res2 === FALSE) {
        return false;
    }
    return get_the_post_thumbnail_url($attach_id);
}
function aiomatic_strip_to_token_count($prompt, $max_token_count, $keepend = true)
{
    $tokens = aiomatic_encode($prompt);
    if(count($tokens) > intval($max_token_count))
    {
        if($keepend == true)
        {
            $my_slice = array_slice($tokens, -$max_token_count, $max_token_count, true);
            return aiomatic_decode($my_slice);
        }
        else
        {
            $my_slice = array_slice($tokens, 0, $max_token_count, true);
            return aiomatic_decode($my_slice);
        }
    }
    return $prompt;
}
function aiomatic_is_vision_model($model, $assistant_id)
{
    if(!empty($assistant_id))
    {
        if(!is_numeric($assistant_id))
        {
            $local_assist = aiomatic_find_local_assistant_id($assistant_id);
        }
        else
        {
            $local_assist = $assistant_id;
        }
        if($local_assist !== false)
        {
            $assist_model = get_post_meta($local_assist, '_assistant_model', true);
            if(!empty($assist_model))
            {
                if(in_array($assist_model, aiomatic_get_all_vision_models()))
                {
                    return true;
                }
                return false;
            }
        }
    }
    $model_parts = explode(':', $model);
    $checkmodel = $model_parts[0];
    if(in_array($checkmodel, aiomatic_get_all_vision_models()))
    {
        return true;
    }
    return false;
}
function aiomatic_is_retrieval_model($model, $assistant_id)
{
    if(!empty($assistant_id))
    {
        if(!is_numeric($assistant_id))
        {
            $local_assist = aiomatic_find_local_assistant_id($assistant_id);
        }
        else
        {
            $local_assist = $assistant_id;
        }
        if($local_assist !== false)
        {
            $assist_model = get_post_meta($local_assist, '_assistant_model', true);
            if(!empty($assist_model))
            {
                if(in_array($assist_model, AIOMATIC_RETRIEVAL_MODELS))
                {
                    return true;
                }
                return false;
            }
        }
    }
    if(in_array($model, AIOMATIC_RETRIEVAL_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_claude_model($model)
{
    if(in_array($model, AIOMATIC_CLAUDE_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_groq_model($model)
{
    if(in_array($model, AIOMATIC_GROQ_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_nvidia_model($model)
{
    if(in_array($model, AIOMATIC_NVIDIA_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_xai_model($model)
{
    if(in_array($model, AIOMATIC_XAI_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_vision_groq_model($model)
{
    if(in_array($model, AIOMATIC_VISION_GROQ_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_vision_xai_model($model)
{
    if(in_array($model, AIOMATIC_VISION_XAI_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_vision_claude_model($model)
{
    if(in_array($model, AIOMATIC_VISION_CLAUDE_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_vision_google_model($model)
{
    if(in_array($model, AIOMATIC_GOOGLE_VISION_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_get_model_provider($model)
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(aiomatic_is_google_model($model))
    {
        return ' (Google)';
    }
    elseif(aiomatic_is_claude_model($model))
    {
        return ' (Anthropic)';
    }
    elseif(aiomatic_is_openrouter_model($model))
    {
        return ' (OpenRouter)';
    }
    elseif(aiomatic_is_huggingface_model($model))
    {
        return ' (HuggingFace)';
    }
    elseif(aiomatic_is_ollama_model($model))
    {
        return ' (Ollama)';
    }
    elseif(aiomatic_is_perplexity_model($model))
    {
        return ' (PerplexityAI)';
    }
    elseif(aiomatic_is_groq_model($model))
    {
        return ' (Groq)';
    }
    elseif(aiomatic_is_nvidia_model($model))
    {
        return ' (Nvidia)';
    }
    elseif(aiomatic_is_xai_model($model))
    {
        return ' (xAI)';
    }
    elseif(aiomatic_is_custom_model($model, $aiomatic_Main_Settings))
    {
        return ' (OpenAI Compatible)';
    }
    return ' (OpenAI)';
}
function aiomatic_is_openrouter_model($model)
{
    $openrouter = false;
    static $router_models = array();
    if(empty($router_models))
    {
        try
        {
            $openrouter = aiomatic_get_openrouter_models();
            if($openrouter !== false)
            {
                foreach($openrouter['source_list'] as $smodel)
                {
                    $router_models[] = $smodel['model'];
                }
            }
        }
        catch(Exception $e)
        {
            aiomatic_log_to_file('Failed to list OpenRouter models: ' . $e->getMessage());
        }
    }
    if(in_array($model, $router_models))
    {
        return true;
    }
    return false;
}
function aiomatic_is_custom_model($model, $aiomatic_Main_Settings)
{
    if (isset($aiomatic_Main_Settings['custom_models']) && trim($aiomatic_Main_Settings['custom_models']) != '') 
    {
        $custom_models = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['custom_models']));
        $custom_models = array_filter($custom_models);
        if(in_array($model, $custom_models))
        {
            return true;
        }
    }
    return false;
}
function aiomatic_is_perplexity_model($model)
{
    $modellist = AIOMATIC_PERPLEXITY_MODELS;
    if(in_array($model, $modellist))
    {
        return true;
    }
    return false;
}
function aiomatic_is_google_chat_model($model)
{
    $modellist = AIOMATIC_GOOGLE_CHAT_MODELS;
    if(in_array($model, $modellist))
    {
        return true;
    }
    return false;
}
function aiomatic_get_ollama_embedding_models($no_cache = false)
{
    if($no_cache !== true)
    {
        $my_options = get_option('aiomatic_ollama_embedding_models', array());
        if(!empty($my_options))
        {
            return $my_options;
        }
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['ollama_url']) || $aiomatic_Main_Settings['ollama_url'] == '') 
    {
        aiomatic_log_to_file('Ollama server URL not set in plugin settings.');
        return false;
    }
    $ollama_url = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['ollama_url']));
    $ollama_url = array_filter($ollama_url);
    $ollama_url = $ollama_url[array_rand($ollama_url)];
    $ollama_url = rtrim(trim($ollama_url), '/');
    $cmdResult = aiomatic_get_web_page($ollama_url . '/api/tags');
    if($cmdResult == false)
    {
        aiomatic_log_to_file('Failed to access Ollama server URL: ' . $ollama_url);
        return false;
    }
    $rules = array();
    if(stristr($cmdResult, '"models"') !== false)
    {
        $rls = json_decode($cmdResult, true);
        if($rls == null || !isset($rls['models']))
        {
            aiomatic_log_to_file('Failed to decode server response: ' . $cmdResult);
            return false;
        }
        foreach($rls['models'] as $mymod)
        {
            $my_mod_part = explode(':', $mymod['model']);
            $check_mod = $my_mod_part[0];
            if(stristr($mymod['model'], 'embed') !== false || in_array($check_mod, AIOMATIC_EMBEDDING_OLLAMA_MODELS))
            {
                $rules[$mymod['model']] = $mymod['model'];
            }
        }
        aiomatic_update_option('aiomatic_ollama_embedding_models', $rules);
    }
    else
    {
        aiomatic_log_to_file('Failed to decode Ollama server response: ' . $cmdResult);
        return false;
    }
    if(count($rules) > 0)
    {
        return $rules;
    }
    return array();
}
function aiomatic_get_ollama_models($no_cache = false)
{
    if($no_cache !== true)
    {
        $my_options = get_option('aiomatic_ollama_models', array());
        if(!empty($my_options))
        {
            return $my_options;
        }
    }
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if (!isset($aiomatic_Main_Settings['ollama_url']) || $aiomatic_Main_Settings['ollama_url'] == '') 
    {
        aiomatic_log_to_file('Ollama server URL not set in plugin settings.');
        return false;
    }
    $ollama_url = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['ollama_url']));
    $ollama_url = array_filter($ollama_url);
    $ollama_url = $ollama_url[array_rand($ollama_url)];
    $ollama_url = rtrim(trim($ollama_url), '/');
    $cmdResult = aiomatic_get_web_page($ollama_url . '/api/tags');
    if($cmdResult == false)
    {
        aiomatic_log_to_file('Failed to access Ollama server URL: ' . $ollama_url);
        return false;
    }
    $rules = array();
    if(stristr($cmdResult, '"models"') !== false)
    {
        $rls = json_decode($cmdResult, true);
        if($rls == null || !isset($rls['models']))
        {
            aiomatic_log_to_file('Failed to decode server response: ' . $cmdResult);
            return false;
        }
        foreach($rls['models'] as $mymod)
        {
            $my_mod_part = explode(':', $mymod['model']);
            $check_mod = $my_mod_part[0];
            if(stristr($mymod['model'], 'embed') === false && !in_array($check_mod, AIOMATIC_EMBEDDING_OLLAMA_MODELS))
            {
                $rules[$mymod['model']] = $mymod['model'];
            }
        }
        aiomatic_update_option('aiomatic_ollama_models', $rules);
    }
    else
    {
        aiomatic_log_to_file('Failed to decode Ollama server response: ' . $cmdResult);
        return false;
    }
    if(count($rules) > 0)
    {
        return $rules;
    }
    return array();
}
function aiomatic_is_ollama_model($model)
{
    $modellist = get_option('aiomatic_ollama_models', array());
    if(in_array($model, $modellist))
    {
        return true;
    }
    return false;
}
function aiomatic_google_extension_is_google_embeddings_model($model)
{
    if(in_array($model, AIOMATIC_GOOGLE_EMBEDDINGS_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_ollama_embeddings_model($model)
{
    $my_options = get_option('aiomatic_ollama_embedding_models', array());
    if(in_array($model, $my_options))
    {
        return true;
    }
    return false;
}

function aiomatic_claude_local_extension_is_claude_model($model)
{
    if(in_array($model, AIOMATIC_CLAUDE_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_huggingface_model($model)
{
    $modellist = get_option('aiomatic_huggingface_models', array());
    if(array_key_exists($model, $modellist))
    {
        return true;
    }
    return false;
}
function aiomatic_is_google_model($model)
{
    if(in_array($model, AIOMATIC_GOOGLE_MODELS))
    {
        return true;
    }
    return false;
}
function aiomatic_is_claude_model_200k($model)
{
    if(in_array($model, AIOMATIC_CLAUDE_MODELS_200K))
    {
        return true;
    }
    return false;
}
function aiomatic_is_claude_3_model($model)
{
    if(in_array($model, AIOMATIC_CLAUDE_CHAT))
    {
        return true;
    }
    return false;
}
function aiomatic_is_new_token_window_model($model)
{
    if(aiomatic_is_chatgpt_turbo_model($model) || aiomatic_is_chatgpt35_16k_context_model($model) || aiomatic_is_chatgpt_o_model($model) || aiomatic_is_o1_model($model))
    {
        return true;
    }
    return false;
}
function aiomatic_is_chatgpt_o_model($model)
{
    if(stristr($model, 'gpt-4o') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_o1_mini_model($model)
{
    if(aiomatic_starts_with($model, 'o1-mini') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_o3_mini_model($model)
{
    if(aiomatic_starts_with($model, 'o3-mini') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_o4_mini_model($model)
{
    if(aiomatic_starts_with($model, 'o4-mini') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_o3_model($model)
{
    if(aiomatic_starts_with($model, 'o3') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_o1_model($model)
{
    if(aiomatic_is_o1_mini_model($model) || aiomatic_is_o3_mini_model($model) || aiomatic_is_o4_mini_model($model) || aiomatic_is_o3_model($model))
    {
        return true;
    }
    if(aiomatic_starts_with($model, 'o1') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_gpt5_model($model)
{
    if(aiomatic_starts_with($model, 'gpt-5') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_chatgpt_o_mini_model($model)
{
    if(stristr($model, 'gpt-4o-mini') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_chatgpt_turbo_model($model)
{
    if($model == 'gpt-4-1106-preview')
    {
        return true;
    }
    elseif($model == 'gpt-4-vision-preview')
    {
        return true;
    }
    elseif($model == 'gpt-4-0125-preview')
    {
        return true;
    }
    elseif(stristr($model, 'gpt-4-turbo') !== false)
    {
        return true;
    }
    return false;
}
function aiomatic_is_chatgpt_45_model($model)
{
    if($model == 'gpt-4.5-preview')
    {
        return true;
    }
    elseif($model == 'gpt-4.5-preview-2025-02-27')
    {
        return true;
    }
    return false;
}
function aiomatic_is_chatgpt_41_model($model)
{
    if($model == 'gpt-4.1-2025-04-14')
    {
        return true;
    }
    elseif($model == 'gpt-4.1-2025-04-14')
    {
        return true;
    }
    elseif($model == 'gpt-4.1-mini')
    {
        return true;
    }
    elseif($model == 'gpt-4.1-mini-2025-04-14')
    {
        return true;
    }
    elseif($model == 'gpt-4.1-nano')
    {
        return true;
    }
    elseif($model == 'gpt-4.1-nano-2025-04-14')
    {
        return true;
    }
    return false;
}
function aiomatic_is_chatgpt_5_model($model)
{
    if($model == 'gpt-5-nano-2025-08-07')
    {
        return true;
    }
    elseif($model == 'gpt-5-nano')
    {
        return true;
    }
    elseif($model == 'gpt-5-mini-2025-08-07')
    {
        return true;
    }
    elseif($model == 'gpt-5-mini')
    {
        return true;
    }
    elseif($model == 'gpt-5-2025-08-07')
    {
        return true;
    }
    elseif($model == 'gpt-5')
    {
        return true;
    }
    return false;
}
function aiomatic_is_chatgpt35_16k_context_model($model)
{
    if($model == 'gpt-3.5-turbo-1106')
    {
        return true;
    }
    elseif($model == 'gpt-3.5-turbo-0125')
    {
        return true;
    }
    return false;
}
function aiomatic_is_chatgpt_model($model)
{
    if(stristr($model, 'gpt-3.5-turbo-instruct') !== false)
    {
        return false;
    }
    elseif(stristr($model, 'gpt-4-1106-preview') !== false)
    {
        return false;
    }
    elseif(stristr($model, 'gpt-4-0125-preview') !== false)
    {
        return false;
    }
    elseif(stristr($model, 'gpt-4-turbo') !== false)
    {
        return false;
    }
    elseif(stristr($model, 'gpt-4o') !== false)
    {
        return false;
    }
    elseif(stristr($model, 'gpt-4-vision-preview') !== false)
    {
        return false;
    }
    elseif ( preg_match('/^((?:gpt)-(?:[\d.]+)(?:-[a-zA-Z0-9]+)?(?:-[\d]+)?)/', $model, $matches ) || preg_match('/^ft:((?:gpt)-(?:[\d.]+)(?:-[a-zA-Z0-9]+)?(?:-[\d]+)?):.*/', $model, $matches ) )
    {
        return true;
    }
    return false;
}
function aiomatic_get_all_models_claude($reverse = false)
{
    $all_models = AIOMATIC_CLAUDE_MODELS;
    if($reverse)
    {
        $all_models = array_reverse($all_models);
    }
    return $all_models;
}
function aiomatic_get_all_models($reverse = false)
{
    $all_models = array();
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(!function_exists('is_plugin_active'))
    {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    if(isset($aiomatic_Main_Settings['ollama_url']) && !empty(trim($aiomatic_Main_Settings['ollama_url'])))
    {
        $llama_models = aiomatic_get_ollama_models(false);
        if($llama_models !== false)
        {
            $all_models = array_merge($llama_models, $all_models);
        }
    }
    if(isset($aiomatic_Main_Settings['app_id_openrouter']) && !empty(trim($aiomatic_Main_Settings['app_id_openrouter'])))
    {
        try
        {
            $openrouter = aiomatic_get_openrouter_models();
            if($openrouter !== false)
            {
                sort($openrouter['source_list']);
                foreach($openrouter['source_list'] as $smodel)
                {
                    $all_models[] = $smodel['model'];
                }
            }
        }
        catch(Exception $e)
        {
            aiomatic_log_to_file('Failed to list OpenRouter models: ' . $e->getMessage());
        }
    }
    if(isset($aiomatic_Main_Settings['app_id_huggingface']) && !empty(trim($aiomatic_Main_Settings['app_id_huggingface'])))
    {
        $huggingmodels = get_option('aiomatic_huggingface_models', array());
        foreach($huggingmodels as $hmodel => $details)
        {
            $all_models[] = $hmodel;
        }
    }
    if(isset($aiomatic_Main_Settings['app_id_perplexity']) && !empty(trim($aiomatic_Main_Settings['app_id_perplexity'])))
    {
        $all_models = array_merge(AIOMATIC_PERPLEXITY_MODELS, $all_models);
    }
    if(isset($aiomatic_Main_Settings['app_id_claude']) && !empty(trim($aiomatic_Main_Settings['app_id_claude'])))
    {
        $all_models = array_merge(AIOMATIC_CLAUDE_MODELS, $all_models);
    }
    if(isset($aiomatic_Main_Settings['app_id_groq']) && !empty(trim($aiomatic_Main_Settings['app_id_groq'])))
    {
        $all_models = array_merge(AIOMATIC_GROQ_MODELS, $all_models);
    }
    if(isset($aiomatic_Main_Settings['app_id_nvidia']) && !empty(trim($aiomatic_Main_Settings['app_id_nvidia'])))
    {
        $all_models = array_merge(AIOMATIC_NVIDIA_MODELS, $all_models);
    }
    if(isset($aiomatic_Main_Settings['app_id_xai']) && !empty(trim($aiomatic_Main_Settings['app_id_xai'])))
    {
        $all_models = array_merge(AIOMATIC_XAI_MODELS, $all_models);
    }
    if(isset($aiomatic_Main_Settings['app_id_google']) && !empty(trim($aiomatic_Main_Settings['app_id_google'])))
    {
        $all_models = array_merge(AIOMATIC_GOOGLE_MODELS, $all_models);
    }
    if(isset($aiomatic_Main_Settings['custom_models']) && !empty(trim($aiomatic_Main_Settings['custom_models'])) && isset($aiomatic_Main_Settings['api_selector']) && $aiomatic_Main_Settings['api_selector'] == 'custom')
    {
        $custom_models = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['custom_models']));
        $custom_models = array_filter($custom_models);
        $all_models = array_merge($custom_models, $all_models);
    }
    $all_custom_models = get_option('aiomatic_custom_models', array());
    if($reverse == true)
    {
        $all_models = array_merge($all_custom_models, $all_models);
        $all_models = array_merge(AIOMATIC_MODELS, $all_models);
        $all_models = array_merge(AIOMATIC_MODELS_CHAT, $all_models);
    }
    else
    {
        $all_models = array_merge(AIOMATIC_MODELS_CHAT, $all_models);
        $all_models = array_merge(AIOMATIC_MODELS, $all_models);
        $all_models = array_merge($all_custom_models, $all_models);
    }
    return $all_models;
}

function aiomatic_get_groq_models()
{
    return AIOMATIC_GROQ_MODELS;
}
function aiomatic_get_vision_groq_models()
{
    return AIOMATIC_VISION_GROQ_MODELS;
}
function aiomatic_get_vision_xai_models()
{
    return AIOMATIC_VISION_XAI_MODELS;
}

function aiomatic_array_to_object($array) 
{
    $obj = new stdClass();
    foreach ($array as $k => $v) 
    {
       if (strlen($k)) 
       {
          if (is_array($v)) 
          {
             $obj->{$k} = aiomatic_array_to_object($v);
          } 
          else 
          {
             $obj->{$k} = $v;
          }
       }
    }
    return $obj;
}
function aiomatic_list_models_google(&$error)
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    $appids_google = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id_google']));
    $appids_google = array_filter($appids_google);
    $token = $appids_google[array_rand($appids_google)];
    if(empty($token))
    {
        $error = 'A Google API key is needed for this to work.';
        return false;
    }
    $ch = curl_init();
    if($ch === false)
    {
        $error = 'Error: failed to init curl in Google AI API';
        return false;
    }
    $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $token;      
    if (isset($aiomatic_Main_Settings['proxy_url']) && $aiomatic_Main_Settings['proxy_url'] != '' && $aiomatic_Main_Settings['proxy_url'] != 'disable' && $aiomatic_Main_Settings['proxy_url'] != 'disabled') 
    {
        $prx = explode(',', $aiomatic_Main_Settings['proxy_url']);
        $randomness = array_rand($prx);
        curl_setopt( $ch, CURLOPT_PROXY, trim($prx[$randomness]));
        if (isset($aiomatic_Main_Settings['proxy_auth']) && $aiomatic_Main_Settings['proxy_auth'] != '') 
        {
            $prx_auth = explode(',', $aiomatic_Main_Settings['proxy_auth']);
            if(isset($prx_auth[$randomness]) && trim($prx_auth[$randomness]) != '')
            {
                curl_setopt( $ch, CURLOPT_PROXYUSERPWD, trim($prx_auth[$randomness]));
            }
        }
    }
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (isset($aiomatic_Main_Settings['max_timeout']) && $aiomatic_Main_Settings['max_timeout'] != '')
    {
        $ztime = intval($aiomatic_Main_Settings['max_timeout']);
    }
    else
    {
        $ztime = 300;
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, $ztime);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) 
    {
        $error = 'Curl error: ' . curl_error($ch);
        curl_close($ch);
        return false;
    }
    if($response === false)
    {
        $error = 'Failed to get Google API response';
        curl_close($ch);
        return false;
    }
    curl_close($ch);
    $decodedResponse = json_decode($response, true);
    if(isset($decodedResponse[0]))
    {
        $decodedResponse = $decodedResponse[0];
    }
    if (isset($decodedResponse['error'])) 
    {
        $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse['error']['message'] : 'Unknown error from Google AI Studio API';
        $error = 'Error: ' . $errorMsg;
        return false;
    } 
    elseif (isset($decodedResponse[0]['error'])) 
    {
        $errorMsg = isset($decodedResponse['error']['message']) ? $decodedResponse[0]['error']['message'] : 'Unknown error from Google AI Studio API';
        $error = 'Error: ' . $errorMsg;
        return false;
    } 
    elseif (empty($decodedResponse)) 
    {
        $error = 'No data found in the response ' . print_r($response, true);
        return false;
    }
    return $decodedResponse;
}
function aiomatic_get_all_vision_models()
{
    $all_models = AIOMATIC_MODELS_VISION;
    $all_models = array_merge($all_models, AIOMATIC_MODELS_OLLAMA_VISION);
    $openrouter = aiomatic_get_openrouter_models();
    if($openrouter !== false)
    {
        sort($openrouter['source_list']);
        foreach($openrouter['source_list'] as $smodel)
        {
            $all_models[] = $smodel['model'];
        }
    }
    $all_models = array_merge($all_models, aiomatic_get_vision_groq_models());
    $all_models = array_merge($all_models, aiomatic_get_vision_xai_models());
    $all_models = array_merge($all_models, AIOMATIC_VISION_CLAUDE_MODELS);
    $all_models = array_merge($all_models, AIOMATIC_GOOGLE_VISION_MODELS);
    return $all_models;
}
function aiomatic_get_all_models_function()
{
    $all_models = AIOMATIC_FUNCTION_CALLING_MODELS;
    $all_models = array_merge($all_models, AIOMATIC_OLLAMA_FUNCTION_CALLING_MODELS);
    $all_models = array_merge($all_models, AIOMATIC_GROQ_FUNCTION_CALLING_MODELS);
    $all_models = array_merge($all_models, AIOMATIC_XAI_FUNCTION_CALLING_MODELS);
    $all_models = array_merge($all_models, AIOMATIC_GOOGLE_FUNCTION_CALLING_MODELS);
    return $all_models;
}
function aiomatic_getPlaylistIdFromUrl($url) 
{
    $pattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:.*?[&?]list=|playlist\?list=)([a-zA-Z0-9_-]+)/i';
    if (preg_match($pattern, $url, $matches)) 
    {
        return $matches[1]; 
    }
    return false; 
}
function aiomatic_fetch_azure_content($url, $headers)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($http_code == 200) {
        return $response;
    } else {
        $error_message = json_decode($response, true)['error']['message'] ?? 'Unknown error';
        return new WP_Error('azure_error', "HTTP request failed with code {$http_code}: {$error_message}");
    }
}
function aiomatic_get_all_assistants($reverse = false)
{
    $aiomatic_Main_Settings = get_option('aiomatic_Main_Settings', false);
    if(!isset($aiomatic_Main_Settings['app_id']) || empty(trim($aiomatic_Main_Settings['app_id'])))
    {
        return array();
    }
    $appids = preg_split('/\r\n|\r|\n/', trim($aiomatic_Main_Settings['app_id']));
    $appids = array_filter($appids);
    $token = $appids[array_rand($appids)];
    if(aiomatic_is_aiomaticapi_key($token))
    {
        return false;
    }
    else
    {
        if (!aiomatic_check_if_azure($aiomatic_Main_Settings) && aiomatic_check_if_azure_or_others($aiomatic_Main_Settings))
        {
            return false;
        }
    }
    $assistant_list = array();
    $postsPerPage = 50000;
    $paged = 0;
    do
    {
        $postOffset = $paged * $postsPerPage;
        $query = array(
            'post_status' => array(
                'publish'
            ),
            'post_type' => array(
                'aiomatic_assistants'
            ),
            'numberposts' => $postsPerPage,
            'offset'  => $postOffset
        );
        $got_me = get_posts($query);
        $assistant_list = array_merge($assistant_list, $got_me);
        $paged++;
    }while(!empty($got_me));
    if($reverse)
    {
        $assistant_list = array_reverse($assistant_list);
    }
    return $assistant_list;
}
function aiomatic_starts_with($newx_url, $query)
{
    if(substr( $newx_url, 0, strlen($query) ) === $query)
    {
        return true;
    }
    return false;
}
function aiomatic_ends_with( $haystack, $needle ) 
{
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}
function aiomatic_get_word($key, $r_vars)
{
    if (isset($r_vars[$key])) {
        
        $words  = $r_vars[$key];
        $w_max  = count($words) - 1;
        $w_rand = rand(0, $w_max);
        return aiomatic_replace_words(trim($words[$w_rand]), $r_vars);
    } else {
        return "";
    }
    
}

function aiomatic_replace_words($sentence, $r_vars)
{
    
    if (str_replace('%', '', $sentence) == $sentence)
        return $sentence;
    
    $words = explode(" ", $sentence);
    
    $new_sentence = array();
    for ($w = 0; $w < count($words); $w++) {
        
        $word = trim($words[$w]);
        
        if ($word != '') {
            if (preg_match('/^%([^%\n]*)$/', $word, $m)) {
                $varkey         = trim($m[1]);
                $new_sentence[] = aiomatic_get_word($varkey, $r_vars);
            } else {
                $new_sentence[] = $word;
            }
        }
    }
    return implode(" ", $new_sentence);
}
function aiomatic_get_plugin_url()
{
    return plugins_url('', __FILE__);
}

function aiomatic_get_file_url($url)
{
    return esc_url_raw(aiomatic_get_plugin_url() . '/' . $url);
}
function aiomatic_redirect($url, $statusCode = 301)
{
  if(!function_exists('wp_redirect'))
  {
     include_once( ABSPATH . 'wp-includes/pluggable.php' );
  }
  wp_redirect($url, $statusCode);
  die();
}
function aiomatic_sanitize_date_time( $date_time, $type = 'date', $accepts_string = false ) {
	if ( empty( $date_time ) || ! in_array( $type, array( 'date', 'time' ) ) ) {
		return array();
	}
	$segments = array();
	if (
		true === $accepts_string
		&& ( false !== strpos( $date_time, ' ' ) || false === strpos( $date_time, '-' ) )
	) {
		if ( false !== $timestamp = strtotime( $date_time ) ) {
			return $date_time;
		}
	}
	$parts = array_map( 'absint', explode( 'date' == $type ? '-' : ':', $date_time ) );
	if ( 'date' == $type ) {
		$year = $month = $day = 1;
		if ( count( $parts ) >= 3 ) {
			list( $year, $month, $day ) = $parts;
			$year  = ( $year  >= 1 && $year  <= 9999 ) ? $year  : 1;
			$month = ( $month >= 1 && $month <= 12   ) ? $month : 1;
			$day   = ( $day   >= 1 && $day   <= 31   ) ? $day   : 1;
		}
		$segments = array(
			'year'  => $year,
			'month' => $month,
			'day'   => $day
		);
	} elseif ( 'time' == $type ) {
		$hour = $minute = $second = 0;
		switch( count( $parts ) ) {
			case 3 :
				list( $hour, $minute, $second ) = $parts;
				$hour   = ( $hour   >= 0 && $hour   <= 23 ) ? $hour   : 0;
				$minute = ( $minute >= 0 && $minute <= 60 ) ? $minute : 0;
				$second = ( $second >= 0 && $second <= 60 ) ? $second : 0;
				break;
			case 2 :
				list( $hour, $minute ) = $parts;
				$hour   = ( $hour   >= 0 && $hour   <= 23 ) ? $hour   : 0;
				$minute = ( $minute >= 0 && $minute <= 60 ) ? $minute : 0;
				break;
			default : break;
		}
		$segments = array(
			'hour'   => $hour,
			'minute' => $minute,
			'second' => $second
		);
	}

	return apply_filters( 'display_posts_shortcode_sanitized_segments', $segments, $date_time, $type );
}
?>