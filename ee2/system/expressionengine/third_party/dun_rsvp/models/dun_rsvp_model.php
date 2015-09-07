<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default Model
 *
 * @package		Default name
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'dun_rsvp/config.php';

class Dun_rsvp_model
{

	private $EE;

	public function __construct()
	{							
		// Creat EE Instance
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Get all custom fields
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_custom_fields()
	{
		$fields = ee()->db->list_fields(DUN_RSVP_MAP.'_fields');

		$return = array();

		foreach ($fields as $field)
		{
			if($field != 'response_id' && $field != 'field_id')
			{
				$return[] = $field;
			}
		}

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Insert non member data
	 *
	 * @access	public
	 * @return	array
	 */
	public function insert_non_member($entry_id, $email, $name)
	{
		//first delete
		ee()->db->where('entry_id', $entry_id);
		ee()->db->where('email', $email);
		ee()->db->where('name', $name);
		ee()->db->where('member_id', (int)ee()->session->userdata['member_id']);
		ee()->db->delete(DUN_RSVP_MAP.'_invite_non_members');
		
		//then insert 
		ee()->db->insert(DUN_RSVP_MAP.'_invite_non_members', array(
			'entry_id' => $entry_id,
			'member_id' => (int)ee()->session->userdata['member_id'],
			'email' => $email,
			'name' => $name
		));
	}
	
	// --------------------------------------------------------------------

	/**
	 * Delete non member data
	 *
	 * @access	public
	 * @return	array
	 */
	public function delete_non_member_invite($invite_id)
	{
		//first delete
		ee()->db->where('invite_non_member_id', $invite_id);
		ee()->db->delete(DUN_RSVP_MAP.'_invite_non_members');
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * Get al member_groups
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_member_groups()
	{
		ee()->db->select('group_id, group_title');
		$q = ee()->db->get('member_groups');

		$member_groups = array();

		if($q->num_rows() > 0)
		{
			foreach($q->result() as $member_group)
			{
				$member_groups[$member_group->group_id] = $member_group->group_title;
			}
		}

		return $member_groups;
	}

	// --------------------------------------------------------------------

	/**
	 * Get al channels
	 *
	 * @access	public
	 * @return	array
	 */
	public function get_channels()
	{
		ee()->db->select('channel_name, channel_id');
		$q = ee()->db->get('channels');

		$channels = array();

		if($q->num_rows() > 0)
		{
			foreach($q->result() as $channel)
			{
				$channels[$channel->channel_id] = $channel->channel_name;
			}
		}

		return $channels;
	}

	// --------------------------------------------------------------------

	/**
	 * Get event by ID
	 *
	 * @access	public
	 * @return	array
	 */
	function get_rsvp_event_by_id($entry_id)
	{
		return ee()->db->query('
			select events.*, titles.*,
				coalesce(sum(responses.seats_reserved),0) as total_seats_reserved,
				events.total_seats - coalesce(sum(responses.seats_reserved),0) as total_seats_remaining,
				count(responses.member_id) as total_members_responded
			from '.ee()->db->protect_identifiers(DUN_RSVP_MAP.'_events', TRUE).' as events
			join '.ee()->db->protect_identifiers('channel_titles', TRUE).' as titles on titles.entry_id = events.entry_id
			left join '.ee()->db->protect_identifiers(DUN_RSVP_MAP.'_responses', TRUE).' as responses on responses.entry_id = events.entry_id
			where events.entry_id = ?
				and titles.channel_id in('.ee()->dun_rsvp_settings->item('channel').')
				and titles.site_id = '.(int)ee()->dun_rsvp_settings->item('site_id').'
			group by events.entry_id', $entry_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Get event by ID
	 *
	 * @access	public
	 * @return	array
	 */
	function total_declines($entry_id = 0)
	{
		return ee()->db->select('entry_id')->where('entry_id', $entry_id)->from('dun_rsvp_declines')->get()->num_rows();
	}
	// --------------------------------------------------------------------

	/**
	 * Get Responde
	 *
	 * @access	public
	 * @return	array
	 */
	function get_rsvp_response($entry_id, $member_id)
	{
		ee()->db->where('entry_id', $entry_id);
		ee()->db->where('member_id', $member_id);
		ee()->db->join(DUN_RSVP_MAP.'_fields', DUN_RSVP_MAP.'_fields.response_id = '.DUN_RSVP_MAP.'_responses.response_id');	
		return ee()->db->get(DUN_RSVP_MAP.'_responses');
	}

	// --------------------------------------------------------------------

	/**
	 * Update an event
	 *
	 * @access	public
	 * @return	array
	 */
	function update_rsvp_event($data)
	{
		ee()->db->where('entry_id', $data['entry_id']);
		$query = ee()->db->get(DUN_RSVP_MAP.'_events');

		if ($query->num_rows() > 0)
		{
			// update existing entry
			ee()->db->where('entry_id', $data['entry_id']);
			ee()->db->update(DUN_RSVP_MAP.'_events', $data);
		}
		else
		{
			// insert new entry
			ee()->db->insert(DUN_RSVP_MAP.'_events', $data);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Remove an event
	 *
	 * @access	public
	 * @return	array
	 */
	function remove_rsvp_event($entry_ids, $remove_responses = FALSE)
	{
		ee()->db->where_in('entry_id', $entry_ids);
		ee()->db->delete(DUN_RSVP_MAP.'_events');

		if ($remove_responses === TRUE)
		{
			ee()->db->where_in('entry_id', $entry_ids);
			ee()->db->delete(DUN_RSVP_MAP.'_responses');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Remove an event
	 *
	 * @access	public
	 * @return	array
	 */
	function get_rsvp_attendance($options)
	{
		$entry_select = ee()->dun_rsvp_lib->build_export_select(ee()->dun_rsvp_settings->item('channel'));

		// prevent array errors
		foreach (array('select', 'entry_id', 'public', 'limit', 'offset', 'order_by', 'sort') as $field)
		{
			if ( ! isset($options[$field])) $options[$field] = FALSE;
		}

		//set the select including the entr
		$options['select'] = (empty($options['select']) ? '*' : $options['select'].', '.implode(',', $entry_select));
		
		//@todo for now override the select
		$options['select'] = DUN_RSVP_MAP.'_responses.*, '.DUN_RSVP_MAP.'_fields.*, members.email, members.screen_name, '.DUN_RSVP_MAP.'_responses.response_id as response_id';
		
		ee()->db->select($options['select'])
			->where(DUN_RSVP_MAP.'_responses.entry_id', $options['entry_id'])
			->from(DUN_RSVP_MAP.'_responses')
			->join('members', 'members.member_id = '.DUN_RSVP_MAP.'_responses.member_id')
			->join(DUN_RSVP_MAP.'_fields', DUN_RSVP_MAP.'_fields.response_id = '.DUN_RSVP_MAP.'_responses.response_id', 'left');
	
		// add limit and offset
		if ($options['limit'])
		{
			$options['limit'] = (int)$options['limit'];
			$options['offset'] = (int)$options['offset'];
			ee()->db->limit($options['limit'], $options['offset']);
		}

		// default order_by
		if (in_array($options['order_by'], array('member_id', 'group_id', 'username', 'screen_name', 'email')))
		{
			$options['order_by'] = 'members.'.$options['order_by'];
		}
		else
		{
			// in docs this is referred to as 'date', in reality it is a catch all
			$options['order_by'] = DUN_RSVP_MAP.'_responses.updated';
		}

		// default sort
		if (empty($options['sort']) AND $options['order_by'] == DUN_RSVP_MAP.'_responses.updated')
		{
			$options['sort'] = 'desc';
		}
		$options['sort'] = $options['sort'] == 'desc' ? 'desc' : 'asc';

		//$result = $this->db->order_by($options['order_by'], $options['sort'])->get();


		return ee()->db->order_by($options['order_by'], $options['sort'])->get();
			
			
		/*	
		// prevent array errors
		foreach (array('select', 'entry_id', 'public', 'limit', 'offset', 'order_by', 'sort') as $field)
		{
			if ( ! isset($options[$field])) $options[$field] = FALSE;
		}

		ee()->db->select(empty($options['select']) ? '*' : $options['select'])
			->where('entry_id', $options['entry_id']);

		if ($options['public']) $this->db->where(DUN_RSVP_MAP.'_responses.public', 'y');

		ee()->db->from(DUN_RSVP_MAP.'_responses')
			->join('members', 'members.member_id = '.DUN_RSVP_MAP.'_responses.member_id')
			->join('member_data', 'member_data.member_id = '.DUN_RSVP_MAP.'_responses.member_id');

		// add limit and offset
		if ($options['limit'])
		{
			$options['limit'] = (int)$options['limit'];
			$options['offset'] = (int)$options['offset'];
			ee()->db->limit($options['limit'], $options['offset']);
		}

		// default order_by
		if (in_array($options['order_by'], array('member_id', 'group_id', 'username', 'screen_name', 'email')))
		{
			$options['order_by'] = 'members.'.$options['order_by'];
		}
		else
		{
			// in docs this is referred to as 'date', in reality it is a catch all
			$options['order_by'] = DUN_RSVP_MAP.'_responses.updated';
		}

		// default sort
		if (empty($options['sort']) AND $options['order_by'] == DUN_RSVP_MAP.'_responses.updated')
		{
			$options['sort'] = 'desc';
		}
		$options['sort'] = $options['sort'] == 'desc' ? 'desc' : 'asc';

		return ee()->db->order_by($options['order_by'], $options['sort'])->get();*/
	}

	// --------------------------------------------------------------------

	/**
	 * Cout all itemst
	 *
	 * @access	public
	 * @return	array
	 */
	public function count_items()
	{
		ee()->db->from(DUN_RSVP_MAP.'_events');
		ee()->db->join('channel_titles', 'channel_titles.entry_id = '.DUN_RSVP_MAP.'_events.entry_id');
		ee()->db->where_in('channel_id', ee()->dun_rsvp_settings->item('channel'));
		ee()->db->where('site_id', (int)ee()->config->item('site_id'));
		return ee()->db->count_all_results();
	}

	// --------------------------------------------------------------------

	/**
	 * Get all aliases
	 *
	 * @access	public
	 * @return	void
	 */
	public function get_all_items($entry_id = '', $start = 0, $limit = false, $order = array())
	{
		$results = array();
		$q = '';

		//get all alias for an specific site_id
		if($entry_id == '')
		{
			ee()->db->select('events.*, titles.*,
				coalesce(sum(responses.seats_reserved),0) as total_seats_reserved,
				events.total_seats - coalesce(sum(responses.seats_reserved),0) as total_seats_remaining,
				count(responses.member_id) as total_members_responded', FALSE);
			ee()->db->from(DUN_RSVP_MAP.'_events as events');
			ee()->db->join('channel_titles as titles', 'titles.entry_id = events.entry_id');
			ee()->db->join(DUN_RSVP_MAP.'_responses as responses', 'responses.entry_id = events.entry_id', 'left');
			ee()->db->where_in('titles.channel_id', ee()->dun_rsvp_settings->item('channel'));
			ee()->db->where('titles.site_id', ee()->config->item('site_id'));
			ee()->db->group_by('events.entry_id');
		}

		//Fetch a list of entries in array
		else if(is_array($entry_id) && !empty($entry_id))
		{
			ee()->db->select('events.*, titles.*,
				coalesce(sum(responses.seats_reserved),0) as total_seats_reserved,
				events.total_seats - coalesce(sum(responses.seats_reserved),0) as total_seats_remaining,
				count(responses.member_id) as total_members_responded', FALSE);
			ee()->db->from(DUN_RSVP_MAP.'_events as events');
			ee()->db->join('channel_titles as titles', 'titles.entry_id = events.entry_id');
			ee()->db->join(DUN_RSVP_MAP.'_responses as responses', 'responses.entry_id = events.entry_id', 'left');
			ee()->db->where_in('titles.channel_id', ee()->dun_rsvp_settings->item('channel'));
			ee()->db->where('titles.site_id', ee()->config->item('site_id'));
			ee()->db->group_by('events.entry_id');
			ee()->db->where_in('events.entry_id', $entry_id);
		}

		//fetch only the alias for an entry_id
		else if(!is_array($entry_id))
		{
			ee()->db->select('events.*, titles.*,
				coalesce(sum(responses.seats_reserved),0) as total_seats_reserved,
				events.total_seats - coalesce(sum(responses.seats_reserved),0) as total_seats_remaining,
				count(responses.member_id) as total_members_responded', FALSE);
			ee()->db->from(DUN_RSVP_MAP.'_events as events');
			ee()->db->join('channel_titles as titles', 'titles.entry_id = events.entry_id');
			ee()->db->join(DUN_RSVP_MAP.'_responses as responses', 'responses.entry_id = events.entry_id', 'left');
			ee()->db->where_in('titles.channel_id', ee()->dun_rsvp_settings->item('channel'));
			ee()->db->where('titles.site_id', ee()->config->item('site_id'));
			ee()->db->group_by('events.entry_id');
			ee()->db->where('events.entry_id', $entry_id);
		}

		//do nothing
		else
		{
			return array();
		}

		//is there a start and limit
		if($limit !== false)
		{
			ee()->db->limit($start, $limit);
		}

		//do we need to order
		//given by the mcp table method http://ellislab.com/expressionengine/user-guide/development/usage/table.html
		if(!empty($order))
		{
			if(isset($order[DUN_RSVP_MAP.'_entry_id']))
			{
				ee()->db->order_by('events.entry_id', $order[DUN_RSVP_MAP.'_entry_id']);
			}

			if(isset($order[DUN_RSVP_MAP.'_title']))
			{
				ee()->db->order_by('titles.entry_id', $order[DUN_RSVP_MAP.'_title']);
			}
		}
		
		//get the result
		$q = ee()->db->get();

		//format result
		if($q != '' && $q->num_rows())
		{
			foreach($q->result() as $val)
			{
				$results[] = $val;
			}
		}

		return $results;
	}

	function update_rsvp_response($data)
	{
		// entry_id and member_id are required
		if (!isset($data['entry_id']) OR !isset($data['member_id'])) return;

		// remove any existing response first
		if($data['member_id'] != 0) $this->remove_rsvp_response($data['entry_id'], $data['member_id']);

		// normalise data
		if (!isset($data['seats_reserved'])) { $data['seats_reserved'] = 1; }
		if (isset($data['public']) AND ($data['public'] === FALSE OR strtolower($data['public']) == 'n'))
		{
			$data['public'] = 'n';
		}
		else
		{
			$data['public'] = 'y';
		}

		$data['updated'] = ee()->localize->now;

		// check we are not making an empty reservation
		if ($data['seats_reserved'] < 1) return;
		
		//get the extra fields
		$fields = $data['fields'];
		unset($data['fields']);

		//insert the data
		ee()->db->insert(DUN_RSVP_MAP.'_responses', $data);
		
		//insert the extra fields
		$fields['response_id'] = ee()->db->insert_id();
		ee()->db->insert(DUN_RSVP_MAP.'_fields', $fields);
	}

	function remove_rsvp_response($entry_id, $member_id)
	{
		//get response
		ee()->db->select('response_id');
        ee()->db->where('entry_id', (int)$entry_id);
		ee()->db->where('member_id', $member_id);
        $query = ee()->db->get(DUN_RSVP_MAP.'_responses');
		
		if($query->num_rows() > 0)
		{
			$result = $query->row_array();
			//delete response
			ee()->db->where('response_id', $result['response_id']);
			ee()->db->delete(DUN_RSVP_MAP.'_responses');
			
			//delete fields
			ee()->db->where('response_id', $result['response_id']);
			ee()->db->delete(DUN_RSVP_MAP.'_fields');
		}
	}
	
	function email_exists($entry_id, $email)
	{
		ee()->db->where('entry_id', $entry_id);
		ee()->db->where('email', $email);
		$result = ee()->db->get(DUN_RSVP_MAP.'_responses');
		
		if($result->num_rows() > 0)
		{
			return true;
		}
		return false;
	}
	
	function get_member_events($member_id)
	{
		ee()->db->select('entry_id');
		ee()->db->where('member_id', (int)$member_id);
		 return ee()->db->get(DUN_RSVP_MAP.'_responses');
	}
	
	function get_member_response($entry_id = 0, $member_id = 0)
    {
    	ee()->db->where('entry_id', (int)$entry_id);
        ee()->db->where('member_id', (int)$member_id);
        return ee()->db->get(DUN_RSVP_MAP.'_responses');
    }
	
	function get_entry_response($entry_id)
    {
        ee()->db->select('member_id');
        ee()->db->where('entry_id', (int)$entry_id);
        return ee()->db->get(DUN_RSVP_MAP.'_responses');
    }
	
	function get_entry_decline($entry_id)
    {
        ee()->db->select('member_id');
        ee()->db->where('entry_id', (int)$entry_id);
        return ee()->db->get(DUN_RSVP_MAP.'_declines');
    }
	
	function get_members($remove = array())
    {
        if(ee()->dun_rsvp_settings->item('member_group') != '')
        {
            //create not in()
            if(!empty($remove))
            {
                
                $not_in = array();
                
                foreach($remove as $val)
                {
                    $not_in[] = $val['member_id'];
                }
                
                ee()->db->where_not_in('member_id', $not_in);  
            }

            ee()->db->where_in('group_id', ee()->dun_rsvp_settings->item('member_group'));
           
            return ee()->db->get('members');
        }
    
        return false;
    }
	
	function get_member($member_id = 0)
    {
        ee()->db->where('member_id', $member_id);
        return ee()->db->get('members');
	}
	
	function add_rsvp_decline($entry_id, $member_id)
    {
        $row = $this->get_entry_decline($entry_id);

        if($row->num_rows() == 0)
        {
           ee()->db->insert(DUN_RSVP_MAP.'_declines', array(
                'entry_id' => $entry_id,
                'member_id' => $member_id
            ));
        }        
    }
	
	function get_non_member_invite($entry_id = 0, $email = '')
    {
    	ee()->db->where(DUN_RSVP_MAP.'_invite_non_members.entry_id', $entry_id);
		if($email != '')
		{
			ee()->db->where(DUN_RSVP_MAP.'_invite_non_members.email', $email);
		}
		ee()->db->select(DUN_RSVP_MAP.'_invite_non_members.*, members.screen_name as invited_by');
		ee()->db->join('members', 'members.member_id = '.DUN_RSVP_MAP.'_invite_non_members.member_id');
	
		$return = ee()->db->get(DUN_RSVP_MAP.'_invite_non_members');

		return $return;
    }
	
	function count_non_member_invite($entry_id = 0)
    {
    	$query = $this->get_non_member_invite($entry_id);
		return $query->num_rows();
    }
	
} // END CLASS

/* End of file default_model.php  */
/* Location: ./system/expressionengine/third_party/default/models/default_model.php */