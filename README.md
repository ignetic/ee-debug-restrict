EE Debug Restrict
=======================

ExpressionEngine 2 Extension

This extension enables restriction of the Display Output Profiler and Display Template Debugging. The was created where admin users other than the developer could see the unwanted debug output. 

This enables restriction by ip address or by member. 

You can restrict it to only display when logged in with an admin session (rather than logged in via the front end).
You can disable the debug output on Ajax requested content.

You may experience problems if 'show_profiler' and 'template_debugging' are in the config.php file. Please remove from the file before using this extension.


This extension uses the following hooks:

sessions_start
