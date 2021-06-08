<?PHP
namespace child_themes_helper;

define( 'PAS_CTH_CHILDTHEME', "child" );
define( 'PAS_CTH_TEMPLATETHEME', "parent" );


define( 'PAS_CTH_DEFAULT_IMAGE_WIDTH', 1200 );
define( 'PAS_CTH_DEFAULT_IMAGE_HEIGHT', 900 );
define( 'PAS_CTH_DEFAULT_SCREENSHOT_BCCOLOR', '#002500' );
define( 'PAS_CTH_DEFAULT_SCREENSHOT_FCCOLOR', '#FFFF00' );
define( 'PAS_CTH_DEFAULT_FONT',
		serialize( ['fontFile-base'=>'Roboto-Medium', 'fontName'=>'Roboto Medium'] ) );


define( 'PAS_CTH_MYNAME', 'Child Themes Helper plugin' );
define( 'PAS_CTH_MYURL', 'http://www.paulswarthout.com/child-themes-helper' );
define( 'PAS_CTH_PLUGINNAME', '...created by Child Themes Helper...' );


define( 'DOTS', '...............................................................................' );


delete_option('pas_cth_edit_allowedFileTypes');
update_option('pas_cth_edit_allowedFileTypes', get_option('pas_cth_edit_allowedFileTypes', ['js', 'css', 'php', 'txt', 'xml', 'html']));


/*
 * As has been the case for many years, Windows uses the folder delimiter of a backslash.
 * Unix, Linux, and most of the rest of the world, uses a forward slash character as a folder
 * delimiter.
 * In cross platform development, when dealing with files and paths, this has always been a problem.
 * However, PHP has been good about allowing Windows users to use either the forward slash or
 * backslash in their PHP scripts.
 *
 * Therefore, all folder delimiters, where possible are changed to the forward slash
 * ( PAS_CTH_SEPARATOR )
 * character throughout this plugin.
 */
define( 'PAS_CTH_SEPARATOR', "/" );


define( 'PAS_CTH_NOT_AUTHORIZED_REMOVE_FILE',	'Not authorized to remove file');
define( 'PAS_CTH_NOT_AUTHORIZED_COPY_FILE',		'Not authorized to copy file');
define( 'PAS_CTH_NOT_AUTHORIZED_EDIT_FILE',		'Not authorized to edit file');
define( 'PAS_CTH_NOT_AUTHORIZED_VIEW_FILE',		'Not authorized to view file');
define( 'PAS_CTH_NOT_AUTHORIZED_CREATE_THEME',	'Not authorized to create child themes');
define( 'PAS_CTH_NOT_AUTHORIZED_CHANGE_OPTIONS','Not authorized to change options');
define( 'PAS_CTH_NOT_AUTHORIZED', 'Not authorized');
