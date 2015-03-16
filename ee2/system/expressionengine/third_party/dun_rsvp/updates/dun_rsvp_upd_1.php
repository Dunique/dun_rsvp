<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update file for the update to 1
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
 
class Dun_rsvp_upd_1
{
	private $EE;
	private $version = '1';
	
	/**
	 * Construct method
	 *
	 * @return      boolean         TRUE
	 */
	public function __construct()
	{	
		//load the classes
		ee()->load->dbforge();
	}
	
	/**
	 * Run the update
	 *
	 * @return      boolean         TRUE
	 */
	public function run_update()
	{
		/*$sql = array();
		
		//add new extension
		$sql[] = "INSERT INTO `exp_entry_api_settings` (`site_id`, `var`, `value`) VALUES (1, 'debug', false)";
		$sql[] = "UPDATE  `exp_extensions` SET `method` = 'sessions_start', `hook` = 'sessions_start', `version` = '1.2' WHERE `class` = 'Entry_api_ext' AND `hook` = 'sessions_end'";

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}*/
	}
}

/* End of file default_upd_1.php  */
/* Location: ./system/expressionengine/third_party/default/updates/default_upd_1.php */