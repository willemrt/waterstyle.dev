<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/*-----------------------------------------------------------------------------------
WC EI Orders Mode Order Received Settings

TABLE OF CONTENTS

- var parent_tab
- var subtab_data
- var option_name
- var form_key
- var position
- var form_fields
- var form_messages

- __construct()
- subtab_init()
- set_default_settings()
- get_settings()
- subtab_data()
- add_subtab()
- settings_form()
- init_form_fields()

-----------------------------------------------------------------------------------*/

class WC_EI_Orders_Mode_Order_Received_Settings extends WC_Email_Inquiry_Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'order-received';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wc_email_inquiry_order_order_received_page';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wc_email_inquiry_order_order_received_page';
	
	/**
	 * @var string
	 * You can change the order show of this sub tab in list sub tabs
	 */
	private $position = 1;
	
	/**
	 * @var array
	 */
	public $form_fields = array();
	
	/**
	 * @var array
	 */
	public $form_messages = array();
	
	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		$this->init_form_fields();
		//$this->subtab_init();
		
		$this->form_messages = array(
				'success_message'	=> __( 'Order Received Settings successfully saved.', 'wc_email_inquiry' ),
				'error_message'		=> __( 'Error: Order Received Settings can not save.', 'wc_email_inquiry' ),
				'reset_message'		=> __( 'Order Received Settings successfully reseted.', 'wc_email_inquiry' ),
			);
			
		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );
				
		add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* subtab_init() */
	/* Sub Tab Init */
	/*-----------------------------------------------------------------------------------*/
	public function subtab_init() {
		
		add_filter( $this->plugin_name . '-' . $this->parent_tab . '_settings_subtabs_array', array( $this, 'add_subtab' ), $this->position );
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* set_default_settings()
	/* Set default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function set_default_settings() {
		global $wc_ei_admin_interface;
		
		$wc_ei_admin_interface->reset_settings( $this->form_fields, $this->option_name, false );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* get_settings()
	/* Get settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function get_settings() {
		global $wc_ei_admin_interface;
		
		$wc_ei_admin_interface->get_settings( $this->form_fields, $this->option_name );
	}
	
	/**
	 * subtab_data()
	 * Get SubTab Data
	 * =============================================
	 * array ( 
	 *		'name'				=> 'my_subtab_name'				: (required) Enter your subtab name that you want to set for this subtab
	 *		'label'				=> 'My SubTab Name'				: (required) Enter the subtab label
	 * 		'callback_function'	=> 'my_callback_function'		: (required) The callback function is called to show content of this subtab
	 * )
	 *
	 */
	public function subtab_data() {
		
		$subtab_data = array( 
			'name'				=> 'order-received',
			'label'				=> __( 'Order Received', 'wc_email_inquiry' ),
			'callback_function'	=> 'wc_ei_orders_mode_order_received_settings_form',
		);
		
		if ( $this->subtab_data ) return $this->subtab_data;
		return $this->subtab_data = $subtab_data;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* add_subtab() */
	/* Add Subtab to Admin Init
	/*-----------------------------------------------------------------------------------*/
	public function add_subtab( $subtabs_array ) {
	
		if ( ! is_array( $subtabs_array ) ) $subtabs_array = array();
		$subtabs_array[] = $this->subtab_data();
		
		return $subtabs_array;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* settings_form() */
	/* Call the form from Admin Interface
	/*-----------------------------------------------------------------------------------*/
	public function settings_form() {
		global $wc_ei_admin_interface;
		
		$output = '';
		$output .= $wc_ei_admin_interface->admin_forms( $this->form_fields, $this->form_key, $this->option_name, $this->form_messages );
		
		return $output;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* init_form_fields() */
	/* Init all fields of this form */
	/*-----------------------------------------------------------------------------------*/
	public function init_form_fields() {
		
  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(
		
			array(
            	'name' 		=> __( 'Customize Order Request Received (Order Received) Page', 'wc_email_inquiry' ),
				'desc'		=> __( "Landing page after Order is submitted. In Order mode system prices for each item is visible. Add text to the top and bottom of the table of items to tell your customers what to expect next.", 'wc_email_inquiry' ),
                'type' 		=> 'heading',
                'id'		=> 'wc_ei_order_recevied_box',
                'is_box'	=> true,
           	),
			array(  
				'name' 		=> __( 'Request Received Page Title', 'wc_email_inquiry' ),
				'desc' 		=> __( "Replace Order Received page name", 'wc_email_inquiry' ),
				'id' 		=> 'order_order_received_page_name',
				'type' 		=> 'text',
				'default'	=> __( 'Request Received', 'wc_email_inquiry' ),
			),
			array(  
				'name' 		=> __( 'Top of page Message', 'wc_email_inquiry' ),
				'desc' 		=> __( "Message that user sees on top of Request Received page", 'wc_email_inquiry' ),
				'id' 		=> 'wc_email_inquiry_order_order_received_top_message',
				'type' 		=> 'wp_editor',
				'default'	=> '',
				'separate_option'	=> true,
			),
			array(  
				'name' 		=> __( 'Bottom of page Message', 'wc_email_inquiry' ),
				'desc' 		=> __( "Message that user sees on bottom of Request Received page", 'wc_email_inquiry' ),
				'id' 		=> 'wc_email_inquiry_order_order_received_bottom_message',
				'type' 		=> 'wp_editor',
				'default'	=> '',
				'separate_option'	=> true,
			),
			
        ));
	}
}

global $wc_ei_orders_mode_order_received_settings;
$wc_ei_orders_mode_order_received_settings = new WC_EI_Orders_Mode_Order_Received_Settings();

/** 
 * wc_ei_orders_mode_order_received_settings_form()
 * Define the callback function to show subtab content
 */
function wc_ei_orders_mode_order_received_settings_form() {
	global $wc_ei_orders_mode_order_received_settings;
	$wc_ei_orders_mode_order_received_settings->settings_form();
}

?>