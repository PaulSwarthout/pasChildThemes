<?PHP
class pas_cth_themes {
	private $pluginDirectory;
	public	$childParentThemesList;
	public	$listChildThemes;
	public	$listTempateThemes;
	public	$pas_cth_active_theme;
	public $WP_Active_Theme;

	function __construct($args) {
		$this->pluginDirectory = (array_key_exists('pluginDirectory', $args) ? $args['pluginDirectory'] : ['url' => '', 'path' => '']);

		$allThemes				= wp_get_themes(); // plural
		$this->WP_Active_Theme	= wp_get_theme()->name;

		$this->pas_cth_active_theme = get_option("pas_cth_active_theme", false);

		$this->listChildThemes		= [];
		$this->listTemplateThemes	= [];
		$this->childParentThemesList= [];
		$childThemeNames			= [];

		foreach ($allThemes as $key => $wpThemeObject) {
			if ($wpThemeObject->parent()) {
				$childObject =
					[
						'themeName'				=>	$wpThemeObject->name,
						'stylesheet'			=>	$wpThemeObject->get_stylesheet(),
						'stylesheet_directory'	=>	$this->fixFileDelimiters($wpThemeObject->get_stylesheet_directory()),
						'template'				=>	$wpThemeObject->get_template(),
						'template_directory'	=>	$this->fixFileDelimiters($wpThemeObject->get_template_directory()),
						'theme_root'			=>	$this->fixFileDelimiters($wpThemeObject->get_theme_root()),
						'parent_theme_name'		=>	$wpThemeObject->parent()->name,
						'parent_theme'			=>	$wpThemeObject->parent(),
						'WP_Theme'				=>	$wpThemeObject,
					];
				if (! constant('WP_DEBUG')) {
					unset($childObject['LASTELEMENT']);
				}
				$this->listChildThemes[$key] = $childObject;
				array_push($childThemeNames, $childObject['themeName']);
			} else {
				$templateObject =
					[
						'themeName'				=>	$wpThemeObject->name,
						'stylesheet'			=>	$wpThemeObject->get_stylesheet(),
						'stylesheet_directory'	=>	$this->fixFileDelimiters($wpThemeObject->get_stylesheet_directory()),
						'theme_root'			=>	$this->fixFileDelimiters($wpThemeObject->get_theme_root()),
					];
					
				$this->listTemplateThemes[$key] = $templateObject;
			}
		}
		$this->childParentThemesList = [];
		foreach ($this->listChildThemes as $key => $object) {
			array_push($this->childParentThemesList, 
				[
					'child'				=>	$object['themeName'],
					'parent'			=>	$object['parent_theme_name'],
					'childStylesheet'	=>	$object['stylesheet'],
					'parentStylesheet'	=>	$object['parent_theme']->get_stylesheet(),
				]);
		}
		foreach ($this->listTemplateThemes as $key => $object) {
			array_push($this->childParentThemesList, 
				[
					'child'				=>	null,
					'childStylesheet'	=>	null,
					'parent'			=>	$object['themeName'],
					'parentStylesheet'	=>	$object['stylesheet'],
				]);
		}

		usort($this->childParentThemesList, array( $this, "theme_sort" ) );
	}

	private function theme_sort($a, $b) {
		$a_up = $a;
		$b_up = $b;
		$a_up['parent'] = strtoupper($a_up['parent']);
		$a_up['child']	= strtoupper($a_up['child']);
		$b_up['parent'] = strtoupper($b_up['parent']);
		$b_up['child'] = strtoupper($b_up['child']);

		if ($a_up['parent'] == $b_up['parent'] && $a_up['child'] == $b_up['child']) {
			return 0;
		} elseif ($a_up['parent'] == $b_up['parent'] && $a_up['child'] < $b_up['child']) {
			return -1;
		} elseif ($a_up['parent'] == $b_up['parent']) {
			return 1;
		} elseif ($a_up['parent'] < $b_up['parent']) {
			return -1;
		} else {
			return 1;
		}
	}

	private function fixFileDelimiters($path) {
		$path = str_replace("/", "|+|", $path);
		$path = str_replace("\\", "|+|", $path);
		$path = str_replace("|+|", "/", $path);
		return $path;
	}
}
