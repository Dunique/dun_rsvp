<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

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

// requires EE_Form_validation class
ee()->load->library('form_validation');

class Dun_rsvp_form_validation extends EE_Form_validation
{
	public function __construct($rules = array())
	{
		parent::__construct($rules);
		$this->CI =& get_instance();
		$this->EE =& $this->CI;

		// overwrite EE form validation library
		$this->EE->form_validation =& $this;
	}

	public function error_array()
	{
		return $this->_error_array;
	}

	/**
	 * Awesome function to manually add an error to the form
	 */
	public function add_error($field, $message)
	{
		// make sure we have data for this field
		if (empty($this->_field_data[$field]))
		{
			$this->set_rules($field, "lang:$field", '');
		}

		$this->_field_data[$field]['error'] = $message;
		$this->_error_array[$field] = $message;
	}

	/**
	 * Add validation rules instead of overwriting them
	 */
	public function add_rules($field, $label = '', $rules = '')
	{
		// are there any existing rules for this field?
		if ( ! empty($this->_field_data[$field]['rules']))
		{
			$rules = trim($this->_field_data[$field]['rules'].'|'.$rules, '|');
		}

		$this->set_rules($field, $label, $rules);
	}

	// ----------------------------------------------------------------------

    /**
     * Check max size of an array for the form validation
     *
     * @param array $logs The debug messages.
     * @return void
     */
    public function max_entries($str, $count = 1)
    {
    	$entries = ee()->input->post('entries');
    	
        if (count($entries) > $count)
        {
          ee()->form_validation->set_message('max_entries', 'The %s field has a max of '.$count.' values');
          return FALSE;
        }
        else
        {
          return TRUE;
        }
    }

	
}

/* End of file ./libraries/.php */