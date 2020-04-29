<?php

/**
 * Display a notice when parent theme may have already been configured
 */
class Cryout_Notice {

	private $slug = 'cryout';
	private $multisite = FALSE;
	private $admin_url = '';
	private $strings = array( // array will be initialized with translatable strings
		1 => '', // already configured
		2 => '', // reset link
		3 => '', // dismiss string
		4 => '', // button label
	);

	public function __construct( $params = array() ) {
		if (is_multisite()) {
			$this->multisite = TRUE;
			return false; // no support for multisite for now
		}
		
		// initialize parameters
		if (!empty($params['slug'])) $this->slug = $params['slug'];
		if (!empty($params['strings'])) $this->strings = $params['strings'];

		add_action( 'admin_init', array( $this, 'init') );
		if ($this->first_time_nag()) {
			if ($this->multisite) {
				//add_action( 'network_admin_notices', array( $this, 'first_time_message' ) );
			} else {
				add_action( 'admin_notices', array( $this, 'first_time_message' ) );
			}
		}
	} // __construct()

	function init() {
		$this->admin_url = sprintf( admin_url( 'themes.php?page=about-%s-theme' ), $this->slug );
		if ( !empty($_REQUEST['_'.$this->slug.'_configured_nonce']) && wp_verify_nonce( $_REQUEST['_'.$this->slug.'_configured_nonce'], 'disable_nag' ) ) {
			// turn off the first time message
			$this->disable_nag();
			wp_safe_redirect( $this->admin_url );
			exit;
		};
		// set_theme_mod( 'codex_configured', FALSE ); // dev mode 
	} // init()

	// checks if first time message was already dismissed
	function first_time_nag() {
		$parent = get_option( _CRYOUT_THEME_SLUG . '_settings' );
		$configured = get_theme_mod( $this->slug . '_configured', false );
		if ($parent && !$configured) return true;
		return false;
	} // first_time_nag()

	function first_time_message() {
		?>
		<div class="notice notice-info is-dismissible">
			<p><?php printf(
				$this->strings[1],
				ucwords( _CRYOUT_THEME_SLUG ),
				sprintf( '<a href="%1$s">%2$s</a>', $this->admin_url, $this->strings[2] )
				); ?></p>
			<p> <?php echo $this->strings[3]; ?>
				<a class="button" href="<?php echo wp_nonce_url( $this->admin_url, 'disable_nag', '_codex_configured_nonce') ?>">
					<?php echo $this->strings[4]; ?>
				</a>
			</p>

		</div>
		<?php
	} // first_time_message()

	// prevent future nag
	function disable_nag() {
		set_theme_mod( $this->slug . '_configured', TRUE );
	} // disable_nag()

} // class

// FIN