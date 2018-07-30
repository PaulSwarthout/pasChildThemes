<?PHP
if (! class_exists('pasChildThemes_activeTheme') ) {
	class pasChildTheme_currentTheme {
		private $currentActiveTheme; // WP_Theme Object for the currently active theme
		private $templateTheme;

		public $childThemeName;
		public $childThemeRoot;
		public $childStylesheet;
		public $templateThemeName;
		public $templateStylesheet;
		public $templateThemeRoot;
		// Status is true if the currently active theme is a child theme, false otherwise.
		public $isChildTheme;

		function __construct() {
			$this->currentActiveTheme = wp_get_theme();

			$this->childThemeName				= $this->currentActiveTheme->get("Name");
			$this->childStylesheet			= $this->currentActiveTheme->get_stylesheet();
			$this->childThemeRoot				= $this->fixDelimiters($this->currentActiveTheme->get_theme_root());

			$this->templateTheme = $this->currentActiveTheme->parent();

			if ($this->templateTheme) {
				$this->templateThemeName	= $this->templateTheme->get("Name");
				$this->templateStylesheet = $this->templateTheme->get_stylesheet();
				$this->templateThemeRoot	= $this->fixDelimiters($this->templateTheme->get_theme_root());

				// Current theme is a child theme
				$this->isChildTheme = true;
			} else {
				// Current theme is NOT a child theme
				$this->isChildTheme = false;
			}
		}
		private function fixDelimiters($path) {
			$path = str_replace("\\", "|+|", $path);
			$path = str_replace("/", "|+|", $path);
			$path = str_replace("|+|", SEPARATOR, $path);
			return $path;
		}

		public function getChildFolder() {
			return $this->childThemeRoot . SEPARATOR . $this->childStylesheet;
		}

		public function getTemplateFolder() {
			return ($this->isChildTheme ? $this->templateThemeRoot . SEPARATOR . $this->templateStylesheet : false);
		}
	}
}
