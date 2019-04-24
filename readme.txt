=== Child Themes Helper ===
Contributors: paulswarthout
Author URI: http://www.paulswarthout.com/child-themes-helper
Donate link: https://paypal.me/PaulSwarthout
Tags: child themes helper, child themes, child theme, child, theme, template theme, parent theme, developers, IIS, Linux, copy files to child theme, create a child theme
Requires at least: 4.7.0
Tested up to: 5.1.1
Stable tag: 2.1.2
Requires PHP: 5.6.31
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copies parent theme files to a child theme while maintaining the correct folder/subfolder structure in the child theme as in the parent theme, and more....

== Description ==
1. **The Child Themes Helper is a tool....**
	...developed for those child theme developers who write or modify PHP code in the development of their child themes. Previous versions of the Child Themes Helper required the child theme being modified be the activated theme. That is no longer the case. However, you will still need to set a child theme to be an "Active Theme" on the Options tab, but it does not have to be the activated theme.

1. **Copy files from Parent Theme to Child Theme**
	The primary purpose of the Child Themes Helper plugin is to copy files from a parent theme (also called the template theme) to a child theme. The folder path in the parent theme is duplicated in the child theme during the copy.

1. **Edit Child Theme Files**
	Starting with the Child Themes Helper version 2.1, you can now edit your child theme files and save the changes. You can also "edit" your parent theme files, but they are marked read-only and you will not be able to save any changes that you make.
 This is not meant to be the primary method of editing your files, but rather a way to make a quick change or to peer inside of a file without having to go elsewhere to make a quick change.

1. **Remove files from the Child Theme**
	The Child Themes Helper plugin will also remove any files that you no longer want in the child theme. Any folders that are made empty by the removal of a file or folder, will also be removed.

1. **Prompt before removal**
	The Child Themes Helper plugin will detect when a child theme file is different from its parent theme counterpart. If the files are not identical, the user will be prompted before allowing a parent theme file to be copied over an existing child theme file, or before allowing a child theme file to be removed.

1. **Create a child theme**
	The primary functionality of the Child Themes Helper plugin requires the existence of a child theme. If a child theme has not already been created, this plugin will help you to create a child theme of any of the currently installed themes (not other child themes) on the website.

1. **Generate a temporary graphic for the Themes page**
	Creating a child theme does not create a graphic for your new theme on the WordPress themes page. The Child Themes Helper plugin can create a graphic for your child theme. You're free to select the foreground and background colors for that graphic and choose from up to a couple of dozen Google Fonts. If you would like a different font, you only need to copy the .ttf file into the Child Themes Helper plugin's assets/fonts folder. The next time you open the Options page, the newly downloaded font will be displayed with a sample string.

1. **Notes**
	- *PHP Versions*
		The Child Themes Helper plugin has been tested and found to work with PHP 5.6.31, PHP 7.2.7, and PHP 7.3.0.

	- *Troubleshooting Installation Issues*
		The primary filename and primary folder name of the Child Themes Helper changed (in v2.1) from pasChildThemes and pasChildThemes.php to child-themes-helper and child-themes-helper.php. This *may* cause some problems during the installation. If you experience problems, I suggest that you deactivate the plugin and delete it. Then reinstall the Child Themes Helper v2.1 from the WordPress Plugins repository.

	- *What would you like to see in this plugin?*

	- *Screenshot*
		The temporary graphic is referred to as the ScreenShot because the filename is "screenshot.png" and is located in the root folder of your theme. The filename and the location are defined by the WordPress core and cannot be changed by this (or any) plugin.

		Your browser will cache the screenshot file whenever possible. If you modify the ScreenShot graphic and you do not see any changes when you generate a new one, then you will need to clear your browser's image and file cache.
		
		If you generate a screenshot graphic and you only see the background (i.e., no words), just generate the screenshot again. This happens when the selected font does not exist in the assets/fonts folder of the Child Themes Helper plugin. If you are updating from version 1.0, you will see this happen the first time that you generate a screenshot since the original fonts were deleted and replaced by Google Fonts.

		Most developers will replace this generated screenshot file with a graphic of their own. This feature is meant to provide a *temporary* graphic that quickly and easily identifies the child theme name and its parent theme.

		In a future release, there will be a lock feature on the options page to prevent accidental overwrites of the screenshot file. Also, in a future release, there will be the ability to select an existing graphic and crop it as necessary, instead of generating one.

	- *Child Themes Helper access*
		The Child Themes Helper is accessed from the WordPress dashboard under the heading "Child Themes Helper". The menu item may be found immediately below the *Appearance* Dashboard menu item.

	- *Platform Support*
		The Child Themes Helper plugin has been tested with both WordPress running on a Windows server and with WordPress running on a Linux server. Development is done on a Windows platform. If you find any compatibility issues with Linux, please let me know.
	
	- *Child Themes Helper on GitHub*
		The GitHub repository for this plugin can be found [here](https://github.com/PaulSwarthout/pasChildThemes). Stable versions are usually found on the WordPress SVN repository. Intermediate versions are often found on GitHub.

	- *Development versions*
		Versions 1\.2, 1\.1\.3 and 1\.0 are available for download and install.

	- *Known Bug*
		Although the Child Themes Helper plugin is mostly responsive, the Edit File functionality doesn't work very well on small screens. But does anybody actually modify themes on smartphones and tablets? (Please say 'No').


== Installation ==

- This plugin may be installed through the usual method of installing and activating WordPress plugins. The first time you open the Child Themes Helper plugin page on your dashboard, it will look a bit different from the previous version. Instead of a stack of menu options on the dashboard menu, there is a single menu option "Child Themes Helper" and it opens to a page featuring tabs across the top -- one for each area of functionality. Previous versions worked on the currently active Child Theme, but effective with this release, you are free to modify any child theme that has been created. You will still need to specify the "active" theme, but it does not have to be the "activated" theme. The first time you use it, you will only be able to set an active theme on the Options tab, or create a new child theme.

- If you experience problems installing or activating version 2.1, after having an earlier version installed, please deactivate it, delete it, and then reinstall it. The primary folder name and the primary plugin filename changed with this release. It may conflict with an earlier version and crash upon install.

- The Child Themes Helper plugin requires an active theme be specified (Options tab). Unlike previous versions, this "active theme" does NOT have to be the currently "Activated" theme.

- *If you downloaded the Child Themes Helper plugin, directly, from the [WordPress Plugin Page](https://wordpress.org/plugins/child-themes-helper/)*, then follow these instructions to install.
	1. If you have already tried this plugin and you are trying a different version through this installation method, I highly recommend that you deactivate the plugin and delete it before following the next steps. There are multiple files that existed in earlier versions that do not exist or are no longer used in later versions. If you don't want to delete it, first, then at least deactivate it.
	1. Using your favorite FTP client, find the wp-content/plugins folder and create the child-themes-helper sub folder.
	1. Unzip the downloaded file and copy the contents to the wp-content/plugins/child-themes-helper folder on your web server. Overwrite anything that previously existed.
	1. Activate the Child Themes Helper plugin.
	1. You're ready to go. You should see the menu option "Child Themes Helper" on your dashboard menu just below the Appearance menu item.

== Frequently Asked Questions ==

= What is next? =

I am glad you asked.

- A pro version that encompasses even more features like:
	- The ability to copy multiple files and/or folders at once, instead of a single file at a time.
	- The ability to make a modified non-child theme into a child theme of itself.
	- Some kind of feature to make stylesheets more manageable. Something to help the relative newcomers understand what they're changing and why, and more importantly, what to change.

= Where I can see the Child Themes Helper in action? =

I am glad you asked. Starting with version 1.2 (version 2.1 is not yet installed on that page), you can visit [my demo page](http://www.1acsi.com) and take it for a test drive. Create your own child theme. Copy files to the newly created child theme. Generate screenshots. Change the Screenshot options. In short, put it through its paces. And don't worry about screwing up the website. It's there for that purpose.

= I generated a screenshot but it didn't change. Why not? =

You did nothing wrong. Your browser will cache the screenshot file to help your website to load more quickly. The solution is to clear your browser's image cache. [Here's a good article](https://kb.iu.edu/d/ahic) that I found that gives great directions on how to accomplish that.

= What does the "Generate ScreenShot" option do? =

The WordPress Themes page displays a graphic for each theme. A newly created child theme does not have a graphic. The Generate ScreenShot menu option creates a temporary graphic. Generally, developers will use a copy of a header image for their screenshot. But rather than leaving it blank until later, the Child Themes Helper plugin will create a temporary graphic which displays the child theme name, the parent theme name, a message indicating that the child theme was created using the Child Themes Helper and the Child Themes Helper's developer's web address. It is expected that the developer will want to replace this temporary graphic with their own custom graphic at a later time. Please check out [my demo page](http://www.1acsi.com) where you are welcome to take the Child Themes Helper plugin for a test drive.

= Why create a screenshot? I'm just going to delete it later anyway. =

I hate the fact that nothing appears as a graphic in a newly created child theme. It just bugs me. So I thought I'd do something about it. But most of all, it was a learning experience. I got to learn about CSS grids, and the GD graphics library, and handling fonts.

== Screenshots ==

1. The files for the Child Theme are displayed in the left-hand pane. The name, 'MyChildTheme' for example
is the name of your Child Theme. The page scrolls.
2. The files for the Template / Parent Theme's, or the Child Theme's parent theme, are displayed in the right-hand pane.
For this example, the Child Theme was created as a child of the 'Twenty Sixteen' theme and it's name appears at the top. The page scrolls.

== Changelog ==

= 2.1.2 =
 - Added "wait" cursors in the places where there is a delay in processing ... i.e., it's not immediate.
 
 - Modified the AJAX calls to be consistent.

= 2.1.1 =
 - WordPress Plugin review team discovered a security vulnerability in version 2.0 of the Child Themes Helper plugin.
   The bug has been fixed. All of the code has been reviewed.
   Upon review, I discovered the vulnerability existed in version 1.3.2 also, so that version will no longer be available for download either.

 - Added a tab that explains the basics of this plugin. Click "Expert Mode" checkbox to hide all of the explanations.

= 2.0 =
- Tested with WP 5.1.1.

- The "active theme" is no longer required to be the activated theme. In other words, the Child Themes Helper will work with any child/parent theme, not just the one activated on the Themes menu.

- Cleaned up the interface. I didn't like the way it worked. It was kind of clunky. Now you only need to right click (or long press with a mobile device) on a file. A popup menu will display with the options available.
 No more floating menu as you move around the screen. Yay!

- Eliminated the stack of options on the dashboard menu. Now there are tabs, one for each area of functionality. The contents of each tab scrolls up underneath the tabs.

- Fixed a problem with the Edit File responsiveness. I'm not thrilled with how I fixed it, but it works.

= 1.3.2 =
- Tested with WP 5.0.3.

- Added the ability to edit files in the child theme or the parent theme. You have the ability to save any changes that you make to the child theme, but the parent theme files are read-only. You can, of course, click on the parent theme file to copy it to the child theme, and then right click on the file to edit it, make your changes, and save those changes in the child theme file.

- Fixed a bug where you could create a child theme whose name didn't start with a letter, and then crash it when you tried to set it active..

- Fixed a bug where files whose names started with an underscore couldn't be copied. The problem is caused by the WordPress core function sanitize_file_name() which strips (trims) periods, dashes, and underscores from the beginning of a filename. The solution was to create a new function to sanitize file names that didn't strip the underscores. I kept the functionality that strips leading periods and dashes. I tripped on this bug when testing a newly created child theme of TwentyNineteen, which has filenames that begin with an underscore.

= 1.2 =
- Tested with WP 5.0 RC1.

= 1.2 =
- Updated the stylesheet to make the plugin responsive. This plugin should work nicely on whatever device you want to use it with. I even tested it with an old Samsung S II that I had laying around. Aside from some browser incompatibilities with such an old device -- I hadn't even turned it on in more than 2 years, let alone updated its software -- the Child Themes Helper plugin was usable at that resolution. Below, 240px wide or tall, (smallest device is the Apple Watch at 240px x 240px) it will hide itself.

- Updated the Screenshot Options page. It works better now. Color options are faster and easier to play with. Somebody twist my arm and I'll make the color picker available as a plugin with an API interface for usage on the backend or a Gutenberg block for the front-end.

- Introduced demo mode. Demo mode works with my plugin demonstration site: [http://www.1acsi.com](http://www.1acsi.com) where I let anybody and everybody log in with demo/Demo and test drive the Child Themes Helper plugin.

- Changed the menu entry from 'Options' to 'Screenshot Options' to be more clear about what it is for. The primary functionality, to copy files to a child theme, does not have any options.

- File copy functionality works much faster now. I changed the process flow to eliminate 2 AJAX calls in favor of local clientside processing. Also eliminated the need to pass huge amounts of data between the server and the client.

- For devices less than 830px wide, which is most (if not all) smartphones in portrait mode and many smartphones in landscape mode, and even a few small-screened laptops, the Child Themes Helper's file copy functionality will now double-up as a single column and two black buttons on the left to switch between looking at the child theme's file listing and the parent theme's file listing.

= 1.1.3 =
- When creating a child theme, the path to the child theme stylesheet was wrong in functions.php. I used dirname( __FILE__ ) . "/style.css", but this created a server rooted path not a URL path to the child theme's stylesheet. This is now fixed.

- Updates to the readme.txt file.

= 1.1.2 =
- Discovered a non-unique "global" variable. Variables outside classes are global by their very nature. They must be unique across all of WordPress and any plugins or themes written or not yet written. This plugin prefixes all objects with global scope with the prefix "pas_cth_". The $pluginDirectory variable in pasChildThemes.php needed to be renamed to $pas_cth_pluginDirectory.
	
= 1.1.1 =
- Version 1\.1 was missing some files. This caused the plugin to crash when activated. Files that were new to version 1.1 didn't get added to the WordPress plugin repository.

= 1.1 =

1. *Updated the ScreenShot Options page*
	- Graphic size options have been removed.
	- Changing the background and/or foreground colors of the graphic will now display a color picker. If you already know the hex code for the color you want, you can directly enter it in the space provided on the color picker. If you know the decimal values for each of the colors, you can enter that in the spaces provided. Otherwise, you can just move the sliders to create your new color code.
	- The font selection dropdown list now provides a sample of each font to the right of the name of the font.
	- Fonts are easy to add or remove. Visit the plugin's assets/fonts folder and remove any fonts that you do not want as an option or add the .ttf file for any 

		The fonts dropdown list, now provides a sample of each font. The font samples are generated on the fly. If you copy a new font into the assets/fonts folder, that sample will be created and added to the drop down list immediately. **One caveat**: *if you remove the font that is specified as the selected font, the first time you generate a screenshot, the wording will be blank. Just regenerate it and it will work.*

1. *Replaced screenshot fonts*
	- Removed the existing fonts and replaced with a subset of the Google Fonts.

1. *Bug Fixes*
	- Fixed a bug where the style.css header block wasn't getting populated correctly.

= 1.0 =
- *First public release*

== Upgrade Notice ==

= 1.1.3 =
- Fixed a bug: The path to the child theme's stylesheet in the functions.php was incorrect.
- If you only use the Child Themes Helper for its primary purpose of copying files from the parent theme to a child theme, then this change will not effect you and there is no reason to update at this time.
- However, if you plan on creating a new child theme with this plugin, then you should update this plugin first.
