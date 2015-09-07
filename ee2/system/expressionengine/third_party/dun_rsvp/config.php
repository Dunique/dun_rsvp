<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default config
 *
 * @package		Default module
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

//contants
if ( ! defined('DUN_RSVP_NAME'))
{
	define('DUN_RSVP_NAME', 'RSVP Plus');
	define('DUN_RSVP_CLASS', 'Dun_rsvp');
	define('DUN_RSVP_MAP', 'dun_rsvp');
	define('DUN_RSVP_VERSION', '1.0.2');
	define('DUN_RSVP_DESCRIPTION', 'De BCA module');
	define('DUN_RSVP_DOCS', '');
	define('DUN_RSVP_DEVOTEE', '');
	define('DUN_RSVP_AUTHOR', 'Rein de Vries');
	define('DUN_RSVP_DEBUG', true);
	define('DUN_RSVP_STATS_URL', 'http://reinos.nl/index.php/module_stats_api/v1'); 
}

//configs
$config['name'] = DUN_RSVP_NAME;
$config['version'] = DUN_RSVP_VERSION;

//load compat file
require_once(PATH_THIRD.DUN_RSVP_MAP.'/compat.php');

/* End of file config.php */
/* Location: /system/expressionengine/third_party/default/config.php */