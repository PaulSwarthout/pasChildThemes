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
		public  $status;

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
				$this->status = true;
			} else {
				$this->status = false;
				// current theme is not a child theme. Bow out gracefully.
			}
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
