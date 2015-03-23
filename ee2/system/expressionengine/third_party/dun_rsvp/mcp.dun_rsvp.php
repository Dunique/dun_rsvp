<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default Module Control Panel File
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

class Dun_rsvp_mcp {
	
	public $return_data;
	public $settings;
	
	private $show_per_page = 25;
	private $_base_url;

	/**
	 * Constructor
	 */
	public function __construct()
	{		
		//load the library`s
		ee()->load->library('table');
		ee()->load->library(DUN_RSVP_MAP.'_lib');
		ee()->load->model(DUN_RSVP_MAP.'_model');
		ee()->load->helper('form');
		//ee()->load->library('javascript');
		//ee()->load->library('pagination');	
 
	   //set the right nav
		$right_nav = array();
		$right_nav[lang(DUN_RSVP_MAP.'_overview')] = ee()->dun_rsvp_settings->item('base_url');
		$right_nav[lang(DUN_RSVP_MAP.'_settings')] = ee()->dun_rsvp_settings->item('base_url').AMP.'method=settings';
		$right_nav[lang(DUN_RSVP_MAP.'_fields')] = ee()->dun_rsvp_settings->item('base_url').AMP.'method=fields';
		ee()->cp->set_right_nav($right_nav);

		define('RSVP_CP', 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=dun_rsvp');
		
		//require the default settings
		require PATH_THIRD.DUN_RSVP_MAP.'/settings.php';
	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		// Set Breadcrumb and Page Title
		$this->_set_cp_var('cp_page_title', lang(DUN_RSVP_MAP.'_module_name'));
		$vars['cp_page_title'] = lang(DUN_RSVP_MAP.'_module_name');

		//load the view
		return $this->overview();    
	}

	// ----------------------------------------------------------------

	/**
	 * Fields Function
	 *
	 * @return 	void
	 */
	public function add_new_field()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '') {

			ee()->load->dbforge();
			$fields = array(
				dun_rsvp_helper::create_url_title($_POST['field_name'], '_') => array('type' => 'TEXT')
			);
			ee()->dbforge->add_column(DUN_RSVP_MAP.'_fields', $fields);

			//set a message
			ee()->session->set_flashdata(
				'message_success',
				ee()->lang->line('field_added')
			);

			//redirect
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.DUN_RSVP_MAP.AMP.'method=fields');
		}

		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->dun_rsvp_settings->item('base_url'), lang(DUN_RSVP_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(DUN_RSVP_MAP.'_add_new_field'));
		$vars['cp_page_title'] = lang(DUN_RSVP_MAP.'_add_new_field');

		//default var array
		$vars = array();

		//vars for the view and the form
		$vars['settings']['default'] = array(
			DUN_RSVP_MAP.'_field_name'   => form_input('field_name', ''),
		);

		//load the view
		return ee()->load->view('add_new_field', $vars, TRUE);
	}

	// ----------------------------------------------------------------

	/**
	 * Fields Function
	 *
	 * @return 	void
	 */
	public function delete_field()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			ee()->load->dbforge();

			ee()->dbforge->drop_column(DUN_RSVP_MAP.'_fields', $_GET['field']);

			//set a message
			ee()->session->set_flashdata(
				'message_success',
				ee()->lang->line('field_deleted')
			);

			//redirect
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.DUN_RSVP_MAP.AMP.'method=fields');
		}

		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->dun_rsvp_settings->item('base_url'), lang(DUN_RSVP_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(DUN_RSVP_MAP.'_add_new_field'));
		$vars['cp_page_title'] = lang(DUN_RSVP_MAP.'_add_new_field');

		//default var array
		$vars = array();

		//vars for the view and the form
		$vars['settings']['default'] = array(
			DUN_RSVP_MAP.'_field_name'   => form_input('field_name', ''),
		);

		//load the view
		return ee()->load->view('delete_field', $vars, TRUE);
	}

	// ----------------------------------------------------------------

	/**
	 * Fields Function
	 *
	 * @return 	void
	 */
	public function fields()
	{
		//set vars
		$vars['theme_url'] = ee()->dun_rsvp_settings->item('theme_url');
		$vars['base_url_js'] = ee()->dun_rsvp_settings->item('base_url_js');
		$vars['table_headers'] = $this->table_headers_fields;

		//load the view
		return ee()->load->view('fields', $vars, TRUE);
	}

	// ----------------------------------------------------------------

	/**
	 * This method will be called by the table class to get the results
	 *
	 * @return 	void
	 */
	public function _datasource_fields($state)
	{
		$offset = $state['offset'];
		$order = $state['sort'];

		$results = ee()->dun_rsvp_model->get_custom_fields();

		$rows = array();

		if(!empty($results))
		{
			foreach($results as $key=>$val)
			{
				$rows[] = array(
					DUN_RSVP_MAP.'_field_name' => $val,
					DUN_RSVP_MAP.'_edit' => '<a href="'.ee()->dun_rsvp_settings->item('base_url').'&method=delete_field'.AMP.'field='.$val.'">Delete</a>',
				);
			}
		}
		//empty
		else
		{
			$rows[] = array(
				DUN_RSVP_MAP.'_field_name' => '',
				DUN_RSVP_MAP.'_edit' => '',
			);
		}

		//return the data
		return array(
			'rows' => $rows,
			'pagination' => array(
				'per_page'   => $this->show_per_page,
				'total_rows' => ee()->dun_rsvp_model->count_items(),
			),
		);
	}

	// ----------------------------------------------------------------

	/**
	 * view Function
	 *
	 * @return 	void
	 */
	public function view()
	{
		//defaults
		$data = array();
		
		// load details for the specified event
		$data['event'] = $this->get_event_data();
		
		if(ee()->dun_rsvp_settings->item('enable_non_member_invites'))
		{
			$data['event_non_member_invites'] = ee()->dun_rsvp_model->count_non_member_invite($data['event']['entry_id']);
		}
		
		
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->dun_rsvp_settings->item('base_url'), lang(DUN_RSVP_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', $data['event']['title']);
		$vars['cp_page_title'] = lang(DUN_RSVP_MAP.'_rsvp_events');
		
		$data['fields'] = ee()->dun_rsvp_lib->get_field_fields();

		// get pagination
		$rownum = (int)ee()->input->get('rownum');
		$perpage = (int)ee()->input->get('perpage');
		if ($perpage == 0)
		{
			$perpage = (int)ee()->input->cookie('perpage');
			if ($perpage == 0) { $perpage = 50; }
		}

		$data['attendance'] = ee()->dun_rsvp_model->get_rsvp_attendance(array(
			'entry_id' => $data['event']['entry_id'],
			'limit' => $perpage,
			'offset' => $rownum,
		))->result_array();

		$data['attendance_export_link'] = BASE.AMP.RSVP_CP.AMP.'method=attendance_export'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['email_link'] = BASE.AMP.RSVP_CP.AMP.'method=email_attendees'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['edit_entry_link'] = BASE.AMP.'C=content_publish'.AMP.'M=entry_form'.AMP.'channel_id='.$data['event']['channel_id'].AMP.'entry_id='.$data['event']['entry_id'];
		$data['email_event'] = BASE.AMP.RSVP_CP.AMP.'method=send_rsvp_event'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['email_test_event'] = BASE.AMP.RSVP_CP.AMP.'method=send_test_rsvp_event'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['add_member'] = BASE.AMP.RSVP_CP.AMP.'method=add_member_for_event'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['view_non_member_invites_link'] = BASE.AMP.RSVP_CP.AMP.'method=view_non_member_invites'.AMP.'entry_id='.$data['event']['entry_id'];
		

		// configure pagination
		ee()->load->library('pagination');
		$current_url = BASE.AMP.RSVP_CP.AMP.'method=view'.AMP.'entry_id='.$data['event']['entry_id'].AMP.'perpage='.$perpage;
		$page_config = $this->get_pagination_config($current_url, $data['event']['total_members_responded'], $perpage);
		ee()->pagination->initialize($page_config);
		$data['pagination'] =ee()->pagination->create_links();

		return ee()->load->view('view', $data, TRUE);
	}

	//show non member invites
	function view_non_member_invites()
	{
		$data['event'] = $this->get_event_data();
		
		if(isset($_GET['delete']) && $_GET['delete'] = 'yes' && isset($_GET['invite_id']))
		{
			ee()->dun_rsvp_model->delete_non_member_invite(ee()->input->get('invite_id'));
			ee()->session->set_flashdata('message_success', 'Invite deleted');
            ee()->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=view_non_member_invites'.AMP.'entry_id='.$data['event']['entry_id']);  
			exit;
		}
		
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->dun_rsvp_settings->item('base_url'), lang(DUN_RSVP_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', $data['event']['title']);
		$vars['cp_page_title'] = lang(DUN_RSVP_MAP.'_view_non_member_invites');
		
		$data['invites'] = ee()->dun_rsvp_model->get_non_member_invite($data['event']['entry_id'])->result_array();
	
		$data['delete_link'] = BASE.'&C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.DUN_RSVP_MAP.AMP.'method=view_non_member_invites&entry_id='.ee()->input->get('entry_id').'&delete=yes&invite_id=';
		return ee()->load->view('view_non_member_invites', $data, TRUE);
	}
	
	
	//sedn event
	function send_rsvp_event()
    {
        //get the entry_id
        $entry_id = (int)ee()->input->get('entry_id');
        //get event
        $event = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
        $attendee = ee()->dun_rsvp_model->get_entry_response($entry_id)->result_array();
        $decline = ee()->dun_rsvp_model->get_entry_decline($entry_id)->result_array();
        
        //Get the members
        $member_data = ee()->dun_rsvp_model->get_members(array_merge($attendee, $decline));
		
        if($member_data != false)
        {
            foreach($member_data->result_array() as $val)
            {
            	$response = array(
					'name' => $val['screen_name'],
					'email' => $val['email'],
					'member_id'	=> $val['member_id']
				);
				
                ee()->dun_rsvp_lib->send_rsvp_confirmation($event, $response, 'event_invitation'); 
            }  

            ee()->session->set_flashdata('message_success', lang('event_message_sent').' ('.$member_data->num_rows().' emails)');
            ee()->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=view'.AMP.'entry_id='.$event['entry_id']);  
        }       
        
        exit;
    }

	// ----------------------------------------------------------------

	/**
	 * delete attendee
	 *
	 * @return 	void
	 */
	public function delete_attendee()
	{
		$entry_id = ee()->input->get('entry_id');
		$member_id = ee()->input->get('member_id');
		 
		if($entry_id != '' && $member_id != '')
		{				
			//get member
			$member = ee()->dun_rsvp_model->get_member($member_id)->row_array();
			
			//get event
			$event = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
			
			$data = array(
				'entry_id' => $entry_id,
				'member_id' => $member_id,
				'seats_reserved' => 0
			);
			
			ee()->dun_rsvp_lib->send_rsvp_confirmation($event, array_merge($data, array('name' => $member['screen_name'],'email' => $member['email'])));
			ee()->dun_rsvp_model->remove_rsvp_response($entry_id, $member_id);
		}
		
		ee()->session->set_flashdata('message_success', lang('attendee_deleted'));
        ee()->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=view'.AMP.'entry_id='.$entry_id);  
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function overview()
	{
		//set vars
		$vars['theme_url'] = ee()->dun_rsvp_settings->item('theme_url');
		$vars['base_url_js'] = ee()->dun_rsvp_settings->item('base_url_js');
		$vars['table_headers'] = $this->table_headers;

		//load the view
		return ee()->load->view('overview', $vars, TRUE);  
	}
	
	// ----------------------------------------------------------------

	/**
	 * This method will be called by the table class to get the results
	 *
	 * @return 	void
	 */
	public function _datasource($state)
	{
		$offset = $state['offset'];
		$order = $state['sort'];

		$results = ee()->dun_rsvp_model->get_all_items('', $this->show_per_page, $offset, $order);

		$rows = array();

		if(!empty($results))
		{
			foreach($results as $key=>$val)
			{
				$rows[] = array(
					DUN_RSVP_MAP.'_entry_id' => $val->entry_id,
					DUN_RSVP_MAP.'_title' => $val->title,
					DUN_RSVP_MAP.'_seats_reserved' => $val->total_seats_reserved .' by '.$val->total_members_responded .' members ('.$val->total_seats_remaining.')',
					DUN_RSVP_MAP.'_edit' => '<a href="'.ee()->dun_rsvp_settings->item('base_url').'&method=view'.AMP.'entry_id='.$val->entry_id.'">Edit</a>',
				);
			}
		}
		//empty
		else
		{
			$rows[] = array(
				DUN_RSVP_MAP.'_entry_id' => '',
				DUN_RSVP_MAP.'_title' => '',
				DUN_RSVP_MAP.'_seats_reserved' => '',
			);
		}

		//return the data
		return array(
			'rows' => $rows,
			'pagination' => array(
				'per_page'   => $this->show_per_page,
				'total_rows' => ee()->dun_rsvp_model->count_items(),
			),
		);
	}
	
	// ----------------------------------------------------------------

	/**
	 * Settings Function
	 *
	 * @return 	void
	 */
	public function settings()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			ee()->dun_rsvp_settings->save_post_settings();
		}
				
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->dun_rsvp_settings->item('base_url'), lang(DUN_RSVP_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(DUN_RSVP_MAP.'_settings'));
		$vars['cp_page_title'] = lang(DUN_RSVP_MAP.'_settings');

		//default var array
		$vars = array();
		
		//license key
		$license_key = ee()->dun_rsvp_settings->item('license_key');
		$report_stats = ee()->dun_rsvp_settings->item('report_stats');
		$channels = ee()->dun_rsvp_model->get_channels();
		$channel = ee()->dun_rsvp_settings->item('channel');
		$member_groups = ee()->dun_rsvp_model->get_member_groups();
		$member_group = ee()->dun_rsvp_settings->item('member_group');
		$def_status = ee()->dun_rsvp_settings->item('def_status');
		$event_url = ee()->dun_rsvp_settings->item('event_url');
		$email_from_address = ee()->dun_rsvp_settings->item('email_from_address');
		$email_from_name = ee()->dun_rsvp_settings->item('email_from_name');
		$email_bcc = ee()->dun_rsvp_settings->item('email_bcc');
		$enable_non_member_invites = ee()->dun_rsvp_settings->item('enable_non_member_invites');
		$button_right = ee()->dun_rsvp_settings->item('button_rights');
		$button_rights = array(
			'rsvp_plus_edit_entry' => 'Edit Entry',
			'rsvp_plus_attendance_export' => 'Export button',
			'rsvp_plus_email_attendees' => 'E-mail deelnemers',
			'rsvp_plus_email_event' => 'Verstuur uitnodiging',
			'rsvp_plus_email_reminder'	=> 'Verstuur reminder',
			'rsvp_plus_test_email_event' => 'E-mail test uitnodiging',
			'rsvp_plus_add_member' => 'Voeg deelnemer toe',
		);

		//vars for the view and the form
		$vars['settings']['default'] = array(
			DUN_RSVP_MAP.'_license_key'   => form_input('license_key', $license_key),	
			DUN_RSVP_MAP.'_report_stats'  => array(form_dropdown('report_stats', array('1' => 'yes', '0' => 'no'), $report_stats), 'PHP & EE versions will be anonymously reported to help improve the product.'),
			DUN_RSVP_MAP.'_channel'  => array(form_dropdown('channel', $channels, $channel), 'Select your channel'),
			DUN_RSVP_MAP.'_member_group'  => array(form_multiselect('member_group[]', $member_groups, $member_group), 'Select your channel'),
			DUN_RSVP_MAP.'_button_rights'  => array(form_multiselect('button_rights[]', $button_rights, $button_right), 'Select your channel'),
			DUN_RSVP_MAP.'_def_status'  => form_dropdown('def_status', array('Closed' => 'Closed', 'Open' => 'Open'), $def_status),
			DUN_RSVP_MAP.'_event_url'   => form_input('event_url', $event_url),	
			DUN_RSVP_MAP.'_email_from_address'   => array(form_input('email_from_address', $email_from_address), 'The email address event reminder emails will be sent from. If left blank will use site default.'),	
			DUN_RSVP_MAP.'_email_from_name'   => array(form_input('email_from_name', $email_from_name), 'The name event reminder emails will be sent as. If left blank will use site default.'),	
			DUN_RSVP_MAP.'_email_bcc'   => array(form_input('email_bcc', $email_bcc), 'All notification emails will be copied to this (comma-separated) address list.'),	
			DUN_RSVP_MAP.'_enable_non_member_invites'  => array(form_dropdown('enable_non_member_invites', array('1' => 'yes', '0' => 'no'), $enable_non_member_invites), 'Enable the non member invites'),
			
		);

		//load the view
		return ee()->load->view('settings', $vars, TRUE);   
	}	
	
	// --------------------------------------------------------------------

	/**
	 * Get the event data
	 * 
	 * @todo move to model file
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	function get_event_data()
	{
		// fetch data for the curent event
		$entry_id = (int)ee()->input->get('entry_id');
		if ($entry_id === 0)
		{
			ee()->session->set_flashdata('message_failure', lang('entry_id_invalid'));
			ee()->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=overview');
		}

		$event_data = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
		if (empty($event_data))
		{
			ee()->session->set_flashdata('message_failure', sprintf(lang('entry_id_not_event'), $entry_id));
			ee()->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=overview');
		}

		return $event_data;
	}

	// --------------------------------------------------------------------

	/**
	 * Email the attendees with the modified communicatie method
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	function email_attendees()
	{
		ee()->lang->loadfile('communicate');

		// load details for the specified event
		$data['event'] = $this->get_event_data();
		$data['current_uri'] = RSVP_CP.AMP.'method=email_attendees'.AMP.'entry_id='.$data['event']['entry_id'];
		$data['event_details_link'] = BASE.AMP.RSVP_CP.AMP.'method=view'.AMP.'entry_id='.$data['event']['entry_id'];

	
		// check for a success message
		if (ee()->session->flashdata('message_success'))
		{
			return ee()->load->view('email_attendees_thanks', $data, TRUE);
		}

		// check there are actually members attending the event
		if ($data['event']['total_members_responded'] == 0)
		{
			ee()->session->set_flashdata('message_failure', lang('email_no_attendees'));
			ee()->functions->redirect($data['event_details_link']);
		}

		// get attendance information
		$data['attendance'] = ee()->dun_rsvp_model->get_rsvp_attendance(array(
			'entry_id' => $data['event']['entry_id']))->result_array();
		foreach ($data['attendance'] as $key => $row)
		{
			if($row['member_id'] != 0)
			{
				$data['attendance'][$key]['member_link'] = BASE.AMP.'C=myaccount'.AMP.'id='.$row['member_id'];	
			}
			else
			{
				$data['attendance'][$key]['member_link'] = '#';
			}	
		}

		// configure default email
		$data['mailtype_options'] = array('text' => lang('plain_text'), 'html' => lang('html'));
		$data['word_wrap_options'] = array('y' => lang('on'), 'n' => lang('off'));

		$email = array(
			'from'		 	=> ee()->dun_rsvp_settings->item('rsvp_from_email'),
			'name'			=> ee()->dun_rsvp_settings->item('rsvp_from_name'),
			'cc'			=> '',
			'bcc'			=> '',
			'subject' 		=> $data['event']['title'],
			'message'		=> '',
			'mailtype'		=> ee()->config->item('mail_format'),
			'wordwrap'		=> ee()->config->item('word_wrap')
		);

		if (empty($email['from']))
		{
			$email['from'] = ee()->config->item('webmaster_email');
			$email['name'] = ee()->config->item('webmaster_name');
		}

		// set page title and breadcrumb
		ee()->cp->set_breadcrumb(BASE.AMP.RSVP_CP.AMP.'method=events', lang('rsvp_events'));
		ee()->cp->set_breadcrumb(BASE.AMP.RSVP_CP.AMP.'method=event_details'.AMP.'entry_id='.$data['event']['entry_id'], $data['event']['title']);
		ee()->cp->set_variable('cp_page_title', lang('rsvp_email_attendees'));

		// check for submitted form
		if (ee()->input->post('submit') !== FALSE)
		{
			// get submitted values
			foreach ($email as $key => $value)
			{
				$post_value = ee()->input->post($key, TRUE);
				if ($post_value !== FALSE)
				{
					$email[$key] = $post_value;
				}
			}

			// validate form
			ee()->load->library('form_validation');
			ee()->form_validation->set_rules('subject', 'lang:subject', 'required');
			ee()->form_validation->set_rules('message', 'lang:message', 'required');
			ee()->form_validation->set_rules('from', 'lang:from', 'required|valid_email');
			ee()->form_validation->set_rules('cc', 'lang:cc', 'valid_emails');
			ee()->form_validation->set_rules('bcc', 'lang:bcc', 'valid_emails');
			ee()->form_validation->set_error_delimiters('<br /><strong class="notice">', '</strong><br />');

			if (ee()->form_validation->run() === TRUE)
			{
				// configure email
				ee()->load->library('email');
				ee()->email->wordwrap  = ($email['wordwrap'] == 'y') ? TRUE : FALSE;

				if ($email['mailtype'] == 'html')
				{
					ee()->load->library('typography');
					ee()->typography->initialize();
					$email['message'] = ee()->typography->auto_typography($email['message']);
					ee()->email->mailtype = 'html';
				}
				else
				{
					ee()->email->mailtype = 'text';
				}

				// check for users who don't want to receive admin email
				if (ee()->input->post('accept_admin_email') == 'y')
				{
					foreach ($data['attendance'] as $key => $attendee)
					{
						if ($attendee['accept_admin_email'] != 'y')
						{
							unset($data['attendance'][$key]);
						}
					}
				}

				if (!empty($email['cc']) OR !empty($email['bcc']))
				{
					// send a separate email to cc/bcc
					ee()->email->EE_initialize();
					ee()->email->from($email['from'], $email['name']);
					ee()->email->to('');
					ee()->email->cc($email['cc']);
					ee()->email->bcc($email['bcc']);
					ee()->email->subject($email['subject']);
					ee()->email->message($email['message']);

					if (!ee()->email->send())
					{
						show_error(lang('error_sending_email').BR.BR.implode(BR, ee()->email->_debug_msg));
					}
				}

				foreach ($data['attendance'] as $attendee)
				{
					// email each attendee individually
					ee()->email->EE_initialize();
					ee()->email->from($email['from'], $email['name']);
					ee()->email->to($attendee['email']);
					ee()->email->subject($email['subject']);
					ee()->email->message($email['message']);

					if (!ee()->email->send())
					{
						show_error(lang('error_sending_email').BR.BR.implode(BR, ee()->email->_debug_msg));
					}
				}

				// return success message
				ee()->session->set_flashdata('message_success', lang('event_message_sent'));
				ee()->functions->redirect(BASE.AMP.$data['current_uri']);
			}
		}

		// load view
		$data = array_merge($data, $email);
		$data['attendance'] = array_slice($data['attendance'], 0, 10);
		return ee()->load->view('email_attendees', $data, TRUE);
	}
	
	// --------------------------------------------------------------------

	/**
	 * Export attendance
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	function attendance_export()
	{
		// check the specified event is valid
		$event = $this->get_event_data();

		$attendance = ee()->dun_rsvp_model->get_rsvp_attendance(array(
			'entry_id' => $event['entry_id'],
		));

		ee()->load->dbutil();
		ee()->load->helper('download');
		force_download($event['url_title'].'-attendance-'.date('Ymd').'.csv', ee()->dbutil->csv_from_result($attendance, ';', "\r\n"));
	}	
	
	
	function send_test_rsvp_event()
    {
    	// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->dun_rsvp_settings->item('base_url'), lang(DUN_RSVP_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(DUN_RSVP_MAP.'_send_test_rsvp_event'));
		$vars['cp_page_title'] = lang(DUN_RSVP_MAP.'_send_test_rsvp_event');
		
    	//get the entry_id
        $entry_id = (int)ee()->input->get('entry_id');

		if(isset($_POST['email']) && $_POST['email'] != '')
		{
			//get event
	        $event = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
	       
		   
	        //Get the members
	       /* $member_data = ee()->dun_rsvp_model->get_member(ee()->session->userdata('member_id'));
			$member_data = $member_data->row_array();
			$member_data['email'] = $_POST['email'];*/
			
			
			
			// create a new response
			$response = array(
				'entry_id' => $entry_id,
				'member_id' => 0,
				'email' => $_POST['email'],
				'name' => $_POST['name'],
			);			
			
            ee()->dun_rsvp_lib->send_rsvp_confirmation($event, $response, 'event_invitation'); 
		  	
            ee()->session->set_flashdata('message_success', lang('event_message_sent').' (1 emails)');
            ee()->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=view'.AMP.'entry_id='.$event['entry_id']);       
	        
	        exit;
		}
		else 
		{
			$data = array();
			$data['email_test_event'] = RSVP_CP.AMP.'method=send_test_rsvp_event'.AMP.'entry_id='.$entry_id;
			$data['email_require'] = isset($_POST['submit']) && (!isset($_POST['email']) || $_POST['email']  == '') ? 'U dient een emailadres op te geven' : '';
			return ee()->load->view('send_test_rsvp_event', $data, TRUE);
		} 
        
    }

	// --------------------------------------------------------------------

	/**
	 * Add member for event
	 * 
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	function add_member_for_event()
	{
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->dun_rsvp_settings->item('base_url'), lang(DUN_RSVP_MAP.'_module_name'));
		$this->_set_cp_var('cp_page_title', lang(DUN_RSVP_MAP.'_add_member'));
		$vars['cp_page_title'] = lang(DUN_RSVP_MAP.'_add_member');
		
		//get the entry_id
        $entry_id = (int)ee()->input->get('entry_id');
		
		//get event
	    $event = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();

		if(isset($_POST['member_id']) && $_POST['member_id'] != '' && isset($_POST['seats_reserved']) && $_POST['seats_reserved'] != 0)
		{
			//get member
			$member = ee()->dun_rsvp_model->get_member($_POST['member_id'])->row_array();

			// create a new response
			$data = array(
				'entry_id' => $entry_id,
				'member_id' => $_POST['member_id'],
				'seats_reserved' => $_POST['seats_reserved']
			);
			
			//fill in other fields
			foreach(ee()->dun_rsvp_lib->get_field_fields() as $field)
			{
				$data['fields'][$field] = isset($_POST['fields'][$field])? $_POST['fields'][$field] : '';
			}
			
			// submit RSVP, email confirmation, and refresh page
			ee()->dun_rsvp_model->update_rsvp_response($data);
			ee()->dun_rsvp_lib->send_rsvp_confirmation($event, array_merge($data, array('name' => $member['screen_name'],'email' => $member['email'])), 'attendee_new');

            ee()->session->set_flashdata('message_success', lang('member_added'));
            ee()->functions->redirect(BASE.AMP.RSVP_CP.AMP.'method=view'.AMP.'entry_id='.$event['entry_id']);       
	        
	        exit;
		}
		else 
		{
			//get member data
			$attendee = ee()->dun_rsvp_model->get_entry_response($entry_id)->result_array();
       		$decline = ee()->dun_rsvp_model->get_entry_decline($entry_id)->result_array();
        	$members = ee()->dun_rsvp_model->get_members(array_merge($attendee, $decline));
			$member_data = array();
			if($members->num_rows() > 0)
			{
				foreach($members->result() as $row)
				{
					$member_data[$row->member_id] = $row->screen_name;
				}
			}
			
			$data = array();
			$data['users'] = form_label('Kies een member', 'member_id').'<br/>'.form_dropdown('member_id', $member_data);
			$data['seats_reserved'] = form_label('Kies het aantal plekken', 'seats_reserved').'<br/>'.form_dropdown('seats_reserved', range(0, $event['total_seats_remaining']));
			$data['add_member_for_event_url'] = RSVP_CP.AMP.'method=add_member_for_event'.AMP.'entry_id='.$entry_id;
			
			$data['error'] = isset($_POST['seats_reserved']) && (!isset($_POST['seats_reserved']) || $_POST['seats_reserved']  == 0) ? 'U dient een aantal plekken op te geven' : '';
			
			return ee()->load->view('add_member_for_event', $data, TRUE);
		} 
	}

	/*
	 *	Helper functions
	 */

	function get_pagination_config($base_url, $total_rows, $perpage = 50)
	{
		return array(
			'base_url' => $base_url,
			'total_rows' => $total_rows,
			'per_page' => $perpage,
			'page_query_string' => TRUE,
			'query_string_segment' => 'rownum',
			'full_tag_open' => '<p id="paginationLinks">',
			'full_tag_close' => '</p>',
			'prev_link' => '<img src="'.ee()->cp->cp_theme_url.'images/pagination_prev_button.gif" width="13" height="13" alt="&lt;" />',
			'next_link' => '<img src="'.ee()->cp->cp_theme_url.'images/pagination_next_button.gif" width="13" height="13" alt="&gt;" />',
			'first_link' => '<img src="'.ee()->cp->cp_theme_url.'images/pagination_first_button.gif" width="13" height="13" alt="&lt; &lt;" />',
			'last_link' => '<img src="'.ee()->cp->cp_theme_url.'images/pagination_last_button.gif" width="13" height="13" alt="&gt; &gt;" />'
		);
	}

	// --------------------------------------------------------------------

	/**
	 * Set cp var
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	private function _set_cp_var($key, $val)
	{
		if (version_compare(APP_VER, '2.6.0', '<'))
		{
			ee()->cp->set_variable($key, $val);
		}
		else
		{
			ee()->view->$key = $val;
		}
	}

	// ----------------------------------------------------------------


}
/* End of file mcp.default.php */
/* Location: /system/expressionengine/third_party/default/mcp.default.php */