<?php
function enumerateThemes() {
	global $currentThemeObject;
	$themes = array();

	// Loads all theme data
	$all_themes = wp_get_themes();

	// Loads theme names into themes array
	foreach ($all_themes as $theme) {
		$name = $theme->get('Name');
		$stylesheet = $theme->get_stylesheet();

		if ($theme->parent()) {
			$status = true;
		} else {
			$status = false;
		}
		$parent = $theme->get('Template');
		$parentStylesheet = $theme->get_stylesheet();

		$themes[$stylesheet] = Array ('themeName' => $name, 'themeStylesheet' => $stylesheet, 'themeParent' => $parent, 'parentStylesheet' => $parentStylesheet, 'childTheme' => $status);
	}

	return $themes;
}