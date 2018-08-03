<?PHP

define('NEWLINE', "\n");
define('CHILDTHEME', "child");
define('TEMPLATETHEME', "parent");

define('PASCHILDTHEMES_DEFAULT_IMAGE_WIDTH', 1200);
define('PASCHILDTHEMES_DEFAULT_IMAGE_HEIGHT', 900);
define('PASCHILDTHEMES_DEFAULT_SCREENSHOT_BCCOLOR', '#002500');
define('PASCHILDTHEMES_DEFAULT_SCREENSHOT_FCCOLOR', '#FFFF00');
define('PASCHILDTHEMES_DEFAULT_SCREENSHOT_FONT', 'arial.ttf');

define('PAULSWARTHOUT_URL', 'http://www.PaulSwarthout.com/WordPress');
define('PASCHILDTHEMES_NAME', '...created by Child Themes Helper...');

define('DOTS', '...............................................................................');

define('WINSEPARATOR', '\\');
/*
 * As has been the case for many years, Windows uses the folder delimiter of a backslash.
 * Unix, Linux, and most of the rest of the world, uses a forward slash character as a folder delimiter.
 * In cross platform development, when dealing with files and paths, this has always been a problem.
 * However, PHP has been good about allowing Windows users to use either the forward slash or
 * backslash in their PHP scripts.
 * 
 * Therefore, all folder delimiters, where possible are changed to the forward slash (SEPARATOR)
 * character throughout this plugin.
 */
define('SEPARATOR', "/");
