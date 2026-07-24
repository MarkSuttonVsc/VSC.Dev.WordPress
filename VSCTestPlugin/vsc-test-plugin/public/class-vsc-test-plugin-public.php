<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    VSC_Test_Plugin
 * @subpackage VSC_Test_Plugin/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    VSC_Test_Plugin
 * @subpackage VSC_Test_Plugin/public
 * @author     Mark Sutton, VSC Ltd
 */

class VSC_Test_Plugin_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $vsc_test_plugin    The ID of this plugin.
	 */
	private $vsc_test_plugin;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $vsc_test_plugin       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $vsc_test_plugin, $version ) {

		$this->vsc_test_plugin = $vsc_test_plugin;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in VSC_Test_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The VSC_Test_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->vsc_test_plugin, plugin_dir_url( __FILE__ ) . 'css/vsc-test-plugin-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in VSC_Test_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The VSC_Test_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->vsc_test_plugin, plugin_dir_url( __FILE__ ) . 'js/vsc-test-plugin-public.js', array( 'jquery' ), $this->version, false );

	}

}
