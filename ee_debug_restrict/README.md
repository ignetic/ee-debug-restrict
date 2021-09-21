EE Debug Restrict
=======================

ExpressionEngine Extension
Compatible with EE2 to EE5

This extension enables restriction of the Display Output Profiler and Display Template Debugging. The was created to solve the problem where admin users other than the developer could see the unwanted debug output. 

This enables restriction of these debug outputs by ip address, url or by member.


Available settings:
=========================

- Enable debugging
------------------------------
Enable the debugging restrictions within this addon. Disabling this uses the default EE debugging settings.

- Restrict debugging by Member
------------------------------------------------------
Only selected members will see the debug output

- Restict debugging to URI
------------------------------------------------------
Only display debugging within the URI's specified
Each URI should be on it's own line. Use % for wildcard. Examples:  
/  
news  
store/category/%  

- Restrict to admin CP Session only?
------------------------------------------------------
You can use this to restrict debug output to when logged in with a CP session only (rather than logged in via the front end).

- Disable debugging in the Control Panel?
---------------------------------------
This will disable the dubug output in in the Control Panel (only front end display)

- Disable debugging on ajax requested content?
--------------------------------------------
You can also conventiently disable the debug output within AJAX requested content, which can otherwise cause problems when testing pages containing AJAX requests.

- Disable debugging with ACT queries?
--------------------------------------------
This will disable the debug output when ACT URL queries are performed

- Show PHP error reporting?
---------------------------
This will enable PHP error reporting to only users of the selected ip address or member



*You may experience problems if 'show_profiler' and 'template_debugging' exist within the config.php file, in which case remove these settings from the file before using this extension.*

This extension uses the following hooks:

sessions_start