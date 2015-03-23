<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * the settings for the module
 *
 * @package		Default module
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

//updates
$this->updates = array(
	//'1.2',
);

//Default Post
$this->default_post = array(
	'license_key'   		=> '',
	'report_date' 			=> time(),
	'report_stats' 			=> true,
	'channel'				=> '',
	'member_group'			=> '',
	'event_url'				=> '',
	'def_status'			=> 'Open',
	'email_from_address'	=> '',
	'email_from_name'		=> '',
	'email_bcc'				=> '',
	'enable_non_member_invites' => false,
	'button_rights' => 'a:7:{i:0;s:20:"rsvp_plus_edit_entry";i:1;s:27:"rsvp_plus_attendance_export";i:2;s:25:"rsvp_plus_email_attendees";i:3;s:21:"rsvp_plus_email_event";i:4;s:24:"rsvp_plus_email_reminder";i:5;s:26:"rsvp_plus_test_email_event";i:6;s:20:"rsvp_plus_add_member";}'
);

//overrides
$this->overide_settings = array(
	//'gmaps_icon_dir' => '[theme_dir]images/icons/',
	//'gmaps_icon_url' => '[theme_url]images/icons/',
);

// Backwards-compatibility with pre-2.6 Localize class
$this->format_date_fn = (version_compare(APP_VER, '2.6', '>=')) ? 'format_date' : 'decode_date';

//mcp veld header
$this->table_headers = array(
	DUN_RSVP_MAP.'_entry_id' => array('data' => lang(DUN_RSVP_MAP.'_entry_id'), 'style' => 'width:10%;'),
	DUN_RSVP_MAP.'_title' => array('data' => lang(DUN_RSVP_MAP.'_title'), 'style' => 'width:40%;'),
	DUN_RSVP_MAP.'_seats_reserved' => array('data' => lang(DUN_RSVP_MAP.'_seats_reserved'), 'style' => 'width:40%;'),
	DUN_RSVP_MAP.'_edit' => array(DUN_RSVP_MAP.'_edit' => '', 'style' => 'width:10%;')
);
$this->table_headers_fields = array(
	DUN_RSVP_MAP.'_field_name' => array('data' => lang(DUN_RSVP_MAP.'_field_name'), 'style' => 'width:80%;'),
	DUN_RSVP_MAP.'_edit' => array(DUN_RSVP_MAP.'_edit' => '', 'style' => 'width:10%;')
);

$this->fieldtype_settings = array(
	array(
		'label' => lang('license'),
		'name' => 'license',
		'type' => 't', // s=select, m=multiselect t=text
		//'options' => array('No', 'Yes'),
		'def_value' => '',
		'global' => true, //show on the global settings page
	),

);

/* End of file settings.php  */
/* Location: ./system/expressionengine/third_party/default/settings.php */