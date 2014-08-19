<?php
/**
 * @package Multisite_Directory_Admin_Settings
 * @author  Trisha Salas <trisha@trishasalas.com>
 */

/**
 * Multisite Directory Page Options
 * @version 0.1.0
 */
class Multisite_Directory_Settings {
 	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'mdp_multisite_directory_options';

	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	protected $option_metabox = array();

	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( '&nbsp;Multisite &nbsp;Directory &nbsp;', 'mdp-multisite-directory' );
 	}

	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
	}

	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}

	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ), 'dashicons-book-alt' );
	}

	/**
	 * Admin page markup. Mostly handled by CMB
	 * @since  0.1.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb_options_page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb_metabox_form( self::option_fields(), $this->key ); ?>
		</div>
		<?php
	}

	/**
	 * Defines the theme option metabox and field configuration
	 * @since  0.1.0
	 * @return array
	 */
	public function option_fields() {

		// Only need to initiate the array once per page-load
		if ( ! empty( $this->option_metabox ) ) {
			return $this->option_metabox;
		}

		$this->fields = array(
			array(
				'name' => __( 'Directory Page Title', 'mdp_multisite-directory' ),
				'desc' => __( 'Choose a name for your directory page.  Default is "Directory"', 'mdp_multisite-directory' ),
				'id'   => 'page_title',
				'type' => 'text_medium',
			),
			array(
				'name' => __( 'Include the Site Description?', 'mdp_multisite-directory' ),
				'desc' => __( 'Would you like to show the site description for each site?', 'mdp_multisite-directory' ),
				'id'   => 'site_description',
				'type' => 'checkbox',
			),
			array(
				'name' => __( 'RSS link?', 'mdp_multisite-directory' ),
				'desc' => __( 'Display the RSS link for each site?', 'mdp_multisite-directory' ),
				'id'   => 'rss_link',
				'type' => 'checkbox',
			),
			array(
				'name'    => __( 'Choose an accent color', 'mdp_multisite-directory' ),
				'desc'    => __( '', 'mdp_multisite-directory' ),
				'id'      => 'accent_color',
				'type'    => 'colorpicker',
				'default' => '#efefef'
			),
		);

		$this->option_metabox = array(
			'id'         => 'option_metabox',
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
			'show_names' => true,
			'fields'     => $this->fields,
		);

		return $this->option_metabox;
	}


	public function __get( $field ) {

		// Allowed fields to retrieve
		if ( in_array( $field, array( 'key', 'fields', 'title', 'options_page' ), true ) ) {
			return $this->{$field};
		}
		if ( 'option_metabox' === $field ) {
			return $this->option_fields();
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

}

// Get it started
$Multisite_Directory_Settings = new Multisite_Directory_Settings();
$Multisite_Directory_Settings->hooks();

function Multisite_Directory_get_option( $key = '' ) {
	global $MDP_Multisite_Directory_Admin;
	return cmb_get_option( $Multisite_Directory_Admin->key, $key );
}

// Initialize the metabox class
add_action( 'init', 'mdp_initialize_cmb_meta_boxes', 9999 );
function mdp_initialize_cmb_meta_boxes() {
    if ( !class_exists( 'cmb_Meta_Box' ) ) {
        require_once( plugin_dir_path( __FILE__ ) . 'cmb/init.php' );
    }
}