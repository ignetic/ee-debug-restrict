<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Debug Restrict Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		Simon Andersohn
 * @link		
 */

class Ee_debug_restrict_ext {
	
	public $settings 		= array();
	public $description		= 'Restricts template debugging and profiler to specified IP address';
	public $docs_url		= '';
	public $name			= 'EE Debug Restrict';
	public $settings_exist	= 'y';
	public $version			= '1.0';
	
	private $default_settings = array(
		'ip_filter' => '',
		'member_filter' => array(),
		'output' => array(),
		'admin_sess' => '',
		'disable_ajax' => '',
	);
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		$this->settings = $settings;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 *
	 * If you wish for ExpressionEngine to automatically create your settings
	 * page, work in this method.  If you wish to have fine-grained control
	 * over your form, use the settings_form() and save_settings() methods 
	 * instead, and delete this one.
	 *
	 * @see http://expressionengine.com/user_guide/development/extensions.html#settings
	 */
	public function settings()
	{
		
		$members = array();
		$results = ee()->db->select('member_id, username')->where('group_id', 1)->get('members');
		foreach($results->result_array() as $row)
		{
			$members[$row['member_id']] = $row['username'];
		}
		
		return array(
			'ip_filter'  => array('t', array('rows' => '4'), ''),
			'member_filter'  => array('ms', $members, ''),
			'output'  => array('c', array('show_profiler' => "Display Output Profiler?", 'template_debugging' => "Display Template Debugging?"), array('show_profiler', 'template_debugging')),
			'admin_sess'  => array('r', array('y' => "Yes", 'n' => "No"), 'n'),
			'disable_ajax'  => array('r', array('y' => "Yes", 'n' => "No"), 'y')
		);
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array(
			'ip_filter'	=> "127.0.0.*\n192.168.*.*",
		);
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'sessions_start',
			'hook'		=> 'sessions_start',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		$this->EE->db->insert('extensions', $data);			
		
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * sessions_start
	 *
	 * @param 
	 * @return 
	 */
	public function sessions_start(&$data)
	{
		
		// Session class variables not initiated at this point, so lets grab them from the cookie
		$sessionid  = ee()->input->cookie('sessionid', TRUE);
		
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
		
		$ip_found = FALSE;
		$member_found = FALSE;
		
		// Initially switch off outputs
		$this->EE->config->set_item('show_profiler', 'n');
		$this->EE->config->set_item('template_debugging', 'n');

		// Do nothing if nothing is selected
		if ( empty($settings['ip_filter']) && empty($settings['member_filter']) )
		{
			return;
		}
	
		// Only display if logged in as admin
		if ($settings['admin_sess'] == 'y' && $member_data['admin_sess'] != 1)
		{
			return;
		}
		
		
		// Restrict by member
		if (in_array($member_data['member_id'], $settings['member_filter']))
		{
			$member_found = TRUE;
		}

		// Restrict by IP address
		$ips = explode("\n", $settings['ip_filter']);
		
		foreach($ips as $ip)
		{
			$ip = trim($ip);

			// Valid IP?
			$temp = str_replace('*', '0', $ip);
			if ($this->validateIpAddress($temp) == FALSE)
			{
				continue;
			}

			$ipregex = preg_replace("/\./", "\.", $ip);
			$ipregex = preg_replace("/\*/", ".*", $ipregex);

			if( preg_match('/'.$ipregex.'/', $this->EE->input->ip_address()) )
			{
				$ip_found = TRUE;
				break;
			}
			else
			{
				continue;
			}

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
		
	
		// Turn on outputs
		if ($enable == TRUE)
		{
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
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
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
	}	
	
	// ----------------------------------------------------------------------
	
	function enable_debug_output($output=array())
	{
		if (in_array('show_profiler', $output)) $this->EE->config->set_item('show_profiler', 'y');
		if (in_array('template_debugging', $output)) $this->EE->config->set_item('template_debugging', 'y');
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
/* Location: /system/expressionengine/third_party/ee_debug_restrictdebug_restrict/ext.debug_restrict.php */