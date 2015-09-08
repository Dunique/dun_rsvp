<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default
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
 
class Dun_rsvp_upd {
	
	public $version = DUN_RSVP_VERSION;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{		
		//load the classes
		ee()->load->dbforge();
		
		//require the settings
		require PATH_THIRD.DUN_RSVP_MAP.'/settings.php';
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{		
		//set the module data
		$mod_data = array(
			'module_name'			=> DUN_RSVP_CLASS,
			'module_version'		=> DUN_RSVP_VERSION,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'Y'
		);
	
		//insert the module
		ee()->db->insert('modules', $mod_data);
		
		//create some actions for the ajax in the control panel
		$this->_register_action('update_rsvp_response');
		$this->_register_action('invite_non_member');
		$this->_register_action('decline_rsvp_event');
		$this->_register_action('vcard_event');
		$this->_register_action('invite_non_member');

		//install the extension
		//$this->_register_hook('sessions_start', 'sessions_start');
		
		//create the Login backup tables
		$this->_create_tables();

		//Add tabs
		ee()->load->library('layout');
		ee()->layout->add_layout_tabs($this->_tabs(), DUN_RSVP_MAP);

		// add custom email templates
		ee()->db->insert('specialty_templates', array(
			'template_name'	=> 'dun_rsvp_confirmation',
			'data_title'	=> '{title}',
			'template_data'	=> <<<EOF
Hi {name},

Thank you for registering your attendance to this event.

Event: {title}
Date: {entry_date format="%j %M %Y at %g:%i %A"}
Seats Reserved: {seats_reserved}
{fields}{label} : {value}
{/fields}

We look forward to seeing you there!
EOF
		));

		ee()->db->insert('specialty_templates', array(
			'template_name'	=> 'dun_rsvp_cancellation',
			'data_title'	=> '{title}',
			'template_data'	=> <<<EOF
Hi {name},

We have removed you from the attendance list for this event.

Event: {title}
Date: {entry_date format="%j %M %Y at %g:%i %A"}

See you next time!
EOF
		));
		
		ee()->db->insert('specialty_templates', array(
			'template_name'	=> 'dun_rsvp_update',
			'data_title'	=> '{title}',
			'template_data'	=> <<<EOF
Hi {name},

Your registration has been updated.

Event: {title}
Date: {entry_date format="%j %M %Y at %g:%i %A"}
Seats Reserved: {seats_reserved}
{fields}{label} : {value}
{/fields}

See you next time!
EOF
		));
		
		ee()->db->insert('specialty_templates', array(
            'template_name' => 'dun_rsvp_invitation',
            'data_title'    => '{title}',
            'template_data' => <<<EOF
Hi {name},

We would like to invite you for the {title} event.

Event: {title}
Date: {entry_date format="%j %M %Y at %g:%i %A"}

Yes, i would like to come {invite_url}
No, i cannot come to this event. {decline_url}

See you next time!
EOF
        ));
		
		ee()->db->insert('specialty_templates', array(
            'template_name' => 'dun_rsvp_invitation_non_member',
            'data_title'    => '{title}',
            'template_data' => <<<EOF
Hi {name},

{invite_name} would like to invite you for the {title} event.

Special note from {invite_name}:
-----------------------------
{notes}
-----------------------------


Event: {title}
Date: {entry_date format="%j %M %Y at %g:%i %A"}

See you next time!
EOF
        ));

		//load the helper
		ee()->load->library(DUN_RSVP_MAP.'_lib');
		
		//insert the settings data
		ee()->dun_rsvp_settings->first_import_settings();	
		
		return TRUE;
	}

	// ----------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall()
	{
		//delete the module
		ee()->db->where('module_name', DUN_RSVP_CLASS);
		ee()->db->delete('modules');

		//remove databases
		ee()->dbforge->drop_table(DUN_RSVP_CLASS.'_settings');
		ee()->dbforge->drop_table(DUN_RSVP_CLASS.'_declines');
		ee()->dbforge->drop_table(DUN_RSVP_CLASS.'_events');
		ee()->dbforge->drop_table(DUN_RSVP_CLASS.'_responses');
		ee()->dbforge->drop_table(DUN_RSVP_CLASS.'_fields');
		ee()->dbforge->drop_table(DUN_RSVP_CLASS.'_invite_non_members');
		
		ee()->db->where('template_name', 'dun_rsvp_confirmation');
		ee()->db->or_where('template_name', 'dun_rsvp_cancellation');
		ee()->db->or_where('template_name', 'dun_rsvp_invitation');
		ee()->db->or_where('template_name', 'dun_rsvp_update');
		ee()->db->or_where('template_name', 'dun_rsvp_invitation_non_member');
		ee()->db->delete('specialty_templates');
		
		//remove actions
		ee()->db->where('class', DUN_RSVP_CLASS);
		ee()->db->delete('actions');
		
		//remove the extension
		ee()->db->where('class', DUN_RSVP_CLASS.'_ext');
		ee()->db->delete('extensions');

		//delete tabs
		ee()->load->library('layout');
		ee()->layout->delete_layout_tabs($this->_tabs(), DUN_RSVP_MAP);
		
		return TRUE;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function update($current = '')
	{
		//nothing to update
		if ($current == '' OR $current == $this->version)
			return FALSE;
		
		//loop through the updates and install them.
		if(!empty($this->updates))
		{
			foreach ($this->updates as $version)
			{
				//$current = str_replace('.', '', $current);
				//$version = str_replace('.', '', $version);
				
				if ($current < $version)
				{
					$this->_init_update($version);
				}
			}
		}
			
		return true;
	}
		
	// ----------------------------------------------------------------
	
	/**
	 * Add the tables for the module
	 *
	 * @return 	boolean 	TRUE
	 */	
	private function _create_tables()
	{			
		// add config tables
		$fields = array(
				'settings_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'site_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'var'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'value'  => array(
									'type' 			=> 'text'
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('settings_id', TRUE);
		ee()->dbforge->create_table(DUN_RSVP_MAP.'_settings', TRUE);

		// add config tables
		$fields = array(
				'entry_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'member_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								)
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('entry_id');
		ee()->dbforge->add_key('member_id');
		ee()->dbforge->create_table(DUN_RSVP_MAP.'_declines', TRUE);

		// add config tables
		$fields = array(
				'entry_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'total_seats'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('entry_id');
		ee()->dbforge->create_table(DUN_RSVP_MAP.'_events', TRUE);

		// add config tables
		$fields = array(
				'response_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'entry_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'member_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'seats_reserved'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'public'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '10',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'updated'		=> array(
									'type' => 'int',
									'constraint' => '10'
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('entry_id');
		ee()->dbforge->add_key('response_id', TRUE);
		ee()->dbforge->create_table(DUN_RSVP_MAP.'_responses', TRUE);
		
		// add config tables
		$fields = array(
				'field_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'response_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'woonplaats'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'land'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								),
				
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('field_id', TRUE);
		ee()->dbforge->create_table(DUN_RSVP_MAP.'_fields', TRUE);
	
		// add config tables
		$fields = array(
				'invite_non_member_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'entry_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'member_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'email'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'name'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('invite_non_member_id', TRUE);
		ee()->dbforge->add_key('entry_id');
		ee()->dbforge->create_table(DUN_RSVP_MAP.'_invite_non_members', TRUE);
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Install a hook for the extension
	 *
	 * @return 	boolean 	TRUE
	 */		
	private function _register_hook($hook, $method = NULL, $priority = 10)
	{
		if (is_null($method))
		{
			$method = $hook;
		}

		if (ee()->db->where('class', DUN_RSVP_CLASS.'_ext')
			->where('hook', $hook)
			->count_all_results('extensions') == 0)
		{
			ee()->db->insert('extensions', array(
				'class'		=> DUN_RSVP_CLASS.'_ext',
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> '',
				'priority'	=> $priority,
				'version'	=> DUN_RSVP_VERSION,
				'enabled'	=> 'y'
			));
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Create a action
	 *
	 * @return 	boolean 	TRUE
	 */	
	private function _register_action($method)
	{		
		if (ee()->db->where('class', DUN_RSVP_CLASS)
			->where('method', $method)
			->count_all_results('actions') == 0)
		{
			ee()->db->insert('actions', array(
				'class' => DUN_RSVP_CLASS,
				'method' => $method
			));
		}
	}

	// ----------------------------------------------------------------
	
	/**
	 * Create a tab
	 *
	 * @return 	boolean 	TRUE
	 */	
	private function _tabs()
	{	
		$tabs['dun_rsvp'] = array(
			DUN_RSVP_MAP.'_enabled'			=> array('visible' => 'true',
												'collapse' => 'false',
												'htmlbuttons' => 'false',
												'width' => '100%'
											),
			DUN_RSVP_MAP.'_total_seats'		=> array('visible' => 'true',
												'collapse' => 'false',
												'htmlbuttons' => 'false',
												'width' => '100%'
											)
		);

		return $tabs;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Run a update from a file
	 *
	 * @return 	boolean 	TRUE
	 */	
	
	private function _init_update($version, $data = '')
	{
		// run the update file
		$class_name = DUN_RSVP_CLASS.'_upd_'.str_replace('.', '', $version);
		require_once(PATH_THIRD.DUN_RSVP_MAP.'/updates/'.strtolower($class_name).'.php');
		$updater = new $class_name($data);
		return $updater->run_update();
	}
	
}
/* End of file upd.default.php */
/* Location: /system/expressionengine/third_party/default/upd.default.php */