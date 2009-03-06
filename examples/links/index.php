<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Style switcher</title>
  <?php
  require('Styleswitcher.php');
  include('inc_switcher.php');
  $ss->printStyles();
  ?>
</head>
<body>
  <h1>My Page</h1>
  
  <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

  <p><strong>Font size:</strong> <?php $ss->link('small'); ?>, <?php $ss->link('large'); ?></p>
  <p><strong>Layout:</strong> <?php $ss->link('normal'); ?>, <?php $ss->link('high'); ?></p>
  
</body>
</html>