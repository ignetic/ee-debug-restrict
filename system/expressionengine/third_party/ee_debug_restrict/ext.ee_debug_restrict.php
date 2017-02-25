<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * EE Debug Restrict Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Simon Andersohn
 * @link		
 */

class Ee_debug_restrict_ext
{
	
	public $description		= 'Restricts output debugging to specified IP address or member';
	public $name			= 'EE Debug Restrict';
	public $docs_url		= '';
	public $version			= '1.6';
	public $settings_exist	= 'y';
	
	private $default_settings = array(
		'ip_filter' => '127.0.0.*',
		'member_filter' => array(),
		'uri_filter' => '',
		'output' => array('show_profiler', 'template_debugging'),
		'admin_sess' => 'n',
		'error_reporting' => 'n',
		'error_reporting_level' => 'all',
		'strict_error_reporting' => 'n',
		'hide_php7_warnings' => 'n',
		'disable_in_cp' => 'y',
		'disable_ajax' => 'y',
		'disable_act' => 'y',
	);
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings='')
	{
		$this->settings = $settings;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 */
	public function settings()
	{
		
		$members = array();
		$results = ee()->db->select('member_id, username')->where('group_id', 1)->get('members');
		foreach($results->result_array() as $row)
		{
			$members[$row['member_id']] = $row['username'];
		}
		
		$error_reporting_level = array(
			'all' => 'E_ALL',
			'strict' => 'E_ALL | E_STRICT',
			'warning' => 'E_ALL & ~E_WARNING',
			'deprecated' => 'E_ALL & ~E_DEPRECATED',
			'warning_deprecated' => 'E_ALL & ~E_WARNING & ~E_DEPRECATED'
		);
				
		return array(
			'ip_filter'  => array('t', array('rows' => '4'), $this->default_settings['ip_filter']),
			'member_filter'  => array('ms', $members, $this->default_settings['member_filter']),
			'uri_filter'  => array('t', array('rows' => '4'), $this->default_settings['uri_filter']),
			'output'  => array('c', array('show_profiler' => 'display_output_profiler', 'template_debugging' => 'display_template_debugging'), $this->default_settings['output']),
			'admin_sess'  => array('r', array('y' => "yes", 'n' => "no"), $this->default_settings['admin_sess']),
			'disable_in_cp'  => array('r', array('y' => "yes", 'n' => "no"), $this->default_settings['disable_in_cp']),
			'disable_ajax'  => array('r', array('y' => "yes", 'n' => "no"), $this->default_settings['disable_ajax']),
			'disable_act'  => array('r', array('y' => "yes", 'n' => "no"), $this->default_settings['disable_act']),
			'error_reporting'  => array('s', array('y' => "yes", 'n' => "no"), $this->default_settings['error_reporting']),
			'error_reporting_level'  => array('s', $error_reporting_level, $this->default_settings['error_reporting_level']),
			//'strict_error_reporting'  => array('r', array('y' => "yes", 'n' => "no"), $this->default_settings['strict_error_reporting']),
			'hide_php7_warnings'  => array('s', array('y' => "yes", 'n' => "no"), $this->default_settings['hide_php7_warnings']),
		);
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array(
			'ip_filter'	=> $default_settings['ip_filter'],
		);
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'sessions_start',
			'hook'		=> 'sessions_start',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		ee()->db->insert('extensions', $data);			
		
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * sessions_start
	 *
	 * @param 
	 * @return 
	 */
	public function sessions_start()
	{
		
		// Ignore this on the actual Output and Debugging settings page
		if (ee()->uri->uri_string() == 'cp/admin_system/output_debugging_preferences')
		{
			return;
		}
	
		// Session class variables not initiated at this point, so lets grab them from the cookie
		$sessionid = ee()->input->cookie('sessionid', TRUE);
		
		// Not logged in
		if (!$sessionid)
			return;
		
		// Get member details
		$results = ee()->db->select('members.member_id, admin_sess, group_id')
							->from('members')
							->join('sessions', 'sessions.member_id = members.member_id')
							->where(array('sessions.session_id' => $sessionid, 'group_id' => 1))
							->limit(1)
							->get();

		if ($results->num_rows() > 0)
		{
			foreach($results->result_array() as $row)
			{
				$member_data = $row;
			}
		} else {
			// Not a Super Admin?
			return;
		}

		// We are admin, so let's continue...
		$settings = array_merge($this->default_settings, $this->settings);
		
		// Only display if logged in as admin
		if ($settings['admin_sess'] == 'y' && $member_data['admin_sess'] != 1)
		{
			return;
		}
		
		// Do nothing if nothing is selected
		if ( empty($settings['ip_filter']) && empty($settings['member_filter']) )
		{
			return;
		}
		
		$ip_found = FALSE;
		$member_found = FALSE;
		
		// Restrict by member
		if (in_array($member_data['member_id'], $settings['member_filter']))
		{
			$member_found = TRUE;
		}

		// Restrict by IP address
		$ips = explode("\n", $settings['ip_filter']);
		foreach($ips as $ip)
		{
			// Allow for comments with # after ip address
			$pos = strrpos($ip,'#');
			if ($pos !== FALSE)
			{
				$ip = substr($ip,0,$pos);
			}

			$ip = trim($ip);

			// Valid IP?
			$temp = str_replace('*', '0', $ip);
			if ($this->validateIpAddress($temp) == FALSE)
			{
				continue;
			}

			$ipregex = preg_replace("/\./", "\.", $ip);
			$ipregex = preg_replace("/\*/", ".*", $ipregex);

			if( preg_match('/'.$ipregex.'/', ee()->input->ip_address()) )
			{
				$ip_found = TRUE;
				break;
			}
			else
			{
				continue;
			}

		}

		// Initially switch off outputs
		ee()->config->set_item('show_profiler', 'n');
		ee()->config->set_item('template_debugging', 'n');
		
		
		// Don't display in admin
		//if ($settings['disable_in_cp'] != 'y' && ee()->uri->segment(1) == 'cp')
		ee()->load->helper('url');
		if ($settings['disable_in_cp'] == 'y' && site_url().basename($_SERVER['SCRIPT_NAME']) == ee()->config->item('cp_url'))
		{
			return;
		}
		
		// Check the requirements
		$enable = FALSE;
		if (!empty($settings['ip_filter']) && !empty($settings['member_filter']) && $ip_found === TRUE && $member_found === TRUE)
		{
			$enable = TRUE;
		}
		elseif ((empty($settings['member_filter']) && $ip_found === TRUE) || (empty($settings['ip_filter']) && $member_found === TRUE))
		{
			$enable = TRUE;
		}
		
		if ($enable == TRUE)
		{
			// Disable with ACT queries
			if ($settings['disable_act'] == 'y')
			{
				$act = ee()->input->get('ACT', TRUE);
				if ($act !== FALSE)
				{
					return;
				}
			}

			// Restrict by URI
			$uris = explode("\n", $settings['uri_filter']);
			foreach($uris as $uri)
			{
				$uri = trim($uri);
				$current_uri = trim(ee()->uri->uri_string(), '/');
				if ($current_uri == '' && $uri == '/')
				{
					// is home page
					return;
				}
				$uri = trim($uri, '/');
				if (!empty($uri) && strpos($current_uri, $uri) === 0)
				{
					// is uri
					return;
				}
			}
			
			
			// Turn on debugging outputs
			if ($settings['disable_ajax'] == 'y')
			{
				if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'))
				{
					$this->enable_debug_output($settings['output']);
				}
			}
			else
			{
				$this->enable_debug_output($settings['output']);
			}

			// Turn on error reporting?
			if ($settings['error_reporting'] == 'y')
			{
			
				switch ($settings['error_reporting_level']) {
					case 'strict':
						error_reporting(E_ALL | E_STRICT);
						break;
					case 'warning':
						error_reporting(E_ALL & ~E_WARNING);
						break;
					case 'deprecated':
						error_reporting(E_ALL & ~E_DEPRECATED);
						break;
					case 'warning_deprecated':
						error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED);
						break;
					default:
						error_reporting(E_ALL);
				}

				if ($settings['hide_php7_warnings'] == 'y')
				{
					if (PHP_MAJOR_VERSION >= 7) {
						set_error_handler(function ($errno, $errstr) {
						   return strpos($errstr, 'Declaration of') === 0;
						}, E_WARNING);
					}
				}
				
				@ini_set('display_errors', 1);
				ee()->db->db_debug = TRUE;
				
			}
			
		}
	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		ee()->db->where('class', __CLASS__);
		ee()->db->update(
					'extensions',
					array('version' => $this->version)
		);
	}	
	
	
	// ----------------------------------------------------------------------
	
	function enable_debug_output($output=array())
	{
		if (in_array('show_profiler', $output)) {
			ee()->config->set_item('show_profiler', 'y');
			ee()->output->enable_profiler(TRUE);
//			ee()->db->db_debug = TRUE;
		}
		if (in_array('template_debugging', $output)) {
			ee()->config->set_item('template_debugging', 'y');
		}
	}
	
	
	// ----------------------------------------------------------------------
	
	function validateIpAddress($ip_addr)
	{
	  //first of all the format of the ip address is matched
	  if(preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/",$ip_addr))
	  {
	    //now all the intger values are separated
	    $parts=explode(".",$ip_addr);
	    //now we need to check each part can range from 0-255
	    foreach($parts as $ip_parts)
	    {
	      if(intval($ip_parts)>255 || intval($ip_parts)<0)
	      return false; //if number is not within range of 0-255
	    }
	    return true;
	  }
	  else
	    return false; //if format of ip address doesn't matches
	}
	
}

/* End of file ext.ee_debug_restrict.php */
/* Location: /system/expressionengine/third_party/ee_debug_restrict/ext.ee_debug_restrict.php */