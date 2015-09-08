<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default library helper
 *
 * @package		Module name
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once(PATH_THIRD.'dun_rsvp/config.php');

/**
 * Include helper
 */
require_once(PATH_THIRD.'dun_rsvp/libraries/dun_rsvp_helper.php');

class Dun_rsvp_lib
{
	private $default_settings;
	private $settings;
	private $EE;

	//debug array
	public $debug = array();

	public function __construct()
	{			
		//fix for the template
		ee()->load->library('template');
		ee()->TMPL = isset(ee()->TMPL) ? ee()->TMPL : new EE_Template();

		//load lang
		ee()->lang->loadfile(DUN_RSVP_MAP);

		//load model
		ee()->load->model(DUN_RSVP_MAP.'_model');

		//load the channel data
		ee()->load->driver('channel_data');

		//load the settings
		ee()->load->library(DUN_RSVP_MAP.'_settings');

		//load logger
		ee()->load->library('logger');
	
		//require the default settings
		require PATH_THIRD.DUN_RSVP_MAP.'/settings.php';
	}

	// ----------------------------------------------------------------------
	// CUSTOM FUNCTIONS
	// ----------------------------------------------------------------------
	function build_export_select($channel_id = 1) 
	{
		$where_array = array('channel_data.channel_id' => $channel_id);
		$fields	 = ee()->channel_data->get_channel_fields(1)->result();
			
		//$fields  = ee()->channel_data->get_fields()->result();
		$select = ee()->channel_data->build_select($fields, 'channel_titles.', 'channel_data.');

		return array_slice($select, 8);
	}
	
	function get_field_fields() 
	{
		$fields = ee()->db->list_fields(DUN_RSVP_MAP.'_fields');
		$return = array();
		foreach($fields as $key=>$field)
		{
			if($field != 'field_id' && $field != 'response_id')
			{
				$return[] = $field;
			}
		}
		return $return;
	}

	function send_rsvp_confirmation($event, $response, $type = '')
    {
        //event email data
        if($type == 'event_invitation')
        {
            $event['invite_url'] = reduce_double_slashes(ee()->functions->fetch_site_index().ee()->dun_rsvp_settings->item('event_url').'/'.$event['url_title'].'/invite/');
			$event['decline_url'] = ee()->functions->fetch_site_index().QUERY_MARKER.'ACT='.$this->fetch_action_id('dun_rsvp', 'decline_rsvp_event').AMP.'entry_id='.$event['entry_id'].AMP.'member_id='.$response['member_id'];
        }
        else if($type == 'attendee_new' || $type == 'attendee_edit')
        {
             $event['calendar_url'] = ee()->functions->fetch_site_index().QUERY_MARKER.'ACT='.$this->fetch_action_id('dun_rsvp', 'vcard_event').AMP.'entry_id='.$event['entry_id'];
        }

        /* -------------------------------------------
        /* 'rsvp_email_start' hook.
        /*  - Added: 1.3
        */
        if (ee()->extensions->active_hook('rsvp_email_start') === TRUE)
        {
            ee()->extensions->call('rsvp_email_start', $event, $response, $type);
            if (ee()->extensions->end_script === TRUE) return;
        }
        // -------------------------------------------

        ee()->load->library(array('email', 'template'));
        ee()->load->helper('text');

        ee()->email->wordwrap = true;

        //event email
        if($type == 'event_invitation')
        {
            $template = ee()->functions->fetch_email_template('dun_rsvp_invitation');
        }

		//invite non member
		else if($type == 'event_invitation_non_member')
		{
			$template = ee()->functions->fetch_email_template('dun_rsvp_invitation_non_member');
		}

        //attendee email
        else
        {
            //cancellation
            if ($response['seats_reserved'] === 0)
            {
                $template = ee()->functions->fetch_email_template('dun_rsvp_cancellation');
            }

			//update
			else if($type == 'attendee_edit')
			{
				$template = ee()->functions->fetch_email_template('dun_rsvp_update');
			}

			//new registration
            else
            {
                $template = ee()->functions->fetch_email_template('dun_rsvp_confirmation');
            }
        }

        if (empty($template['title']) OR empty($template['data'])) { return; }

        $vars = array(
            'site_name' => stripslashes(ee()->config->item('site_name')),
            'site_url'  => ee()->config->item('site_url'),
            'name'      => isset($response['name']) ? $response['name'] : ee()->session->userdata('screen_name'),
            'email'      => isset($response['email']) ? $response['email'] : ee()->session->userdata('email'),
        );

        //event email data
        if($type == 'event_invitation')
        {
            $vars['name'] = $response['name'];
            $vars['invite_url'] = $event['invite_url'];
            $vars['decline_url'] = $event['decline_url'];
        }

		//fill in other fields
		$vars['fields'] = array();
		foreach(ee()->dun_rsvp_lib->get_field_fields() as $field)
		{
			$vars[$field] = isset($response[$field])  ? $response[$field] : (isset($response['fields'][$field])? $response['fields'][$field] : '');

			if($vars[$field] != '')
			{
				$vars['fields'][] = array(
						'label' => ucfirst(str_replace('_',  ' ', $field)),
						'value' => $vars[$field]
				);
			}

			$vars['rsvp_'.$field] = isset($vars[$field])? $vars[$field] : '';
		}

		//get the entry
		$entry = ee()->channel_data->get_entry($event['entry_id']);

        $vars = array(array_merge($entry->row_array(), $event, $response, $vars, array('type' => $type)));

        $email_title = ee()->template->parse_variables($template['title'], $vars);
        $email_body = ee()->template->parse_variables($template['data'], $vars);

        // sender address defaults to site webmaster email
        if (ee()->dun_rsvp_settings->item('email_from_address') == '')
        {
            $from_email = ee()->config->item('webmaster_email');
			$from_name = ee()->config->item('webmaster_name');
        }
        else
        {
			$from_email = ee()->dun_rsvp_settings->item('email_from_address');
			$from_name = ee()->dun_rsvp_settings->item('email_from_name');
        }

        // send message
        $email = isset($response['email']) ? $response['email'] : ee()->session->userdata('email');
        //($type == 'event_invitation' ? $response['email'] : ee()->session->userdata['email']);

		// send message to the person
        ee()->email->to($email);
		ee()->email->from($from_email, $from_name);
        ee()->email->subject(entities_to_ascii($email_title));
        ee()->email->message(entities_to_ascii($email_body));
		ee()->email->mailtype = ee()->dun_rsvp_settings->item('mailtype');
        ee()->email->send();

		// send a copy
		if (ee()->dun_rsvp_settings->item('email_copy'))
		{
			ee()->email->to(ee()->dun_rsvp_settings->item('email_copy'));
			ee()->email->from($from_email, $from_name);
			ee()->email->subject('[KOPIE] ' . entities_to_ascii($email_title));
			ee()->email->message(entities_to_ascii($email_body));
			ee()->email->mailtype = ee()->dun_rsvp_settings->item('mailtype');
			ee()->email->send();
		}
        
        /* -------------------------------------------
        /* 'rsvp_email_end' hook.
        /*  - Added: 1.3
        */
        if (ee()->extensions->active_hook('rsvp_email_end') === TRUE)
        {
            ee()->extensions->call('rsvp_email_end', $event, $response, $type);
        }
        // -------------------------------------------
    }

	// -------------------------------------------

	function filter_custom_fields($attendance = array(), $fields = array())
	{
		$new_fields = array();

		//loop over the fiels
		if(!empty($fields))
		{
			foreach($fields as $field)
			{
				//set the var, that indicate if the all of the custom field is empty
				$empty_records = 0;

				//loop over the attendees
				foreach($attendance as $attendee)
				{
					if($attendee[$field] == '')
					{
						$empty_records++;
					}
				}

				//are all fields empty?
				//unset those fields from the attendee list
				if($empty_records == count($attendance))
				{
					//remove the values from the array
					foreach($attendance as $key=>$val)
					{
						unset($attendance[$key][$field]);
					}
				}

				//set new fields
				else
				{
					$new_fields[] = $field;
				}
			}

			//set the fields
			$fields = $new_fields;
		}

		return array('attendance' => $attendance, 'fields' => $fields);
	}


	// -------------------------------------------

	function fetch_action_id($class = '', $method)
    {
        ee()->db->select('action_id');
        ee()->db->where('class', $class);
        ee()->db->where('method', $method);
        $query = ee()->db->get('actions');
        
        if ($query->num_rows() == 0)
        {
            return FALSE;
        }
        
        return $query->row('action_id');
    }  
	
	
	// ----------------------------------------------------------------------
	// PRIVATE FUNCTIONS
	// ----------------------------------------------------------------------
	
	// ----------------------------------------------------------------------
	// DEFAULT FUNCTIONS
	// ----------------------------------------------------------------------

	// --------------------------------------------------------------------
        
    /**
     * Hook - allows each method to check for relevant hooks
     */
    public function activate_hook($hook='', $data=array())
    {
        if ($hook AND ee()->extensions->active_hook(DUN_RSVP_MAP.'_'.$hook) === TRUE)
        {
                $data = ee()->extensions->call(DUN_RSVP_MAP.'_'.$hook, $data);
                if (ee()->extensions->end_script === TRUE) return;
        }
        
        return $data;
    }

	// ----------------------------------------------------------------------

	/**
	 * Log all messages
	 *
	 * @param array $logs The debug messages.
	 * @return void
	 */
	public function expose_log()
	{
		if(!empty($this->debug))
		{
			foreach ($this->debug as $log)
			{
				ee()->TMPL->log_item('&nbsp;&nbsp;***&nbsp;&nbsp;'.DUN_RSVP_CLASS.' debug: ' . $log);
			}
		}
	} 
		
	// ----------------------------------------------------------------------
	 
	
	
} // END CLASS

/* End of file default_library.php  */
/* Location: ./system/expressionengine/third_party/default/libraries/default_library.php */