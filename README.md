# pasChildThemes
Child Themes Helper Plugin
This plugin does 2 things:
1) I've always thought, copying files from a template theme to a child theme was a pain in the caboose.  Find the file with your FTP Client, copy it to the local device, change directory to the right path in the child theme, then copy the file back to the child theme and into the right folder.
This plugin solves that problem. You can simply click on the template theme file that you want to copy, and this plugin will copy it to the appropriate folder in the child theme.
If the file already exists and it's been changed, you'll get a pop-up warning message. You can choose to overwrite the file anyway, or not.
If you click on a file in the child theme, it will be removed from the child theme. If the file has been modified, you'll get a popup warning message. You can choose to delete anyway or not.

2) If the current theme is NOT a child theme, you can create a new child theme. The plugin creates the child theme, and creates the basic functions.php and style.css files.
The functions.php is built such that the style.css files are included in the proper order.
