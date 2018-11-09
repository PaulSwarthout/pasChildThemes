=== Child Themes Helper ===
Contributors: paulswarthout
Author URI: http://www.paulswarthout.com/child-themes-helper
Donate link: https://paypal.me/PaulSwarthout
Tags: child themes helper, child themes, child theme, child, theme, template theme, parent theme, developers, IIS, Linux, copy files to child theme, create a child theme
Requires at least: 4.7.0
Tested up to: 4.9.8
Stable tag: 1.2
Requires PHP: 5.5.38
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Copies parent theme files to a child theme while maintaining the correct folder/subfolder structure in the child theme as in the parent theme.

== Description ==

1. **Copy files from Parent Theme to Child Theme**
	The primary purpose of the Child Themes Helper plugin is to copy files from a parent theme (also called the template theme) to a child theme. The folder path in the parent theme is duplicated in the child theme during the copy.

1. **Remove files from the Child Theme**
	The Child Themes Helper plugin will also remove any files that you no longer want in the child theme. Any folders that are made empty by the removal of a file or folder, will also be removed.

1. **Prompt before removal**
	The Child Themes Helper plugin will detect when a child theme file is different from its parent theme counterpart. If the files are not identical, the user will be prompted before allowing a parent theme file to be copied over an existing child theme file, or before allowing a child theme file to be removed.

1. **Create a child theme**
	The primary functionality of the Child Themes Helper plugin requires the existence of a child theme. If a child theme has not already been created, this plugin will help you to create a child theme of any of the currently installed themes (not other child themes) on the website.

1. **Generate a temporary graphic for the Themes page**
	Creating a child theme does not create a graphic for your new theme on the WordPress themes page. The Child Themes Helper plugin can create a graphic for your child theme. You're free to select the foreground and background colors for that graphic and choose from up to a couple of dozen Google Fonts. If you would like a different font, you only need to copy the .ttf file into the Child Themes Helper plugin's assets/fonts folder. The next time you open the Options page, the newly downloaded font will be displayed with a sample string.

1. **Notes**
	- *Screenshot*
		The temporary graphic is referred to as the ScreenShot because the filename is "screenshot.png" and is located in the root folder of your theme. The filename and the location are defined by the WordPress core and cannot be changed by this (or any) plugin.

		Your browser will cache the screenshot file whenever possible. If you modify the ScreenShot graphic and you do not see any changes when you generate a new one, then you will need to clear your browser's image and file cache.
		
		If you generate a screenshot graphic and you only see the background (i.e., no words), just generate the screenshot again. This happens when the selected font does not exist in the assets/fonts folder of the Child Themes Helper plugin. If you are updating from version 1.0, you will see this happen the first time that you generate a screenshot since the original fonts were deleted and replaced by Google Fonts.

		Most developers will replace this generated screenshot file with a graphic of their own. This feature is meant to provide a *temporary* graphic that quickly and easily identifies the child theme name and its parent theme.

		In a future release, there will be a lock feature on the options page to prevent accidental overwrites of the screenshot file. Also, in a future release, there will be the ability to select an existing graphic and crop it as necessary, instead of generating one.

	- *Child Themes Helper access*
		The Child Themes Helper is accessed from the WordPress dashboard under the heading "Child Themes Helper". It is located immediately below the *Appearance* Dashboard menu item.

	- *Platform Support*
		The Child Themes Helper plugin has been tested with both WordPress running on a Windows server and with WordPress running on a Linux server. Development is done on a Windows platform. If you find any compatibility issues with Linux, please let me know.
	
	- *Child Themes Helper plugin code*
		The GitHub repository for this plugin can be found [here](https://github.com/PaulSwarthout/pasChildThemes).

	- *Development versions*
		Versions 1\.1\.3 and 1\.0 are available for download and install.

== Installation ==

- This plugin may be installed through the usual method of installing and activating WordPress plugins. Once installed, it is ready to be used. No further configuration is necessary.

- Once installed and activated:
	1. if the currently active theme is **not** a child theme, the user will see a form to create a	child theme.

	1. if the currently active theme **is** a child theme, the user will see two vertical panes. The left-hand pane shows the child theme's files and the right-hand pane shows the parent theme's files.

- Reminder: Only 1 theme can be active at a time. This plugin works with a child theme. Even though a child theme and a parent theme go together, only the child theme or the parent theme can be marked as active.

- *If you downloaded the Child Themes Helper plugin, directly, from the [WordPress Plugin Page](https://wordpress.org/plugins/child-themes-helper/)*, then follow these instructions to install.
	1. If you have already tried this plugin and you are trying a different version through this installation method, I highly recommend that you deactivate the plugin and delete it before following the next steps. There are multiple files that existed in earlier versions that do not exist or are no longer used in later versions. If you don't want to delete it, first, then at least deactivate it.
	1. Using your favorite FTP client, find the wp-content/plugins folder and create the child-themes-helper sub folder.
	1. Unzip the downloaded file and copy the contents to the wp-content/plugins/child-themes-helper folder on your web server. Overwrite anything that previously existed.
	1. Activate the Child Themes Helper plugin.
	1. You're ready to go. You should see the menu option "Child Themes Helper" on your dashboard menu just below the Appearance menu item.

== Frequently Asked Questions ==

= What is next? =

I am glad you asked.

- The ability to directly edit a file will be available in version 1.3.
- The ability to edit (through a form prompt) the different fields that define a child theme. Basically this will let your directly edit the style.css header without opening the file and editting it directly.
- I'm considering a drag and drop feature for files, rather than just click on them for the added user feedback that it supports. For now, users of wide screens see a hovering prompt that tells them what they're about to do. But users of small screens, do see that. I think a drag and drop feature would help the users of smalls screens be more clear on what's about to happen.

- A pro version that encompasses even more features like:
	- The ability to copy multiple files and/or folders at once, instead of a single file at a time.
	- The ability to make a modified non-child theme into a child theme of itself.
	- Some kind of feature to make stylesheets more manageable. Something to help the relative newcomers understand what they're changing and why, and more importantly, what to change.

= Where I can see the Child Themes Helper in action? =

I am glad you asked. Starting with version 1.2, you can visit [my demo page](http://www.1acsi.com) and take it for a test drive. Create your own child theme. Copy files to the newly created child theme. Generate screenshots. Change the Screenshot options. In short, put it through its paces. And don't worry about screwing up the website. It's there for that purpose.

= Why do the prompts sometimes disappear without making any changes? =

Originally, I thought it would be nice to be able to close any prompt by just clicking on it. But as I started to use the plugin, myself, I discovered that, that feature, was more troublesome than useful. I was always clicking beside a button rather than right on it, and poof, the prompt would vanish. Argh!!!

Effective with version 1.2, this problem has been resolved. You must now click on the provided buttons to close the prompts.

= I generated a screenshot but it didn't change. Why not? =

You did nothing wrong. Your browser will cache the screenshot file to help your website to load more quickly. The solution is to clear your browser's image cache. [Here's a good article](https://kb.iu.edu/d/ahic) that I found that gives great directions on how to accomplish that.

= I generated a screenshot but all I see is the background color. What did I do wrong? =

Updated Answer: This issue has been fixed in version 1.2. Please update.

Original Answer: You did nothing wrong. Just generate the screenshot again and it should work fine. This is a nagging issue that happens when the chosen font doesn't exist in the assets/fonts folder. If you have deleted any fonts from that folder, or updated the Child Themes Helper to a newer version, the font that you chose (or defaulted to) on the Options page, may no longer exist. The first time you generate a screenshot, it cannot find the chosen font, so it resets it to the default font. The second time you generate a screenshot, it will use the default. Also, note the question above.

= What does the "Generate ScreenShot" option do? =

The WordPress Themes page displays a graphic for each theme. A newly created child theme does not have a graphic. The Generate ScreenShot menu option creates a temporary graphic. Generally, developers will use a copy of a header image for their screenshot. But rather than leaving it blank until later, the Child Themes Helper plugin will create a temporary graphic which displays the child theme name, the parent theme name, a message indicating that the child theme was created using the Child Themes Helper and the Child Themes Helper's developer's web address. It is expected that the developer will want to replace this temporary graphic with their own custom graphic at a later time. Please check out [my demo page](http://www.1acsi.com) where you are welcome to take the Child Themes Helper plugin for a test drive.

== Screenshots ==

1. The files for the Child Theme are displayed in the left-hand pane. The name, 'MyChildTheme' for example
is the name of your Child Theme. The page scrolls.
2. The files for the Template / Parent Theme's, or the Child Theme's parent theme, are displayed in the right-hand pane.
For this example, the Child Theme was created as a child of the 'Twenty Sixteen' theme and it's name appears at the top. The page scrolls.

== Changelog ==

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
