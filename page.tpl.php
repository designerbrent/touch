<?php // $Id: page.tpl.php,v 1.15.4.7 2008/12/23 03:40:02 designerbrent Exp $ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $language->language ?>" lang="<?php print $language->language ?>">
<head>
	<title><?php print $head_title ?></title>
	<meta http-equiv="content-language" content="<?php print $language->language ?>" />
	<?php print $meta; ?>
  <?php print $head; ?>
  <?php print $styles; ?>

</head>

<body class="<?php print $body_classes; ?>">
  
  <div id="header">
    <?php if ($logo || $site_name): ?>
      <?php if ($logo): ?>
        <div id="logo">
         <a title="<?php print $site_name; ?><?php if ($site_slogan != '') print ' &ndash; '. $site_slogan; ?>" href="<?php print url(); ?>"><img src="<?php print($logo) ?>" alt="<?php print $site_name; ?>" border="0" /></a>
        </div>
      <?php endif ?>
       <h1 id="site_name" class="<?php if ($logo) print ' logo '; ?>">
         <a title="<?php print $site_name; ?><?php if ($site_slogan != '') print ' &ndash; '. $site_slogan; ?>" href="<?php print url(); ?>"><?php print $site_name; ?><?php if ($site_slogan != '') print ' &ndash; '. $site_slogan; ?></a>
       </h1>
     <?php endif ?>
    <?php print $header; ?>
    <?php if (isset($primary_links)) : ?>
      <?php print $primary_links; ?>
    <?php endif; ?>
  </div>

<div class="container">

  <div class="<?php print $main_classes; ?>">
    <?php
      if ($breadcrumb != '') {
        print $breadcrumb;
      }

      if ($tabs != '') {
        print '<div class="tabs">'. $tabs .'</div>';
      }

      if ($messages != '') {
        print '<div id="messages">'. $messages .'</div>';
      }
      
      if ($title != '') {
        print '<h2>'. $title .'</h2>';
      }      

      print $help; // Drupal already wraps this one in a class      

      print $content;
      print $feed_icons;
    ?>
    <?php if ($blocks): ?>
      <div class="<?php print $blocks_classes; ?>"><?php print $blocks; ?></div>
    <?php endif ?>

    <?php if ($footer_message | $footer): ?>
      <div id="footer" class="clear">
        <?php if ($footer): ?>
          <?php print $footer; ?>
        <?php endif; ?>
        <?php if ($footer_message): ?>
          <div id="footer-message"><?php print $footer_message; ?></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>



  <?php print $scripts ?>
  <?php print $closure; ?>

</div>

</body>
</html>
