<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default Tab
 *
 * @package		Default module
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/add-ons
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'dun_rsvp/config.php';
 
class Dun_rsvp_tab {
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load default helper
		ee()->load->library(DUN_RSVP_MAP.'_lib');

		//load lnag
		ee()->lang->loadfile(DUN_RSVP_MAP);	
		
		//require the settings
		require PATH_THIRD.DUN_RSVP_MAP.'/settings.php';
	}

	// ----------------------------------------------------------------

	function publish_tabs($channel_id, $entry_id = '')
	{
		//do we not handle this channel?
		if($channel_id != ee()->dun_rsvp_settings->item('channel'))
		{
			return array();	
		}
		
        // default values
        $rsvp_enabled = ee()->dun_rsvp_settings->item('def_status') == 'Open' ? 'y' : 'n';
        $rsvp_total_seats = '';

        // check for existing values
        if ($entry_id)
        {
            $event = ee()->dun_rsvp_model->get_rsvp_event_by_id($entry_id)->row_array();
            if (empty($event))
            {
                $rsvp_enabled = 'n';
            }
            else
            {
                $rsvp_enabled = 'y';
                $rsvp_total_seats = $event['total_seats'] == 0 ? '' : $event['total_seats'];
            }
        }

        // configure tabs
        $tabs = array(
            array(
                'field_id'      => DUN_RSVP_MAP.'_enabled',
                'field_label'     => lang(DUN_RSVP_MAP.'_enabled'),
                'field_required'    => 'n',
                'field_type'      => 'select',
                'field_data'      => $rsvp_enabled,
                'field_list_items'    => array('y' => 'Enabled', 'n' => 'Disabled'),
                'field_instructions'  => '',
                'field_pre_populate'  => 'n',
                'field_pre_field_id'  => '',
                'field_pre_channel_id'  => '',
                'field_text_direction'  => 'ltr'
            ),

            array(
                'field_id'      =>  DUN_RSVP_MAP.'_total_seats',
                'field_label'     => lang(DUN_RSVP_MAP.'_total_seats'),
                'field_type'      => 'text',
                'field_data'      =>  $rsvp_total_seats,
                'field_required'    => 'n',
                'field_instructions'  => lang(DUN_RSVP_MAP.'_total_seats_instructions'),
                'field_text_direction'  => 'ltr',
                'field_maxl'      => '10'
            )
        );

        return $tabs;
	}

	function validate_publish($params)
    {
        return TRUE;
    }

    /**
     * Save the data to the db
     *
     * @param  $params
     * @return void
     */
    function publish_data_db($params)
    {
        if (isset($params['mod_data'][DUN_RSVP_MAP.'_enabled']))
        {
            if ($params['mod_data'][DUN_RSVP_MAP.'_enabled'] == 'y')
            {
                // enable event
               ee()->dun_rsvp_model->update_rsvp_event(array(
                    'entry_id' => $params['entry_id'],
                    'total_seats' => (int)$params['mod_data'][DUN_RSVP_MAP.'_total_seats']
                ));
            }
            else
            {
                // disable event
                ee()->dun_rsvp_model->remove_rsvp_event($params['entry_id']);
            }
        }

    }

    /**
     * Delete data if entry is deleted
     *
     * @param  $params
     * @return void
     */
    function publish_data_delete_db($params)
    {
        // remove event and all responses
        ee()->dun_rsvp_model->remove_rsvp_event($params['entry_ids'], TRUE);
    }

    // ----------------------------------------------------------------

	function default_tab()
	{
		$settings[] = array(
			'field_id'				=> '',
			'field_label'			=> '',
			'field_required' 		=> 'n',
			'field_data'			=> '',
			'field_list_items'		=> '',
			'field_fmt'				=> '',
			'field_instructions' 	=> '',
			'field_show_fmt'		=> 'n',
			'field_fmt_options'		=> array(),
			'field_pre_populate'	=> 'n',
			'field_text_direction'	=> 'ltr',
			'field_type' 			=> 'text',
			'field_maxl'			=> '1'
		);
			
		return $settings;
	}
	
	
}
/* End of file upd.structure_url_alias.php */
/* Location: /system/expressionengine/third_party/structure_url_alias/upd.structure_url_alias.php */