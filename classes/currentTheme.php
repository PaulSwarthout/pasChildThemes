<?PHP
if (! class_exists('pasChildThemes_activeTheme') ) {
	class pasChildTheme_currentTheme {
		private $currentActiveTheme; // WP_Theme Object for the currently active theme
		private $name;
		private $themeRoot;
		private $stylesheet;
		private $parentName;
		private $parentStylesheet;
		private $parentThemeRoot;

		function __construct() {
			$this->currentActiveTheme = wp_get_theme();
			$this->name = $this->currentActiveTheme->get("Name");
			$this->stylesheet = $this->currentActiveTheme->get_stylesheet();
			$this->themeRoot = $this->fixDelimiters($this->currentActiveTheme->get_theme_root() . (isWin() ? "\\" : "/") . $this->stylesheet);
			$parent = $this->currentActiveTheme->parent();

			if ($parent) {
				$this->parentName = $parent->get("Name");
				$this->parentStylesheet = $parent->get_stylesheet();
				$this->parentThemeRoot = $this->fixDelimiters($parent->get_theme_root() . (isWin() ? "\\" : "/") . $this->parentStylesheet);
			}
			$x = [ 'current theme' => $this->name, 'current stylesheet' => $this->stylesheet, 'current theme root' => $this->themeRoot,
				     'parent theme' => $this->parentName, 'parent stylesheet' => $this->parentStylesheet, 'parent theme root' => $this->parentThemeRoot ];
//			echo "<pre>" . print_r($x, true) . "</pre>";
		}
		private function fixDelimiters($path) {
			if (isWin()) {
				$path = str_replace("\\", "/", $path);
				$path = str_replace("/", "\\", $path);
				return $path;
			} else {
				return $path;
			}
		}
		public function name() {
			return $this->name;
		}
		public function themeStylesheet() {
			return $this->stylesheet;
		}
		public function themeRoot() {
			return $this->themeRoot;
		}
		public function parent() {
			return $this->parentName;
		}
		public function parentStylesheet() {
			return $this->parentStylesheet;
		}
		public function parentThemeRoot() {
			return $this->parentThemeRoot;
		}
	}
}
