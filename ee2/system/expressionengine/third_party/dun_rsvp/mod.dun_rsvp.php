<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default Module file
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

class Dun_rsvp {
		
	private $EE;
	
	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{		
		//load default helper
		ee()->load->library(DUN_RSVP_MAP.'_lib');

		//require the default settings
		require PATH_THIRD.DUN_RSVP_MAP.'/settings.php';

	}

	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */	
	function form()
	{
		ee()->load->helper('form');

		// display any error messages from a submitted form
		$rsvp_error = ee()->session->flashdata('rsvp_error');
		if (!empty($rsvp_error))
		{
			return '<p>'.lang('rsvp_error').$rsvp_error.'</p>';
		}

		$entry_id = (int)ee()->TMPL->fetch_param('entry_id');
		if ($entry_id === 0)
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_template').'</p>';
		}

		$event_data = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
		if (empty($event_data)) { return; }

		$entry_id = $event_data['entry_id'];

		// is the user logged in?
		$member_id = (int)ee()->session->userdata['member_id'];

		// get any existing RSVP
		$event_rsvp = ee()->dun_rsvp_model->get_rsvp_response($entry_id, $member_id)->row_array();
		if($member_id == 0) $event_rsvp = array();

		// this array will store variables used in the tagdata
		$tag_vars = array();

		// fill the tagdata variables with event info
		foreach (array('total_seats', 'total_seats_reserved', 'total_members_responded', 'total_seats_remaining') as $var)
		{
			$tag_vars[0][$var] = $event_data[$var];
		}

		// check for existing response
		if (empty($event_rsvp))
		{
			$tag_vars[0]['rsvp_seats'] = 0;
			$tag_vars[0]['rsvp_public'] = 'y';
		}
		else
		{
			$tag_vars[0]['rsvp_seats'] = $event_rsvp['seats_reserved'];
			$tag_vars[0]['rsvp_public'] = $event_rsvp['public'];

			// available seats should include any the member has already reserved
			$tag_vars[0]['total_seats_remaining'] += $event_rsvp['seats_reserved'];
		}
		
		//fill in other fields
		$tag_vars[0]['rsvp_fields'] = array();
		foreach(ee()->dun_rsvp_lib->get_field_fields() as $field)
		{
			$tag_vars[0]['rsvp_fields'][] = array(
				'field_name' => 'fields['.$field.']',
				'field_label' => lang($field),
				'field_value' => isset($event_rsvp[$field])? $event_rsvp[$field] : '',
			);
			$tag_vars[0]['rsvp_'.$field] = isset($event_rsvp[$field])? $event_rsvp[$field] : '';
		}

		$hidden_fields = array(
			'entry_id' => $entry_id,
			'return_url' => ee()->TMPL->fetch_param('return', ee()->uri->uri_string),
		);
		
		// start our form output
		$out = ee()->functions->form_declaration(array(
			'action' => ee()->functions->fetch_site_index().QUERY_MARKER.
							'ACT='.ee()->functions->fetch_action_id(DUN_RSVP_MAP, 'update_rsvp_response'),
			'hidden_fields' => $hidden_fields
		));
		
		// default tagdata if nothing is specified
		if (trim(ee()->TMPL->tagdata) === '')
		{
			ee()->TMPL->tagdata = '
				<div class="rsvp_form">
					{if logged_in}
						{if rsvp_seats > 0}
							<p>'.lang('rsvp_already_responded').'</p>
							<p><strong>'.lang('rsvp_edit_response').'</strong></p>
						{if:elseif total_seats > 0 AND total_seats_remaining == 0}
							<p>'.lang('rsvp_sold_out').'</p>
						{if:else}
							<p><strong>'.lang('rsvp_respond').'</strong></p>
							{if total_seats > 0}
								{if total_seats_remaining == 1}
									<p>'.lang('rsvp_hurry_one_seat').'</p>
								{if:elseif total_seats_remaining <= 10}
									<p>'.lang('rsvp_hurry_seats').'</p>
								{/if}
							{/if}
						{/if}
						{if total_seats == 0 OR rsvp_seats > 0 OR total_seats_remaining > 0}
							<label for="rsvp_seats">'.lang('rsvp_seats_required').'</label>
							<select name="rsvp_seats">
								<option value="1" {if rsvp_seats <= 1} selected="selected"{/if}>1</option>
								{if total_seats == 0 OR total_seats_remaining >= 2}<option value="2" {if rsvp_seats == 2} selected="selected"{/if}>2</option>{/if}
								{if total_seats == 0 OR total_seats_remaining >= 3}<option value="3" {if rsvp_seats == 3} selected="selected"{/if}>3</option>{/if}
								{if total_seats == 0 OR total_seats_remaining >= 4}<option value="4" {if rsvp_seats == 4} selected="selected"{/if}>4</option>{/if}
								{if total_seats == 0 OR total_seats_remaining >= 5}<option value="5" {if rsvp_seats == 5} selected="selected"{/if}>5</option>{/if}
							</select><br />

				           	{rsvp_fields}
								<label for="{field_name}">{field_label}</label><br />
								 <input type="text" name="{field_name}" value="{field_value}" /><br />
							{/rsvp_fields}
						   	
							<input name="rsvp_public" type="checkbox" value="y" {if rsvp_public == "y"}checked="checked"{/if} />
							<label for="rsvp_public">'.lang('rsvp_attendance_public').'</label><br />
							{if rsvp_seats > 0}<input type="submit" name="rsvp_cancel" value="'.lang('rsvp_cancel').'" />{/if}
							<input type="submit" name="rsvp_submit" value="{if rsvp_seats > 0}'.lang('rsvp_update').'{if:else}'.lang('rsvp_send').'{/if}" />
						{/if}
					{if:else}
						<p>'.lang('rsvp_please_login').'</p>
					{/if}
				</div>
			';
		}

		// parse tagdata variables
		$out .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $tag_vars);

		// end form output and return
		$out .= '</form>';
		return $out;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Apeldoeners specific
	 */	
	function form_invite_non_member()
	{
		ee()->load->helper('form');

		// display any error messages from a submitted form
		/*$rsvp_error = ee()->session->flashdata('rsvp_error');
		if (!empty($rsvp_error))
		{
			return '<p>'.lang('rsvp_error').$rsvp_error.'</p>';
		}*/

		$entry_id = (int)ee()->TMPL->fetch_param('entry_id');
		if ($entry_id === 0)
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_template').'</p>';
		}

		// is the user logged in?
		$member_id = (int)ee()->session->userdata['member_id'];

		$hidden_fields = array(
			'entry_id' => $entry_id,
			'return_url' => ee()->TMPL->fetch_param('return', ee()->uri->uri_string),
		);
		
		//get the email
		$email = ee()->TMPL->fetch_param('email');
		if ($email == '')
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_email').'</p>';
		}
		$hidden_fields['email'] = $email;
		
		//get the name
		$name = ee()->TMPL->fetch_param('name');
		if ($name == '')
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_name').'</p>';
		}
		$hidden_fields['name'] = $name;
		
		$event_data = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
		if (empty($event_data)) { return; }

		$entry_id = $event_data['entry_id'];
		
		// get any existing RSVP
		$event_rsvp = ee()->dun_rsvp_model->get_rsvp_response($entry_id, $member_id)->row_array();
		if($member_id == 0) $event_rsvp = array();

		// this array will store variables used in the tagdata
		$tag_vars = array();

		// fill the tagdata variables with event info
		foreach (array('total_seats', 'total_seats_reserved', 'total_members_responded', 'total_seats_remaining') as $var)
		{
			$tag_vars[0][$var] = $event_data[$var];
		}

		if(trim(ee()->TMPL->tagdata) === '')
		{
			ee()->TMPL->tagdata = '
				<div class="rsvp_form">
					{if logged_in}
						{if total_seats == 0 OR rsvp_seats > 0 OR total_seats_remaining > 0}
							<textarea name="notes"></textarea>
							<input type="submit" name="rsvp_submit" value="'.lang('rsvp_invite').'" />
						{/if}
					{if:else}
						<p>'.lang('rsvp_please_login').'</p>
					{/if}
				</div>
			';
		}

		// start our form output
		$out = ee()->functions->form_declaration(array(
			'action' => ee()->functions->fetch_site_index().QUERY_MARKER.
							'ACT='.ee()->functions->fetch_action_id(DUN_RSVP_MAP, 'invite_non_member'),
			'hidden_fields' => $hidden_fields
		));

		// parse tagdata variables
		$out .= ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $tag_vars);

		// end form output and return
		$out .= '</form>';
		return $out;
	}

	function attendance()
	{
		$entry_id = (int)ee()->TMPL->fetch_param('entry_id');
		if ($entry_id === 0)
		{
			return '<p>'.lang('rsvp_error').lang('error_invalid_template').'</p>';
		}

		$event_data = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
		if (empty($event_data)) { return; }

		$rsvp_attendance = ee()->dun_rsvp_model->get_rsvp_attendance(array(
			'entry_id' => $entry_id,
			'public' => TRUE,
			'limit' => (int)ee()->TMPL->fetch_param('limit'),
			'offset' => (int)ee()->TMPL->fetch_param('offset'),
			'order_by' => ee()->TMPL->fetch_param('orderby'),
			'sort' => ee()->TMPL->fetch_param('sort'),
		));

		//prefix
		$prefix = ee()->TMPL->fetch_param('prefix', 'attendee').':';

		$tag_vars = array();
		foreach ($rsvp_attendance->result_array() as $key => $response)
		{
			foreach ($response as $field => $value)
			{
				$tag_vars[$key][$prefix.$field] = $value;
			}

			$tag_vars[$key][$prefix.'count'] = $key + 1;
			$tag_vars[$key][$prefix.'total_results'] = $rsvp_attendance->num_rows();
		}

		// default tagdata if nothing is specified
		if (trim(ee()->TMPL->tagdata) === '')
		{
			ee()->TMPL->tagdata = '
				{if no_attendance}
					'.lang('rsvp_no_attendance').'
				{/if}
				{attendee_screen_name}<br />
			';
		}

		// check for an empty result set
		if (empty($tag_vars))
		{
			// based on no_results code in ./system/expressionengine/libraries/Template.php
			if (strpos(ee()->TMPL->tagdata, 'if no_attendance') !== FALSE && preg_match("/".LD."if no_attendance".RD."(.*?)".LD.'\/'."if".RD."/s", ee()->TMPL->tagdata, $match))
			{
				if (stristr($match[1], LD.'if'))
				{
					$match[0] = ee()->functions->full_tag($match[0], ee()->TMPL->tagdata, LD.'if', LD.'\/'."if".RD);
				}

				// return the no_attendance template
				return substr($match[0], strlen(LD."if no_attendance".RD), -strlen(LD.'/'."if".RD));
			}
			else
			{
				// nothing to return
				return;
			}
		}
		else
		{
			// parse the template as normal
			return ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $tag_vars);
		}
	}

	/*public function member_events()
	{
		$member_id = ee()->TMPL->fetch_param('member_id');
		if ($member_id === FALSE OR $member_id == 'CURRENT_USER')
		{
			$member_id = ee()->session->userdata['member_id'];
		}

		if (empty($member_id))
		{
			$entry_ids = '';
		}
		else
		{
			$entry_ids = ee()->rsvp_model->get_member_events($member_id)->result_array();
			foreach ($entry_ids as $key => $row)
			{
				$entry_ids[$key] = $row['entry_id'];
			}
			$entry_ids = implode('|', $entry_ids);
		}

		return str_replace('{entry_ids}', $entry_ids, ee()->TMPL->tagdata);
	}*/

	function invite_non_member()
	{
		//insert 
		ee()->dun_rsvp_model->insert_non_member(ee()->input->post('entry_id'), ee()->input->post('email'), ee()->input->post('name'));
		
		$response = array(
			'entry_id' => ee()->input->post('entry_id'),
			'member_id' => 0,
			'email' => ee()->input->post('email'),
			'name' => ee()->input->post('name'),
			'notes' => ee()->input->post('notes'),
			'invite_name' => ee()->session->userdata['screen_name']
		);
		
		$event = ee()->dun_rsvp_model->get_rsvp_event_by_id(ee()->input->post('entry_id'))->row_array();
		
        ee()->dun_rsvp_lib->send_rsvp_confirmation($event, $response, 'event_invitation_non_member'); 

		ee()->functions->redirect(ee()->input->post('return_url', TRUE));
	}
	
	function is_invited_non_member()
	{
		$entry_id = ee()->TMPL->fetch_param('entry_id', 0);
		$email = ee()->TMPL->fetch_param('email', '-');
		
		$result = ee()->dun_rsvp_model->get_non_member_invite($entry_id, $email);
		return $result->num_rows();
	}
	
	function get_invited_non_member()
	{
		$entry_id = ee()->TMPL->fetch_param('entry_id', 0);
		$email = ee()->TMPL->fetch_param('email', '-');
		$result = ee()->dun_rsvp_model->get_non_member_invite($entry_id, $email);
		
		if($result->num_rows() > 0)
		{
			$result = $result->row_array();
			
			if(isset($result[ee()->TMPL->fetch_param('get_field', 'invited_by')]))
			{
				return $result[ee()->TMPL->fetch_param('get_field', 'invited_by')];
			}
		}
	}
	
	function update_rsvp_response()
	{
		$entry_id = (int)ee()->input->post('entry_id');
		$member_id = (int)ee()->session->userdata['member_id'];
		$event = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();

		$return_url = ee()->functions->create_url(ee()->input->post('return_url', TRUE));

		if ($entry_id === 0 OR $member_id === 0 OR empty($event))
		{
			ee()->functions->redirect($return_url);
		}

		// get any existing RSVP response
		$response = ee()->dun_rsvp_model->get_rsvp_response($entry_id, $member_id)->row_array();

		// create a new response
		$data = array(
			'entry_id' => $entry_id,
			'member_id' => $member_id,
		);

		// validate input data
		$rsvp_seats = (int)ee()->input->post('rsvp_seats');
		if (ee()->input->post('rsvp_cancel') !== FALSE)
		{
			$data['seats_reserved'] = 0;
		}
		elseif ($rsvp_seats > 0)
		{
			$data['seats_reserved'] = $rsvp_seats;
		}
		else
		{
			$data['seats_reserved'] = 1;
		}

		//fill in other fields
		foreach(ee()->dun_rsvp_lib->get_field_fields() as $field)
		{
			$data['fields'][$field] = isset($_POST['fields'][$field])? $_POST['fields'][$field] : '';
		}
		
		
		$data['public'] = ee()->input->post('rsvp_public') === 'y' ? 'y' : 'n';

		// available seats should include any the member has already reserved
		$total_seats_available = $event['total_seats_remaining'];
		if (isset($response['seats_reserved']))
		{
			$total_seats_available += $response['seats_reserved'];
		}

		// check the event is not sold out
		if ($data['seats_reserved'] > 0 AND $event['total_seats'] > 0 AND $total_seats_available < 1)
		{
			ee()->session->set_flashdata('rsvp_error', lang('error_sold_out'));
			ee()->functions->redirect($return_url);
		}
		// check the number of seats available
		elseif ($event['total_seats'] > 0 AND $data['seats_reserved'] > $total_seats_available)
		{
			ee()->session->set_flashdata('rsvp_error', lang('error_insufficient_seats'));
			ee()->functions->redirect($return_url);
		}
		else
		{
			//response exists
			$response_exists = ee()->dun_rsvp_model->get_member_response($entry_id, $member_id);
			
			// submit RSVP, email confirmation, and refresh page
			ee()->dun_rsvp_model->update_rsvp_response($data);
			if($response_exists->num_rows() == 0)
			{
				ee()->dun_rsvp_lib->send_rsvp_confirmation($event, $data, 'attendee_new');
			}
			else
			{
				ee()->dun_rsvp_lib->send_rsvp_confirmation($event, $data, 'attendee_edit');
			}
			ee()->functions->redirect($return_url);
		}
	}

	function decline_rsvp_event()
    {
        $entry_id = (int)ee()->input->get('entry_id');
        $member_id = (int)ee()->input->get('member_id');
        
        if($entry_id != '' && $member_id != '')
        {
            //look if the member already accept the invite
            $decline_check = ee()->dun_rsvp_model->get_rsvp_response($entry_id, $member_id);
			
            //Remove the user form the responses table
            if($decline_check->num_rows() > 0)
            {
               ee()->dun_rsvp_model->remove_rsvp_response($entry_id, $member_id); 
            }
            
            //send message
            $event = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
            ee()->dun_rsvp_lib->send_rsvp_confirmation($event, array('member_id'=>$member_id, 'seats_reserved'=>0), 'dun_rsvp_cancellation');
            
            //add decline row
            ee()->dun_rsvp_model->add_rsvp_decline(ee()->input->get('entry_id'), ee()->input->get('member_id'));
            
            //get event data
            $event = ee()->dun_rsvp_model->get_rsvp_event_by_id(ee()->input->get('entry_id'))->row_array();
            
            $return_url = ee()->functions->remove_double_slashes(ee()->functions->fetch_site_index().ee()->dun_rsvp_settings->item('event_url').'/'.$event['url_title'].'/declined/');
            ee()->functions->redirect($return_url);  
        }
        
        //redirect when nothing can be done
        $return_url = ee()->functions->remove_double_slashes(ee()->functions->fetch_site_index().ee()->dun_rsvp_settings->item('rsvp_event_url'));
        ee()->functions->redirect($return_url); 
    } 

	function get_members()
	{
		ee()->db->select('screen_name, email');
		ee()->db->where_in('group_id', ee()->dun_rsvp_settings->item('member_group'));
		return ee()->db->get('members');
	}
}


/* End of file mod.default.php */
/* Location: /system/expressionengine/third_party/default/mod.default.php */