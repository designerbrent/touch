<?php

/**
 * Implements HOOK_theme().
 *
 * We are simply using this hook as a convenient time to do some related work.
 */
function _touch_theme(&$existing, $type, $theme, $path) {

  // Since we are rebuilding the theme registry and the theme settings' default
  // values may have changed, make sure they are saved in the database properly.
  touch_theme_get_default_settings($theme);

  // If we are auto-rebuilding the theme registry, warn about the feature.
  // Always display the warning in the admin section, otherwise limit to three
  // warnings per hour.
  if (user_access('administer site configuration') && theme_get_setting('touch_rebuild_registry') && $theme == $GLOBALS['theme'] && (arg(0) == 'admin' || flood_is_allowed($GLOBALS['theme'] . '_rebuild_registry_warning', 3))) {
    flood_register_event($GLOBALS['theme'] . '_rebuild_registry_warning');
    drupal_set_message(t('For easier theme development, the theme registry is being rebuilt on every page request. It is <em>extremely</em> important to <a href="!link">turn off this feature</a> on production websites.', array('!link' => url('admin/build/themes/settings/' . $GLOBALS['theme']))), 'warning', FALSE);
    
  }

  // Return nothing.
  return array();
}


/**
 * Return the theme settings' default values from the .info and save them into the database.
 *
 * @param $theme
 *   The name of theme.
 */
function touch_theme_get_default_settings($theme) {
  $themes = list_themes();
  // Get the default values from the .info file.
  $defaults = !empty($themes[$theme]->info['settings']) ? $themes[$theme]->info['settings'] : array();

  if (!empty($defaults)) {
    // Get the theme settings saved in the database.
    $settings = theme_get_settings($theme);
    // Save default theme settings.
    variable_set(
      str_replace('/', '_', 'theme_' . $theme . '_settings'),
      array_merge($defaults, $settings)
    );
    // If the active theme has been loaded, force refresh of Drupal internals.
    if (!empty($GLOBALS['theme_key'])) {
      theme_get_setting('', TRUE);
    }
  }


  /*
  * Initialize theme settings
  */
  // if (is_null(theme_get_setting('touch_node.css'))) {  // <-- change this line
  //   global $theme_key;
  // 
  //   /*
  //    * The default values for the theme variables. Make sure $defaults exactly
  //    * matches the $defaults in the theme-settings.php file.
  //    */
  //   $css = drupal_add_css();
  //   $defaults = array();
  //   foreach ($css['all']['module'] as $k => $path) {
  //     $file = substr($k, strrpos($k, '/') + 1);
  //     $file = str_replace('.css','',$file);
  //     $defaults['touch_'. $file] = 0;
  //   }
  // 
  //   // Get default theme settings.
  //   $settings = theme_get_settings($theme_key);
  // 
  //   // Don't save the toggle_node_info_ variables.
  //   if (module_exists('node')) {
  //     foreach (node_get_types() as $type => $name) {
  //       unset($settings['toggle_node_info_' . $type]);
  //     }
  //   }
  //   // Save default theme settings.
  //   variable_set(
  //     str_replace('/', '_', 'theme_'. $theme_key .'_settings'),
  //     array_merge($defaults, $settings)
  //   );
  //   // Force refresh of Drupal internals.
  //   theme_get_setting('', TRUE);
  // }

  // Return the default settings.
  return $defaults;
}