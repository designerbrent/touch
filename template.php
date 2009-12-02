<?php
// $Id: template.php,v 1.15.2.1.2.20 2009/10/29 00:27:39 designerbrent Exp $

/**
 * Uncomment the following line during development to automatically
 * flush the theme cache when you load the page. That way it will
 * always look for new tpl files.
 */
// drupal_flush_all_caches();

//disable admin menu 
if (module_exists('admin_menu')) {
  admin_menu_suppress(TRUE);
}

// Auto-rebuild the theme registry during theme development.
if (theme_get_setting('touch_rebuild_registry')) {
  drupal_rebuild_theme_registry();
}

/**
 * Implements HOOK_theme().
 */
function touch_theme(&$existing, $type, $theme, $path) {
  if (!db_is_active()) {
    return array();
  }
  include_once './' . drupal_get_path('theme', 'touch') . '/template.theme-registry.inc';
  return _touch_theme($existing, $type, $theme, $path);
}


/**
 * Intercept page template variables
 *
 * @param $vars
 *   A sequential array of variables passed to the theme function.
 */
function touch_preprocess_page(&$vars) {
  global $user;
  $vars['path'] = base_path() . path_to_theme() .'/';
  $vars['user'] = $user;
  // Fixup the $head_title and $title vars to display better.
  $title = drupal_get_title();
  $headers = drupal_set_header();
  
  // wrap taxonomy listing pages in quotes and prefix with topic
  if (arg(0) == 'taxonomy' && arg(1) == 'term' && is_numeric(arg(2))) {
    $title = t('Topic') .' &#8220;'. $title .'&#8221;';
  }
  // if this is a 403 and they aren't logged in, tell them they need to log in
  else if (strpos($headers, 'HTTP/1.1 403 Forbidden') && !$user->uid) {
    $title = t('Please login to continue');
  }
  $vars['title'] = $title;

  if (!drupal_is_front_page()) {
    $vars['head_title'] = $title .' | '. $vars['site_name'];
    if ($vars['site_slogan'] != '') {
      $vars['head_title'] .= ' &ndash; '. $vars['site_slogan'];
    }
  }

  // Resize the site logo to make it 30 px square
  
  // dpm($vars['logo']);
  // $logo_info = image_get_info('http://drupal6/sites/default/files/touch_logo.gif');
  // dpm($logo_info);
  // dpm(if ($picture) { print theme('user_picture', $node, '65x65'); } );
  // $logoimage = touch_icon_picture("/sites/default/files/touch_logo.gif", '65x65');
  

  // Wrap the Menu in a div for collapsing 
  if ($vars['primary_links']) {
    if ($vars['logo']) {
      $primary_links_class = ' logo ';
    }
    $vars['primary_links'] = theme('links', $vars['primary_links'], array('id' => 'nav', 'class' => 'links'));
    if (theme_get_setting('touch_collapse_primary')) {
      $primary_links_class .= 'closed ';
    } else {
      $primary_links_class .= 'open ';
    }
      $vars['primary_links'] = '<div id="primary-links" class="menu '. $primary_links_class .'"> <h3 class="title toggle-button">Menu</h3><div class="blockwrapper">' . $vars['primary_links'] . '</div></div>';
    
    
  }
  // layout variables
    $vars['blocks_classes'] = 'col-blocks';
    $vars['main_classes'] = 'col-main';
    $vars['body_classes'] .= ' content ';

  $vars['meta'] = '';
  // SEO optimization, add in the node's teaser, or if on the homepage, the mission statement
  // as a description of the page that appears in search engines
  if ($vars['is_front'] && $vars['mission'] != '') {
    $vars['meta'] .= '<meta name="description" content="'. touch_trim_text($vars['mission']) .'" />'."\n";
  }
  else if (isset($vars['node']->teaser) && $vars['node']->teaser != '') {
    $vars['meta'] .= '<meta name="description" content="'. touch_trim_text($vars['node']->teaser) .'" />'."\n";
  }
  else if (isset($vars['node']->body) && $vars['node']->body != '') {
    $vars['meta'] .= '<meta name="description" content="'. touch_trim_text($vars['node']->body) .'" />'."\n";
  }
  // SEO optimization, if the node has tags, use these as keywords for the page
  if (isset($vars['node']->taxonomy)) {
    $keywords = array();
    foreach ($vars['node']->taxonomy as $term) {
      $keywords[] = $term->name;
    }
    $vars['meta'] .= '<meta name="keywords" content="'. implode(',', $keywords) .'" />'."\n";
  }

  // SEO optimization, avoid duplicate titles in search indexes for pager pages
  if (isset($_GET['page']) || isset($_GET['sort'])) {
    $vars['meta'] .= '<meta name="robots" content="noindex,follow" />'. "\n";
  }

}

/**
 * Intercept node template variables
 *
 * @param $vars
 *   A sequential array of variables passed to the theme function.
 */
function touch_preprocess_node(&$vars) {
  $node = $vars['node']; // for easy reference
  // for easy variable adding for different node types
  switch ($node->type) {
    case 'page':
      break;
  }
}

/**
 * Intercept comment template variables
 *
 * @param $vars
 *   A sequential array of variables passed to the theme function.
 */
function touch_preprocess_comment(&$vars) {
  static $comment_count = 1; // keep track the # of comments rendered
  
  // Calculate the comment number for each comment with accounting for pages.
  if ($page = $_GET['page']) {
    $comments_per_page = variable_get('comment_default_per_page_' . $vars['node']->type, 1);
    $comments_previous = $comments_per_page * $page;
  }
  $vars['comment_count'] =  $comments_previous + $comment_count;
    
  // if the author of the node comments as well, highlight that comment
  $node = node_load($vars['comment']->nid);
  if ($vars['comment']->uid == $node->uid) {
    $vars['author_comment'] = TRUE;
  }
  // only show links for users that can administer links
  if (!user_access('administer comments')) {
    $vars['links'] = '';
  }
  // If comment subjects are disabled, don't display them.
  if (variable_get('comment_subject_field_' . $vars['node']->type, 1) == 0) {
    $vars['title'] = '';
  }
  // if user has no picture, add in a filler
  if (theme_get_setting('toggle_comment_user_picture') && empty($vars['comment']->picture)) {
    $vars['picture'] = '<div class="no-picture">&nbsp;</div>';
  }

  // Add the pager variable to the title link if it needs it.
  $fragment = 'comment-' . $vars['comment']->cid;
  if ($page != NULL) {
    $query = 'page='. $page;
  }
  $vars['title'] = l($vars['comment']->subject, $vars['node']->path, array('query' => $query, 'fragment' => $fragment));
  $vars['comment_count_link'] = l('#'. $vars['comment_count'], $vars['node']->path, array('query' => $query, 'fragment' => $fragment));


  $comment_count++;
}

/**
 * Override or insert variables into the block templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
function touch_preprocess_block(&$vars, $hook) {
  $block = $vars['block'];

  // Special classes for blocks.
  $classes = array('block');
  $classes[] = 'block-' . $block->module;
  $classes[] = 'region-' . $vars['block_zebra'];
  $classes[] = $vars['zebra'];
  $classes[] = 'region-count-' . $vars['block_id'];
  $classes[] = 'count-' . $vars['id'];

  // Render block classes.
  $vars['classes'] = implode(' ', $classes);
}


/**
 * Intercept box template variables
 *
 * @param $vars
 *   A sequential array of variables passed to the theme function.
 */
function touch_preprocess_box(&$vars) {
  // rename to more common text
  if (strpos($vars['title'], 'Post new comment') === 0) {
    $vars['title'] = 'Add your comment';
  }
}

/**
 * Override, remove "not verified", confusing
 *
 * Format a username.
 *
 * @param $object
 *   The user object to format, usually returned from user_load().
 * @return
 *   A string containing an HTML link to the user's page if the passed object
 *   suggests that this is a site user. Otherwise, only the username is returned.
 */
function touch_username($object) {
  if ($object->uid && $object->name) {
    // Shorten the name when it is too long or it will break many tables.
    if (drupal_strlen($object->name) > 20) {
      $name = drupal_substr($object->name, 0, 15) .'...';
    }
    else {
      $name = $object->name;
    }

    if (user_access('access user profiles')) {
      $output = l($name, 'user/'. $object->uid, array('attributes' => array('title' => t('View user profile.'))));
    }
    else {
      $output = check_plain($name);
    }
  }
  else if ($object->name) {
    // Sometimes modules display content composed by people who are
    // not registered members of the site (e.g. mailing list or news
    // aggregator modules). This clause enables modules to display
    // the true author of the content.
    if (!empty($object->homepage)) {
      $output = l($object->name, $object->homepage, array('attributes' => array('rel' => 'nofollow')));
    }
    else {
      $output = check_plain($object->name);
    }
  }
  else {
    $output = variable_get('anonymous', t('Anonymous'));
  }

  return $output;
}

/**
 * Override, make sure Drupal doesn't return empty <P>
 *
 * Return a themed help message.
 *
 * @return a string containing the helptext for the current page.
 */
function touch_help() {
  $help = menu_get_active_help();
  // Drupal sometimes returns empty <p></p> so strip tags to check if empty
  if (strlen(strip_tags($help)) > 1) {
    return '<div class="help">'. $help .'</div>';
  }
}

/**
 * Override, use a better default breadcrumb separator.
 *
 * Return a themed breadcrumb trail.
 *
 * @param $breadcrumb
 *   An array containing the breadcrumb links.
 * @return a string containing the breadcrumb output.
 */
function touch_breadcrumb($breadcrumb) {
  if (count($breadcrumb) > 1) {
    $breadcrumb[] = drupal_get_title();
    return '<div class="breadcrumb">'. implode(' &rsaquo; ', $breadcrumb) .'</div>';
  }
}

/**
 * Rewrite of theme_form_element() to suppress ":" if the title ends with a punctuation mark.
 */
function touch_form_element($element, $value) {
  $args = func_get_args();
  return preg_replace('@([.!?]):\s*(</label>)@i', '$1$2', call_user_func_array('theme_form_element', $args));
}

/**
 * Set status messages to use Blueprint CSS classes.
 */
function touch_status_messages($display = NULL) {
  $output = '';
  foreach (drupal_get_messages($display) as $type => $messages) {
    // blueprint can either call this success or notice
    if ($type == 'status') {
      $type = 'success';
    }
    $output .= "<div class=\"messages $type\">\n";
    if (count($messages) > 1) {
      $output .= " <ul>\n";
      foreach ($messages as $message) {
        $output .= '  <li>'. $message ."</li>\n";
      }
      $output .= " </ul>\n";
    }
    else {
      $output .= $messages[0];
    }
    $output .= "</div>\n";
  }
  return $output;
}

/**
 * Override comment wrapper to show you must login to comment.
 */
function touch_comment_wrapper($content, $node) {
  global $user;
  $output = '';

  if ($node = menu_get_object()) {
    if ($node->type != 'forum') {
      $count = $node->comment_count .' '. format_plural($node->comment_count, 'comment', 'comments');
      $count = ($count > 0) ? $count : 'No comments';
      $output .= '<h3 id="comment-number">'. $count .'</h3>';
    }
  }

  $output .= '<div id="comments">';
  $msg = '';
  if (!user_access('post comments')) {
    $dest = 'destination='. $_GET['q'] .'#comment-form';
    $msg = '<div id="messages"><div class="error-wrapper"><div class="messages error">'. t('Please <a href="!register">register</a> or <a href="!login">login</a> to post a comment.', array('!register' => url("user/register", array('query' => $dest)), '!login' => url('user', array('query' => $dest)))) .'</div></div></div>';
  }
  $output .= $content;
  $output .= $msg;

  return $output .'</div>';
}


/**
 * Trim a post to a certain number of characters, removing all HTML.
 */
function touch_trim_text($text, $length = 150) {
  // remove any HTML or line breaks so these don't appear in the text
  $text = trim(str_replace(array("\n", "\r"), ' ', strip_tags($text)));
  $text = trim(substr($text, 0, $length));
  $lastchar = substr($text, -1, 1);
  // check to see if the last character in the title is a non-alphanumeric character, except for ? or !
  // if it is strip it off so you don't get strange looking titles
  if (preg_match('/[^0-9A-Za-z\!\?]/', $lastchar)) {
    $text = substr($text, 0, -1);
  }
  // ? and ! are ok to end a title with since they make sense
  if ($lastchar != '!' && $lastchar != '?') {
    $text .= '...';
  }
  return $text;
}


function touch_icon_picture($image, $size = '65x65') {

  if ($image && file_exists($image)) {
    switch($size) {
      case '100x100':
        $maxsize_icon = array('w'=> 100, 'h'=> 100);
        $info = image_get_info($image);
        if ($info['height'] < $maxsize_icon['h']) {
          $maxsize_icon['h'] = $info['height'];
        }
        if ($info['width'] < $maxsize_icon['w']) {
          $maxsize_icon['w'] = $info['width'];
        }
        $newpicture = dirname($image) . '/picture-logo.' . $info['extension'];
        if (!file_exists($newpicture) || 
          (filectime($newpicture) < filectime($image))) {
          image_scale($image, $newpicture, $maxsize_icon['w'],
            $maxsize_icon['h']);
        }
        $picture = file_create_url($newpicture);
        break;

      case '65x65':
        $maxsize_tile = array('w'=> 65, 'h'=> 65);
        $info = image_get_info($image);
        $newpicture = dirname($image) . '/picture-logo-small' . '.' . $info['extension'];
        if (!file_exists($newpicture) || 
          (filectime($newpicture) < filectime($image))) {
          image_scale($image, $newpicture, $newpicture, 
            $maxsize_tile['w'], $maxsize_tile['h']);
        }
        $picture = file_create_url($newpicture);
        break;

      default:
        $picture = file_create_url($image);
        break;
    }
  }

}
