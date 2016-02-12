EE Debug Restrict
=======================

ExpressionEngine 2 Extension

This extension enables restriction of the Display Output Profiler and Display Template Debugging. The was created yo solve the problem where admin users other than the developer could see the unwanted debug output. 

This enables restriction of these debug outputs by ip address or by member.


Other available settings:
=========================

- Select which output to display
------------------------------
Opt to display either "Display Output Profiler?" or "Display Template Debugging?" or both.

- Restrict to only when logged in with CP admin session?
------------------------------------------------------
You can use this to restrict debug output to when logged in with a CP session only (rather than logged in via the front end).

- Disable debugging in the Control Panel?
---------------------------------------
This will disable the dubug output in in the Control Panel (only front end display)

- Disable debugging on ajax requested content?
--------------------------------------------
You can also conventiently disable the debug output within AJAX requested content, which can otherwise cause problems when testing pages containing AJAX requests.

- Enable PHP error reporting?
---------------------------
This will enable PHP error reporting by the selected ip address or by member



*You may experience problems if 'show_profiler' and 'template_debugging' are in the config.php file, in which case remove these settings from the file before using this extension.*

This extension uses the following hooks:

sessions_start