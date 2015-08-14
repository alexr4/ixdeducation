<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Easy_Social_Share_Buttons {

	/**
	 * The single instance of Easy_Social_Share_Buttons.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'easy_social_share_buttons';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Easy_Social_Share_Buttons_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		//Add post content filters
		add_filter('the_content', array( $this, 'add_share_buttons_to_post'));
		add_filter('the_content', array( $this, 'add_share_buttons_to_media'));

		//Create shortcode
		add_shortcode( 'ess_post', array($this, 'share_post_shortcode') );

	} // End __construct ()

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		if (get_option('ess_load_css')) {
			wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-frontend' );
		}
	} // End enqueue_styles ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'easy-social-share-buttons', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'easy-social-share-buttons';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Easy_Social_Share_Buttons Instance
	 *
	 * Ensures only one instance of Easy_Social_Share_Buttons is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Easy_Social_Share_Buttons()
	 * @return Main Easy_Social_Share_Buttons instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	 * Return the url of the current page.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_current_url() {
		global $wp;
		return home_url(add_query_arg(array(),$wp->request)) . '/';
	} // End get_current_url()

	/**
	 * Return the number of shares on Facebook.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_facebook_count($url) {
		//https://api.facebook.com/method/links.getStats?urls=%%URL%%&format=json

		$endpoint = 'https://api.facebook.com/method/links.getStats?urls=' . $url . '&format=json';

		// setup curl to make a call to the endpoint
		$session = curl_init($endpoint);

		// indicates that we want the response back rather than just returning a "TRUE" string
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

		// execute GET and get the session back
		$result = json_decode(curl_exec($session));

		// close connection
		curl_close($session);

		return $result[0]->total_count;
	} // End get_facebook_count()

	/**
	 * Return the number of shares on Twitter.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_twitter_count($url) {
		//http://urls.api.twitter.com/1/urls/count.json?url=%%URL%%&callback=twttr.receiveCount  

		$endpoint = 'http://urls.api.twitter.com/1/urls/count.json?url=' . $url;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

		# Get the response
		$response = curl_exec($curl);

		# Close connection
		curl_close($curl);

		# Return JSON
		$result = json_decode($response, true);

		return $result["count"];
	} // End get_twitter_count()

	/**
	 * Return the number of shares on Pinterest.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_pinterest_count($url) {
		//https://api.pinterest.com/v1/urls/count.json?callback=jsonp&url=%%URL%%

		$endpoint = 'https://api.pinterest.com/v1/urls/count.json?callback=jsonp&url=' . $url;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

		# Get the response
		$response = curl_exec($curl);

		# Close connection
		curl_close($curl);

		# Return JSON
		$response = str_replace(array('jsonp(', ')'), '', $response);
		$result = json_decode($response, true);

		return $result['count'];
	} // End get_pinterest_count()

	/**
	 * Return the number of shares on Google Plus.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_google_count($url) {

		$html =  file_get_contents( "https://plusone.google.com/_/+1/fastbutton?url=" . $url );
		$doc = new DOMDocument();   
		libxml_use_internal_errors(true);
		$doc->loadHTML($html);
		$counter=$doc->getElementById('aggregateCount');
		libxml_clear_errors();
		return $counter->nodeValue;
	} // End get_google_count()

	/**
	 * Create and return a post excerpt from a post ID outside of the loop.
	 * A similar function in Wordpress was deprecated.
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_excerpt_by_id($post_id){
	    $the_post = get_post($post_id); //Gets post ID
	    $the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
	    $excerpt_length = 35; //Sets excerpt length by word count
	    $the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
	    $words = explode(' ', $the_excerpt, $excerpt_length + 1);

	    if(count($words) > $excerpt_length) :
	        array_pop($words);
	        array_push($words, '…');
	        $the_excerpt = implode(' ', $words);
	    endif;

	    return $the_excerpt;
	} //End get_excerpt_by_id()

	/**
	 * Encode the url query parameters in hex to preserve special characters
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function mb_rawurlencode($url){
		$encoded='';
		$length=mb_strlen($url);

		for ($i=0;$i<$length;$i++) {
			$encoded.='%'.wordwrap(bin2hex(mb_substr($url,$i,1)),2,'%',true);
		}

		return $encoded;
	} // End mb_rawurlencode()

	/**
	 * Generate buttons
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_button_html($social_site, $post_id, $show_count, $image_url = null, $image_id = null) {

		$title = $this->mb_rawurlencode( get_the_title( $post_id ) );
		$description = $this->mb_rawurlencode( $this->get_excerpt_by_id( $post_id ) );
		$link = rawurlencode( get_permalink( $post_id ) );

		if ( get_option('permalink_structure') ) {
			$slash = '';	
		} else {
			// if pretty permalinks are not enabled add slash
			$slash = '/';
		}

		$direct_link = get_permalink( $post_id ) . ($image_id ? $slash . '#' . $image_id : '');
		$current_url = $this->get_current_url();
		$button = '';

		switch ($social_site) {
			case 'facebook':

			    $facebook_share_url = 'https://www.facebook.com/dialog/feed?';
			    $facebook_share_url .= 'app_id=' . esc_html( get_option('ess_facebook_app_id') );
			    $facebook_share_url .= '&amp;display=popup';
			    $facebook_share_url .= '&amp;caption=' . $title;
			    $facebook_share_url .= '&amp;link=' . $link;
			    $facebook_share_url .= '&amp;description=' . $description;
			    $facebook_share_url .= '&amp;redirect_uri=' . $link;

			    if (!empty($image_url)) {
			    	$facebook_share_url .= '&amp;picture=' . rawurlencode( $image_url );
			    }

				$button = '<a class="ess-button ess-button--facebook" href="' . $facebook_share_url . '" onclick="window.open(this.href, \'facebookwindow\',\'left=20,top=20,width=600,height=700,toolbar=0,resizable=1\'); return false;" title="Share on Facebook" target="_blank">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="24px" height="20px" viewBox="0 0 24 20" enable-background="new 0 0 24 20" xml:space="preserve">
<path fill="#333333" d="M9.54,11.276l0.446-3.46H6.56V5.608c0-1.002,0.279-1.685,1.716-1.685l1.83-0.001V0.828
	C9.79,0.786,8.703,0.691,7.437,0.691c-2.642,0-4.448,1.613-4.448,4.574v2.551H0v3.46h2.988v8.878H6.56v-8.878H9.54z"/>
</svg>' . "\n";
				$button .= '<span class="ess-share-text">Facebook</span>' . "\n";
				$button .= '</div>' . "\n";
				
				if ($show_count) {
					$button .= '<span class="ess-social-count">' . $this->get_facebook_count( $current_url ) . '</span>';
				}

				$button .= '</a>' . "\n";
			break;

			case 'twitter':
				$twitter_url = 'http://twitter.com/intent/tweet?';
				$twitter_url .= 'text=' . $title . '%20' . $link;

				$button = '<a class="ess-button ess-button--twitter" href="' . $twitter_url .'" onclick="window.open(this.href, \'twitterwindow\',\'left=20,top=20,width=600,height=300,toolbar=0,resizable=1\'); return false;" title="Tweet" target="_blank">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="24px" height="20px" viewBox="0 0 24 20" enable-background="new 0 0 24 20" xml:space="preserve">
<path fill="#333333" d="M23.72,3.207c-0.862,0.382-1.79,0.641-2.762,0.757c0.992-0.595,1.756-1.537,2.114-2.66
	c-0.928,0.551-1.957,0.952-3.053,1.167c-0.878-0.935-2.128-1.519-3.511-1.519c-2.656,0-4.81,2.153-4.81,4.809
	c0,0.377,0.043,0.744,0.124,1.095c-3.996-0.2-7.539-2.114-9.911-5.024c-0.413,0.711-0.65,1.537-0.65,2.418
	c0,1.668,0.849,3.141,2.138,4.002C2.612,8.229,1.87,8.011,1.222,7.65v0.061c0,2.33,1.658,4.274,3.857,4.715
	c-0.403,0.109-0.829,0.17-1.267,0.17c-0.31,0-0.612-0.031-0.905-0.088c0.611,1.911,2.388,3.301,4.491,3.341
	c-1.645,1.289-3.718,2.059-5.972,2.059c-0.388,0-0.771-0.023-1.146-0.067C2.408,19.203,4.936,20,7.653,20
	c8.845,0,13.681-7.326,13.681-13.682c0-0.208-0.005-0.415-0.013-0.623C22.259,5.019,23.073,4.172,23.72,3.207z"/>
</svg>' . "\n";
				$button .= '<span class="ess-share-text">Tweet</span>' . "\n";
				$button .= '</div>' . "\n";
				
				if ($show_count) {
					$button .= '<span class="ess-social-count">' . $this->get_twitter_count( $current_url ) . '</span>';
				}

				$button .= '</a>' . "\n";
			break;
			
			case 'gplus':
				$google_url = 'https://plus.google.com/share?';
				$google_url .= 'url=' . $link;

				$button = '<a class="ess-button ess-button--gplus" href="' . $google_url .'" onclick="javascript:window.open(this.href,\'googlepluswindow\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;" title="Share on Google" target="_blank">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="24px" height="20px" viewBox="0 0 24 20" enable-background="new 0 0 24 20" xml:space="preserve">
<g>
	<path fill="#333333" d="M8.837,1.879C8.99,1.975,9.162,2.113,9.354,2.296c0.184,0.19,0.363,0.425,0.539,0.702
		c0.169,0.262,0.318,0.57,0.449,0.928c0.106,0.356,0.161,0.773,0.161,1.249c-0.015,0.872-0.207,1.57-0.579,2.093
		C9.743,7.522,9.551,7.756,9.347,7.97C9.122,8.184,8.882,8.402,8.628,8.625c-0.146,0.15-0.279,0.321-0.402,0.511
		C8.08,9.334,8.007,9.563,8.007,9.825c0,0.255,0.075,0.464,0.224,0.631c0.127,0.159,0.25,0.298,0.369,0.417l0.827,0.677
		c0.515,0.421,0.966,0.883,1.353,1.392c0.365,0.516,0.555,1.189,0.571,2.021c0,1.182-0.522,2.229-1.565,3.141
		c-1.081,0.945-2.644,1.432-4.685,1.464c-1.708-0.016-2.982-0.38-3.825-1.092C0.426,17.812,0,17.016,0,16.089
		c0-0.45,0.138-0.953,0.416-1.508c0.268-0.553,0.753-1.04,1.457-1.459c0.788-0.451,1.619-0.753,2.488-0.903
		c0.861-0.127,1.575-0.197,2.145-0.214c-0.175-0.23-0.332-0.479-0.47-0.741c-0.16-0.254-0.24-0.563-0.24-0.919
		c0-0.216,0.029-0.396,0.091-0.539C5.94,9.653,5.99,9.515,6.036,9.386C5.759,9.418,5.497,9.434,5.253,9.434
		c-1.3-0.016-2.288-0.425-2.97-1.226C1.57,7.463,1.214,6.594,1.214,5.604c0-1.198,0.505-2.284,1.516-3.259
		c0.692-0.57,1.412-0.943,2.16-1.118C5.63,1.076,6.322,1,6.97,1h4.876L10.34,1.879H8.837z M9.778,15.917
		c0-0.619-0.202-1.157-0.604-1.617c-0.428-0.436-1.095-0.972-2.005-1.606c-0.155-0.017-0.338-0.024-0.547-0.024
		c-0.124-0.015-0.442,0-0.955,0.048c-0.504,0.071-1.021,0.186-1.55,0.345c-0.124,0.048-0.298,0.12-0.522,0.215
		c-0.226,0.104-0.455,0.25-0.688,0.439c-0.226,0.198-0.415,0.445-0.57,0.738c-0.179,0.31-0.268,0.683-0.268,1.118
		c0,0.856,0.388,1.563,1.163,2.118c0.739,0.556,1.747,0.841,3.029,0.857c1.149-0.017,2.026-0.271,2.632-0.762
		C9.482,17.301,9.778,16.679,9.778,15.917z M6.379,8.769c0.643-0.024,1.177-0.255,1.604-0.692c0.207-0.31,0.34-0.627,0.399-0.954
		c0.035-0.326,0.053-0.6,0.053-0.822c0-0.961-0.245-1.932-0.737-2.908C7.466,2.924,7.162,2.542,6.786,2.249
		C6.4,1.972,5.959,1.824,5.46,1.808C4.8,1.824,4.251,2.09,3.812,2.606C3.442,3.147,3.266,3.751,3.282,4.418
		c0,0.882,0.257,1.799,0.773,2.753c0.251,0.445,0.572,0.822,0.969,1.131C5.419,8.614,5.87,8.769,6.379,8.769z"/>
	<polygon fill="#333333" points="18.453,3.664 15.895,3.664 15.895,1.104 14.654,1.104 14.654,3.664 12.096,3.664 12.096,4.904 
		14.654,4.904 14.654,7.462 15.895,7.462 15.895,4.904 18.453,4.904 	"/>
</g>
</svg>' . "\n";
				$button .= '<span class="ess-share-text">Googgle+</span>' . "\n";
				$button .= '</div>' . "\n";
				
				if ($show_count) {
					$button .= '<span class="ess-social-count">' . $this->get_google_count( $current_url ) . '</span>';
				}

				$button .= '</a>' . "\n";
			break;

			case 'pinterest':
				$pinterest_url = 'http://pinterest.com/pin/create/bookmarklet/';
				$pinterest_url .= '?media=' . rawurlencode( $image_url );
				$pinterest_url .= '&amp;url=' . $link;
				$pinterest_url .= '&amp;is_video=false';
				$pinterest_url .= '&amp;description=' . $title . rawurlencode(' – ') . $description;

				$button = '<a class="ess-button ess-button--pinterest" href="' . $pinterest_url . '" onclick="window.open(this.href, \'pinterestwindow\',\'left=20,top=20,width=750,height=750,toolbar=0,resizable=1\');" title="Pin" target="_blank">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="24px" height="20px" viewBox="0 0 24 20" enable-background="new 0 0 24 20" xml:space="preserve">
<path fill="#333333" d="M7.986,0.771C2.676,0.771,0,4.578,0,7.752c0,1.921,0.728,3.631,2.288,4.267
	c0.256,0.104,0.484,0.005,0.559-0.28c0.052-0.195,0.176-0.688,0.229-0.896c0.075-0.28,0.047-0.377-0.161-0.622
	C2.465,9.689,2.178,9.003,2.178,8.029c0-2.823,2.111-5.35,5.5-5.35c3,0,4.649,1.833,4.649,4.281c0,3.222-1.425,5.94-3.542,5.94
	c-1.169,0-2.043-0.966-1.763-2.152c0.334-1.414,0.985-2.943,0.985-3.964c0-0.914-0.491-1.677-1.506-1.677
	c-1.195,0-2.156,1.237-2.156,2.892c0,1.055,0.356,1.767,0.356,1.767s-1.223,5.182-1.438,6.089c-0.054,0.233-0.094,0.471-0.126,0.712
	c-0.011,0.078-0.015,0.155-0.023,0.232c-0.013,0.104-0.023,0.218-0.031,0.338c0.001-0.017,0.003-0.032,0.004-0.048
	c-0.02,0.302-0.028,0.603-0.026,0.888c-0.002-0.281,0.006-0.575,0.022-0.84c-0.03,0.356-0.056,0.851-0.02,1.317
	c0.002,0.001,0.004,0.009,0.005,0.016c0.03,0.359,0.1,0.691,0.231,0.906c0.058,0.094,0.126,0.167,0.208,0.21
	c0.205,0.104,0.42,0.003,0.626-0.21c0.118-0.12,0.233-0.274,0.344-0.448c0.397-0.631,0.721-1.495,0.814-1.764
	c0.085-0.216,0.162-0.435,0.222-0.655c0.137-0.496,0.787-3.07,0.787-3.07c0.387,0.738,1.521,1.392,2.729,1.392
	c3.592,0,6.03-3.274,6.03-7.658C15.061,3.859,12.251,0.771,7.986,0.771z M3.069,18.42c-0.004-0.136-0.008-0.283-0.008-0.437
	c0.001,0.149,0.005,0.297,0.01,0.438C3.071,18.421,3.07,18.42,3.069,18.42z"/>
</svg>' . "\n";
				$button .= '<span class="ess-share-text">Pinterest</span>' . "\n";
				$button .= '</div>' . "\n";
				
				if ($show_count) {
					$button .= '<span class="ess-social-count">' . $this->get_pinterest_count( $current_url ) . '</span>';
				}

				$button .= '</a>' . "\n";
			break;

			case 'email':

				$email_url = 'mailto:';
				$email_url .= '?subject=' . $title;
				$email_url .= '&amp;body=' . $description . '%20%20' . rawurlencode($direct_link);

				$button = '<a class="ess-button ess-button--email" href="' . $email_url . '" title="Email">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="24px" height="20px" viewBox="0 0 24 20" enable-background="new 0 0 24 20" xml:space="preserve">
<g>
	<path fill="#333333" d="M22,4v14H2V4H22 M24,2H0v18h24V2L24,2z"/>
</g>
<polyline fill="none" stroke="#333333" stroke-width="2" stroke-miterlimit="10" points="0.074,2.5 12.125,14.25 24.176,2.5 "/>
</svg>' . "\n";
				$button .= '<span class="ess-share-text">Email</span>' . "\n";
				$button .= '</div>' . "\n";
				$button .= '</a>' . "\n";
			break;

			case 'link':

				$button = '<a class="ess-button ess-button--link" href="' . $direct_link . '" title="Share Direct Link" onclick="event.preventDefault(); ">' . "\n";
				$button .= '<div class="ess-button-inner">' . "\n";
				$button .= '<svg class="ess-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1024 1024"><g><path class="path1" d="M440.236 635.766c-13.31 0-26.616-5.076-36.77-15.23-95.134-95.136-95.134-249.934 0-345.070l192-192c46.088-46.086 107.36-71.466 172.534-71.466s126.448 25.38 172.536 71.464c95.132 95.136 95.132 249.934 0 345.070l-87.766 87.766c-20.308 20.308-53.23 20.308-73.54 0-20.306-20.306-20.306-53.232 0-73.54l87.766-87.766c54.584-54.586 54.584-143.404 0-197.99-26.442-26.442-61.6-41.004-98.996-41.004s-72.552 14.562-98.996 41.006l-192 191.998c-54.586 54.586-54.586 143.406 0 197.992 20.308 20.306 20.306 53.232 0 73.54-10.15 10.152-23.462 15.23-36.768 15.23z"></path><path class="path2" d="M256 1012c-65.176 0-126.45-25.38-172.534-71.464-95.134-95.136-95.134-249.934 0-345.070l87.764-87.764c20.308-20.306 53.234-20.306 73.54 0 20.308 20.306 20.308 53.232 0 73.54l-87.764 87.764c-54.586 54.586-54.586 143.406 0 197.992 26.44 26.44 61.598 41.002 98.994 41.002s72.552-14.562 98.998-41.006l192-191.998c54.584-54.586 54.584-143.406 0-197.992-20.308-20.308-20.306-53.232 0-73.54 20.306-20.306 53.232-20.306 73.54 0.002 95.132 95.134 95.132 249.932 0.002 345.068l-192.002 192c-46.090 46.088-107.364 71.466-172.538 71.466z"></path></g></svg>' . "\n";
				$button .= '<span class="ess-share-text">' . $direct_link . '</span>' . "\n";
				$button .= '</div>' . "\n";
				$button .= '<div class="ess-share-link-wrap">' . "\n";
				$button .= '<input class="ess-share-link" type="text" value="' . $direct_link . '" onclick="this.setSelectionRange(0, this.value.length)"/>' . "\n";
				$button .= '</div>' . "\n";
				$button .= '</a>' . "\n";
			break;

			default:
			break;
		}

		return $button;
	}// End get_button_html()

	/**
	 * Build the html the share button component
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function build_share_buttons($button_type) {
		global $post;

		$html = '';

		$sites = get_option('ess_social_sites');

		if ( is_array( $sites ) ) {

			$extra_classes = '';
			$show_count = false;

			switch ($button_type) {
				case 'text':
					$extra_classes = 'ess-buttons--text';
					break;
				case 'count':
					$extra_classes = 'ess-buttons--count';
					$show_count = true;
					break;
			}

			$html = '<ul class="ess-buttons ' . $extra_classes . '">' . "\n";

			// Get post thumbnail if it has one
			$thumbnail_url = '';
			$thumbnail_id = get_post_thumbnail_id( $post->ID );

			if ( !empty( $thumbnail_id ) ) {
				$thumbnail = wp_get_attachment_image_src($thumbnail_id,'large', true);

				if ( $thumbnail ) {
					$thumbnail_url = $thumbnail[0];
				}
			}
		
			foreach($sites as $site) {

				$button = $this->get_button_html($site, $post->ID, $show_count, $thumbnail_url);

				$html .= '<li>' . $button . '</li>' . "\n";
			}

			$html .= '</ul>' . "\n";
		}

		return $html;
	} // End build_share_buttons()

	/**
	 * Add the social sharing buttons either before or after the post content.
	 * @access  public
	 * @since   1.0.0
	 * @return  string
	 */
	public function add_share_buttons_to_post($content) {
		if (is_single()) {
			$location = get_option('ess_share_location');

			$button_type = get_option( 'ess_share_type' );

			if ( is_array( $location ) ) {
				foreach($location as $position) {
					if ($position == 'before') {
						$content = $this->build_share_buttons($button_type) . $content;
					}
					if ($position == 'after') {
						$content = $content . $this->build_share_buttons($button_type);
					}
				}
			}
		}
		

		return $content;
	} // End add_share_buttons_to_post()

	

	/**
	 * Shortcode for adding the sharing buttons to content or templates
	 * [ess_post], [ess_post share_type="count"], <?php echo do_shortcode('[ess_post share_type="count"]'); ?>
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  string
	 */
	public function share_post_shortcode( $atts, $content = null ) {
		$options = shortcode_atts( array(
			'share_type' => 'basic'
		), $atts );

		$button_type = $options['share_type'];

		return $this->build_share_buttons($button_type);
	} // End share_post_shortcode()

	/**
	 * Add the social sharing buttons to post media.
	 * @access  public
	 * @since   1.0.0
	 * @return  string
	 */
	public function add_share_buttons_to_media($content) {
		global $post;

		if (get_option('show_media_buttons')) {
	
			$content = do_shortcode($content);

			$wrap_class = 'ess-image-wrap';	
		  
			if ( trim( $content ) != '' ) {

				libxml_use_internal_errors(true);
				$dom = new DOMDocument();
			
				$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'));
				
				$wrapper_div = $dom->createElement('div');
				$wrapper_div->setAttribute('class', $wrap_class);

				$ImageList = $dom->getElementsByTagName('img');

				foreach ( $ImageList as $key => $Image ) {

					$share_list = $dom->createElement('ul');
					$share_list->setAttribute('class', 'ess-buttons');

					$sites = get_option('ess_media_social_sites');

					$random_id = substr(base64_encode(basename($Image->getAttribute('src'))), 0, 15);

					foreach($sites as $site) {

						$button = $this->get_button_html($site, $post->ID, false, $Image->getAttribute('src'), $random_id);

						$share_item = $dom->createDocumentFragment();
						$share_item->appendXML($button);
						$share_list->appendChild( $share_item );

					}//End social sites foreach

					if ( $Image->parentNode->nodeName == 'a' ) {

						$link_parent = $Image->parentNode;

						$wrap_clone = $wrapper_div->cloneNode();

						$wrap_clone->setAttribute('id', $random_id);

						$link_parent->parentNode->replaceChild($wrap_clone, $link_parent);
						$wrap_clone->appendChild($link_parent);

						$wrap_clone->appendChild( $share_list );
						
					} else {
						
						$wrap_clone = $wrapper_div->cloneNode();

						$wrap_clone->setAttribute('id', $random_id);

						$Image->parentNode->replaceChild($wrap_clone, $Image);
						$wrap_clone->appendChild($Image);

						$wrap_clone->appendChild( $share_list );
						
					}

				}//End Images foreach
			

				//Fixed the issue with additional html tags loading
				$content = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));

			}// End if has content

		}//End if media share option on

	   // return the processed content
	   return $content;
	
	} //End add_share_buttons_to_media()

}//End class Easy_Social_Share_Buttons
