<?php
/**
 * @package Styleswitcher
 */
require_once('Styleswitcher/Style.php');
require_once('Styleswitcher/StyleSet.php');
require_once('Styleswitcher/Styleswitcher.php');

/**
 * This was a PHP 4 compatibility function, but in PHP 5 we just use isset()
 * 
 * This is scheduled to be removed in version 3
 * 
 * @deprecated
 */
function cwssArrayKeyExists($key, $array){
  if(function_exists("array_key_exists")){
    return array_key_exists($key, $array);
  }
  else if(function_exists("key_exists")){
    return key_exists($key, $array);
  }
  else {
    return in_array($key, array_keys($search)); 
  }
}
?>