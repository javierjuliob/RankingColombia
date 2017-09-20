=== Wordpress Tabs ===
Contributors: Diomenas,leogermani,rodrigosprimo 
Tags: post, page, tabs, content, section, subsection, tab
Requires at least: 2.1
Tested up to: 4.7.3
Stable tag: 4.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wordpress Tabs allows you to easily split your post/page content into multiple "Tabbed" sections. 

== Description ==

Wordpress Tabs allows you to easily split your post/page content into multiple "Tabbed" sections.  This plugin was adapted from [PostTabs](http://wordpress.org/extend/plugins/posttabs/ "PostTabs") which had gone dormant in 2011.  The adaptation was to permit both Horizontal and Vertical tabs as well as to expand the style functions.

=Features:=
* The color and style of the tabs can be modified easily to fit with any theme
* Tabs can be displayed either Horizontally (On top of Content) or Vertically (To the Left or Right of Content)

This plugin has been tested on Firefox, IE6, iE7, Opera, Safari and Konqueror.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/gt-tabs` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Wordpress Tabs screen to configure the plugin

== Usage ==

Go to Settings->Wordpress Tabs (or Options->Wordpress Tabs) to adjust the colors of your tabs.

Edit your post and page and add the code below wherever you want to insert the tab:

[tab:Tab Title]

All the content bellow this code will be inserted inside this tab, untill another tab is declared with:

[tab:Another Tab]

And so on.. untill the end of the page or optionally you can add the code bellow to end the last tab and add more text outside the tabs:

[tab:END]

You can also have text before the first tab. Just type it as normal text...

== Screenshots ==

1. An example of the text on the editor window and the live result

2. The admin options page where you can set the colors with a colorpick and have a instant preview
	
== Change Log ==
=4.0.3=
* Fixed issue causing Options page to fail when rendering
* Removed legacy GT-Tabs setup image header from Admin Page
* Changed verbiage of the Update Settings button on admin to "Save"
* Fixed Example on Admin page to output proper code syntax
* Fixed typos on the Admin Page

=4.0.2=
* Fixed version number inconsistency and renamed plugin to "Wordpress Tabs"

=4.0.1=
* Updating verbiage in readme and installation instructions
* Preparing plugin for 4.x overhaul

=4.0=
* Updating Plugin to add CSS for printing
* Updating tested with version
* De-stagnating the plugin development

=3.1=
* Added Font Size Selection to Admin menu
* Added ability to control vertical or horizontal spacing
* Fixed two coding issues creating miscellaneous code validation errors

3.0.1=
* Fixed bug causing the Layout Height to incorrectly set as the Layout Width
* Added general plugin usage to the Admin page
* Corrected bug causing the Vertical Tab height to be incorrectly spaced which caused overlapping tabs.

=3.0=
* migrated abanoned project to "Wordpress Tabs" as version 3.0.  Previous versions of postTabs remain untouched.
* Added Vertical or Horizontal Tab functionality

=2.9=
* Now uses jquery on front end
* Displays everything if javascript is not present


=2.7=
* Fixed bugs on permalink support
* Added Table of Contents and Navigation options
* Improved RSS feed appearence


=2.5=
* Even better cross-theme CSS compatibility
* fixed - now xhtml compliant (tks to ovidiu)
* fixed - path to javascript works with wordpress installed on subdirectory (tks to ovidiu)
* fixed appearence on RSS feeds and other situations where the post is presented outside the context (i.e. wp-print plugin). Now it hides the unordered list and displays a title at the top of each tab content (tks to JK)

New Features

* Choose the tabs alignment
* When page reloads it remembers wich tab was opened
* You can choose wether tabs links will only show-hide tabs or will point to a individual permalink for each tab
* add option to display the tab permalink as post metadata information.

=2.0=
* refactored css stylesheet for better cross-browser cross-themes compatibility 
* now you can change also the line color
* improved admin interface with color picker and preview

=1.0=
Released first version! Full functional in all browsers!


=0.1 beta=
Released first version, with known issues on styles...
