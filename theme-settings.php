<?php
// $Id: $

// Include the definition of zen_theme_get_default_settings().
include_once './' . drupal_get_path('theme', 'touch') . '/template.theme-registry.inc';


/**
 * Implementation of THEMEHOOK_settings() function.
 *
 * @param $saved_settings
 *   An array of saved settings for this theme.
 * @param $subtheme_defaults
 *   Allow a subtheme to override the default values.
 * @return
 *   A form array.
 */
function touch_settings($saved_settings, $subtheme_defaults = array()) {
  /*
   * The default values for the theme variables. Make sure $defaults exactly
   * matches the $defaults in the template.php file.
   */
  // Add CSS to adjust the layout on the settings page
  drupal_add_css(drupal_get_path('theme', 'touch') . '/css/theme-settings.css', 'theme');

  // Add javascript 
  // drupal_add_js(drupal_get_path('theme', 'touch') . '/js/theme-settings.js', 'theme');
  
  // Get the default values from the .info file.
  $defaults = touch_theme_get_default_settings('blueprint');

  // Allow a subtheme to override the default values.
  $defaults = array_merge($defaults, $subtheme_defaults);

  // Merge the saved variables and their default values.
  $settings = array_merge($defaults, $saved_settings);

  
  // Setting for flush all caches
  $form['touch_block_edit_links'] = array(
     '#type'          => 'checkbox',
     '#title'         => t('Display block editing links.'),
     '#default_value' => $settings['touch_block_edit_links'],
     '#description'   => t('When hovering over blocks, display edit links for the proper users.'),
    );
  
  // Setting for flush all caches
  $form['touch_rebuild_registry'] = array(
     '#type'          => 'checkbox',
     '#title'         => t('Rebuild the theme registry on every page.'),
     '#default_value' => $settings['touch_rebuild_registry'],
     '#description'   => t('During theme development, it can be very useful to continuously <a href="!link">rebuild the theme registry</a>. WARNING: this is a huge performance penalty and must be turned off on production websites.', array('!link' => 'http://drupal.org/node/173880#theme-registry')),
    );

  $form['visual_settings'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Visual settings'),
    '#attributes'    => array('id' => 'touch-visual'),
  );
  $form['visual_settings']['touch_collapse_primary'] = array(
     '#type'          => 'checkbox',
     '#title'         => t('Collapse the Primary Links'),
     '#default_value' => $settings['touch_collapse_primary'],
     '#description'   => t('Collapse the Primary Links to make them take up less room.'),
    );
  $form['apple_touch_icon'] = array(
    '#type'          => 'fieldset',
    '#title'         => t('Apple Touch Icon'),
    '#attributes'    => array('id' => 'apple_touch_icon'),
    '#description'   => t('iPhone\'s and iPod Touch\'s support what Apple calls a Touch Icon. This shows up when you bookmark your Drupal site on the homescreen of a touch device.  Minimum size: 57 × 57 px. For better quality, use a 158px x 158px image.'),
  );
  $form['apple_touch_icon']['apple_icon_enabled'] = array(
     '#type'          => 'checkbox',
     '#title'         => t('Enable the "Apple Touch Icon" on your site'),
     '#default_value' => $settings['apple_icon_enabled'],
    );
  
  $form['apple_touch_icon']['apple_icon_location'] = array(
    '#type'           => 'textfield', 
    '#title'          => t('Path to custom Apple Touch Icon'), 
    '#default_value'  => $settings['apple_icon_location'], 
    '#size'           => 60, 
    '#maxlength'      => 128, 
    '#description'    => t('The path to the image file you would like to use as your custom shortcut icon.'),
  );
  $form['apple_touch_icon']['apple_icon_upload'] = array(
    '#type'           => 'file', 
    '#title'          => t('Attach new file'), 
    '#size'           => 40,
    '#description'    => t('If you don\'t have direct file access to the server, use this field to upload your shortcut icon.'),
  );
  // $form['#submit'][] = 'touch_settings_submit';
  $form['apple_touch_icon']['apple_icon_upload']['#element_validate'][] = 'touch_settings_submit';
  // Return the additional form widgets
  return $form;
}


/**
* Capture theme settings submissions and update uploaded image
*/
function touch_settings_submit($form, &$form_state) {

  // Check for a new uploaded file, and use that if available.
  if ($file = file_save_upload('apple_icon_upload')) {
    $parts = pathinfo($file->filename);
    $filename = (! empty($key)) ? str_replace('/', '_', $key) .'_touch_icon.'. $parts['extension'] : 'touchlogo.'. $parts['extension'];

    // The image was saved using file_save_upload() and was added to the
    // files table as a temporary file. We'll make a copy and let the garbage
    // collector delete the original upload.
    if (file_copy($file, $filename)) {
      $_POST['apple_icon_enabled'] = $form_state['values']['apple_icon_enabled'] = TRUE;
      $_POST['apple_icon_location'] = $form_state['values']['apple_icon_location'] = $file->filepath;
    }
  }
}