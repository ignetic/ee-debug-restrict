<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$lang = array(
	'ip_filter'	=> "Restrict debugging by IP Address <br>
		<span style='font-weight:normal'>Only users with the entered IP addresses will see the debug output</span><br>
		<em>Each IP Filter should be on it's own line</em>",
	'uri_filter'	=> "Disable debugging by URI <br>
		<span style='font-weight:normal'>Disable debugging within the URI's specified</span><br>
		<em>Each URI should be on it's own line</em>",
	'member_filter'	=> "Restrict debugging by Member<br>
		<span style='font-weight:normal'>Only selected members will see the debug output</span><br>
		<em>Combined with the above IP address restriction</em>",
	'output'	=> "Select which output to display<br>
		<em>Ensure to remove show_profiler and template_debugging from the config.php file when using this extension.</em>",
	'admin_sess'	=> "Restrict to admin CP Session only?<br>
		<em>Enable this to restrict debug output to when logged in with a CP session only</em>",
	'disable_in_cp'	=> "Disable debugging in the Control Panel?<br>
		<em>This will disable the dubug output in in the Control Panel</em>",
	'disable_ajax'	=> "Disable debugging on ajax requested content?<br>
		<em>This will disable the debug output within AJAX requested content and from displaying within CP</em>",
	'disable_act'	=> "Disable debugging with ACT queries?<br>
		<em>This will disable the debug output when ACT queries are performed</em>",
	'error_reporting'	=> "Show PHP error reporting?<br>
		<em>Displays PHP error reporting to only users of the selected ip address or member</em>",
	'strict_error_reporting'	=> "Use strict error reporting?<br>
		<em>Show E_STRICT and E_DEPRECATED in error reporting</em>",
	'hide_php7_warnings'	=> "Hide PHP 7 warnings?<br>
		<em>Hide 'Declaration of...' warnings in PHP 7</em>",
	
	'display_output_profiler' => 'Display Output Profiler?',
	'display_template_debugging' => 'Display Template Debugging?',
);

/* End of file ee_debug_restrict_lang.php */
/* Location: /system/expressionengine/third_party/ee_debug_restrict/language/english/ee_debug_restrict_lang.php */
