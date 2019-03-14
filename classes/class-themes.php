<?PHP
class pas_cth_theme {
	private $wp_object;

	function __construct($object) {
		$this->wp_object = $object;
	}
}
class pas_cth_themes {
	private $plugin_directory;
	public $allThemes;
	private $childThemes;
	private $templateThemes;

	function __construct($args) {
		$this->plugin_directory = (array_key_exists('plugin_directory', $args) ? $args['plugin_directory'] : ['url' => '', 'path' => '']);

		$this->allThemes = wp_get_themes();
		$activeTheme = wp_get_theme()->get_stylesheet();

		$themeList = [];
		$childThemeNames = [];

		foreach ($this->allThemes as $key => $object) {
			if ($object->parent()) {
				$childObject =
					[
						'themeName'				=>	$key,
						'stylesheet'			=>	$object->get_stylesheet(),
						'stylesheet_directory'	=>	$this->fixFileDelimiters($object->get_stylesheet_directory()),
						'template'				=>	$object->get_template(),
						'template_directory'	=>	$this->fixFileDelimiters($object->get_template_directory()),
						'theme_root'			=>	$this->fixFileDelimiters($object->get_theme_root()),
//						'WP_Theme'				=>	$object,
						'parent_theme'			=>	$object->parent()->get_template(),
					];
				$this->childThemes[$key] = $childObject;
				array_push($childThemeNames, $childObject['themeName']);
			} else {
				$templateObject =
					[
						'themeName'				=>	$key,
						'stylesheet'			=>	$object->get_stylesheet(),
						'stylesheet_directory'	=>	$this->fixFileDelimiters($object->get_stylesheet_directory()),
						'theme_root'			=>	$this->fixFileDelimiters($object->get_theme_root()),
//						'WP_Theme'				=>	$object,
					];
				$this->templateThemes[$key] = $templateObject;
			}
		}
		$this->allThemes =
			[
				'activeTheme'	=>	$activeTheme,
				'isChild'		=>	(array_search($activeTheme, $childThemeNames) ? true : false),
				'childThemes'	=>	$this->childThemes,
				'parentThemes'	=>	$this->templateThemes,
			];
		unset($childThemeNames);
	}

	private function fixFileDelimiters($path) {
		$path = str_replace("/", "|+|", $path);
		$path = str_replace("\\", "|+|", $path);
		$path = str_replace("|+|", "/", $path);
		return $path;
	}
}
