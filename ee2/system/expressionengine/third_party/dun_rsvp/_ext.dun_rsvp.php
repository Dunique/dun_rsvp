<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default Extension
 *
 * @package		Default module
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'dun_rsvp/config.php';
 
class Dun_rsvp_ext 
{	
	
	public $name			= DUN_RSVP_NAME;
	public $description		= DUN_RSVP_DESCRIPTION;
	public $version			= DUN_RSVP_VERSION;
	public $settings 		= array();
	public $docs_url		= DUN_RSVP_DOCS;
	public $settings_exist	= 'n';
	//public $required_by 	= array(DUN_RSVP_NAME);
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		//get the instance of the EE object
		$this->EE =& get_instance();		
	}

	/**
	 * hook_function_here
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function hook_function_here($ee)
	{
		
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		//the module will install the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		//the module will disable the extension if needed
		return true;
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		//the module will update the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.default.php */
/* Location: /system/expressionengine/third_party/default/ext.default.php */