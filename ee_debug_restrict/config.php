<?php

if ( ! defined('EE_DEBUG_RESTRICT_NAME'))
{
	define('EE_DEBUG_RESTRICT_NAME',		'EE Debug Restrict');
	define('EE_DEBUG_RESTRICT_DESCRIPTION',	'Restricts output debugging to specified IP address or member');
	define('EE_DEBUG_RESTRICT_VERSION',		'1.8');
	define('EE_DEBUG_RESTRICT_AUTHOR_URL',	'https://github.com/ignetic'); 
	define('EE_DEBUG_RESTRICT_DOCS_URL',	'https://github.com/ignetic/ee-debug-restrict'); 
}

$config['name']    = EE_DEBUG_RESTRICT_NAME;
$config['version'] = EE_DEBUG_RESTRICT_VERSION; 
 
