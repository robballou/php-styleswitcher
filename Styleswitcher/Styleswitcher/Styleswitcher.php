<?php
/*=======================================================
  PHP Styleswitcher
  Version 2.03 (PHP5)
  
  This code implements a simple PHP stylesheet switcher.
  Unlike version 1, this code is more roboust and much
  cleaner. Please refer to the enclosed documentation or
  to the documentation on the web site about how to use
  this code.
  
  Coded By Rob Ballou (rob.ballou@gmail.com)
  Last Updated: 2009-03-03
  
  Styleswitcher web site:
  http://robballou.com/switcher/
=========================================================*/
/**
 * Styleswitcher class
 * 
 * This is the main class out of the three. This handles
 * most all of the user interaction and the tasks a user
 * needs to perform (setting the cookie, reading the cookie,
 * writing the stylesheet information).
 * 
 * Requires:
 * - StyleSet class
 * - Style class
 * 
 * @package Styleswitcher
 */
class Styleswitcher {
  /*---------------------------------------------------
    Fields
  -----------------------------------------------------*/
  var $acceptQuery;       // Flag: Accept "query" style (or GET) input values
  var $acceptPost;        // Flag: Accept POST style input values
  var $bounceToReferer;   // Flag: Bounce to the referring page (and not the default)
  var $cookieDomain;      // The domain for all cookies set
  var $cookieName;        // The name for the cookies
  var $home;              // The default page that the Styleswitcher bounces users to
  var $sets;              // Array of style sets
  var $styleSet;          // The complete list of styles
  var $styleVariable;     // The default style variable (used in POST or GET requests)
  var $includeType;       // Flag: include type="text/css"

  /*---------------------------------------------------
    Constructor
  -----------------------------------------------------*/
  function Styleswitcher($home="", $domain=""){
    // Check that we have all the classes we need
    if(!class_exists("styleset") || !class_exists("style")){
      print "<p><strong>Styleswitcher Error</strong><br />".
          "All of the Styleswitcher classes need to be loaded. Be sure you have all the proper files.</p>";
      return;
    }

    // Initalize
    $this->styleSet = new StyleSet();
    $this->acceptPost = true;
    $this->acceptQuery = true;
    $this->bounceToReferer = $this->bounceToReferrer = true;
    $this->cookieDomain = $domain;
    $this->cookieName = "sitestyle";
    $this->home = $home;
    $this->styleVariable = "set";
    $this->sets = array();
    $this->includeType = true;
  }
  
  /*---------------------------------------------------
    Public Methods
  -----------------------------------------------------*/
  /*
    addStyle()
  */
  function addStyle($style, $file="", $media="", $title="", $static=false){
    return $this->styleSet->addStyle($style, $file, $media, $title, $static);
  }
  
  /*
    addStyleToSet()
  */
  function addStyleToSet($setName, $style, $default=false){
    if(isset($this->sets[$setName]) && $this->styleSet->exists($style)){
      $this->sets[$setName]->addStyle($this->styleSet->getStyle($style));
      if($default){ 
        $tmp = $this->styleSet->getStyle($style);
        $this->sets[$setName]->default = $tmp->name;
      }
      return true;
    }
    return false;
  }
  
  /*
    createSet()
  */
  function createSet($setName, $setItems=""){
    $this->sets[$setName] = new StyleSet("", $setName);
    if(is_array($setItems)){
      foreach($setItems as $item){
        $this->sets[$setName]->addStyle($item);
      }
    }
  }
  
  /*
    printAlternateStyles()
  */
  function printAlternateStyles($printAll=true){
    if($printAll){
      foreach($this->styleSet->styles as $s){
        if(!$s->static){ $this->_printLink($s, true); }
      }
    }
  }
  
  /*
    printSetInputChecked()
  */
  function printSetInputChecked($set, $input){
    if(isset($this->sets[$set])){
      // Set exists
      // Check that input name matches a style name
      if($this->sets[$set]->exists($input)){
        if($this->sets[$set]->set == $input){
          print " checked=\"checked\" ";
          return;
        }
        else if($this->sets[$set]->set == ""){
          if($this->sets[$set]->default == $input){
            print " checked=\"checked\" ";
            return;
          }
          else if($this->sets[$set]->default == ""){
            // Check for anything in the cookies to do with this set
            $styles = $this->_getStyle();
            if(is_array($styles)){
              foreach($styles as $s){
                if(is_string($s)){
                  if($s == $input){
                    print " checked=\"checked\" ";
                    return;
                  }
                }
                else if(get_class($s) == "style"){
                  if($s->name == $input){
                    print " checked=\"checked\" ";
                    return;
                  }
                }
              }
            }
            else if(get_class($styles) == "style"){
              if(is_string($styles)){
                if($styles == $input){
                  print " checked=\"checked\" ";
                  return;
                }
              }
              else if(get_class($styles) == "style"){
                if($styles->name == $input){
                  print " checked=\"checked\" ";
                  return;
                }
              }
            }
          }
        }
      }
    }
  }
  
  /*
    printSetStyles()
  */
  function printSetStyles(){
    // Double check that all the "sets" have been "satisfied"
    foreach($this->sets as $name=>$set){
      if($set->set != ""){
        // This set has a chosen style
        $this->_printLink($this->styleSet->getStyle($set->set));
      }
      else {
        // This set does not have a chose style, use the
        // default if it's available.
        if($set->default != ""){
          $this->_printLink($this->styleSet->getStyle($set->default));
        }
      }
    }  
  }
  
  /*
    printStyles()
  */
  function printStyles($printAlt=true){
    $this->printStaticStyles();
    $this->printUserStyles();
    if($printAlt){ $this->printAlternateStyles(); }
  }
  
  /*
    printStaticStyles()
  */
  function printStaticStyles(){
    foreach($this->styleSet->styles as $s){
      if($s->static){ $this->_printLink($s); }
    }
  }
  
  /*
    printUserStyles()
  */
  function printUserStyles(){
    $styles = $this->_getStyle();
    if(!is_array($styles)){
      $styles = array($styles);
    }
    // More than one style has been passed in
    foreach($styles as $style){
      // Check if style exists
      if($this->styleSet->exists($style->name)){
        // Check if style is part of a set
        if($this->_inSet($style)){
          // Get set name
          $set = $this->_getSetName($style->name);
          if($this->sets[$set]->set == ""){
            $this->sets[$set]->set = strval($style->name);
          }
          else {
            if($this->sets[$set]->set != $this->sets[$set]->default){
              $this->sets[$set]->set = strvale($style->name);
            }
          }
        }
        else {
          $this->_printLink($this->styleSet->getStyle($style->name));
        }
      }
    }
    $this->printSetStyles();
  }
  
  /*
    setHome()
  */
  function setHome($home){
    if(!is_string($home)){ return false; }
    $this->home = $home;
    return true;
  }
  
  /*
    start()
  */
  function start(){
    // Check for info on what form information to accept
    $referer = "";
    $inputs  = array();
    
    if($this->acceptPost){ // post information
      foreach($_POST as $name=>$value){
        if(strpos($name, "inputStyle") !== false){
          if(isset($_POST[$value])){
            $inputs[] = $_POST[$value];
          }
        }
        else if($name == "inputReferer" || $name == "inputreferer"){
          // Use the sent referer
          $referer = $_POST[$value];
        }
        else if($name == "referer" || $name == "ref"){ $referer = $value; }
      }
      if(isset($_POST[$this->styleVariable])){
        $inputs[] = $_POST[$this->styleVariable];
      }
    }
    
    if($this->acceptQuery){ // query information
      foreach($_GET as $name=>$value){
        if(strpos($name, "inputStyle") !== false){
          if(isset($_GET[$value])){
            $inputs[] = $_GET[$value];
          }
        }
        else if($name == "inputReferer" || $name == "inputreferer"){
          // Use the sent referer
          $referer = $_GET[$value];
        }
        else if($name == "referer" || $name == "ref"){ $referer = $value; }
      }
      if(isset($_GET[$this->styleVariable])){
        $inputs[] = $_GET[$this->styleVariable];
      }
    }
    
    // Check that we grabbed the referer (if we need to)
    if(($this->bounceToReferer) && $referer == ""){
      if(isset($_SERVER['HTTP_REFERER'])){
        $referer = $_SERVER['HTTP_REFERER'];
      }
    }
    
    // Decide what cookie to set
    $cookie = "";
    foreach($inputs as $i){
      if($this->_inSet($i)){ // Style is in a set
        $setName = $this->_getSetName($i);
        if($setName === false){ continue; }
        foreach($this->sets as $name=>$set){
          if($name == $setName){
            if($set->set == ""){ 
              $set->set  = $i;
              $cookie .= "$i+";
            }
            else {
              if($set->set != $set->default){
                $set->set  = $i;
                $cookie .= "$i+";
              }
            }
            break;
          }
        }
      }
      else {
        if($i != ""){
          $cookie .= "$i+";
        }
      }
    }
    
    // Set the cookie
    if($cookie != ""){
      // Remove trailing "+" from the cookie string
      $length = strlen($cookie);
      $cookie = substr($cookie, 0, $length-1);
      $this->_setCookie($cookie);
    }
    $this->_bounce($referer);
  }
  
  function styleCookieSet(){
    if(isset($_COOKIE[$this->cookieName])){
      return true;
    }
    return false;
  }
  
  /*---------------------------------------------------
    Private Methods
  -----------------------------------------------------*/
  function _bounce($r){
    header("Location: $r");
    exit;
  }
  
  function _getSetName($style){
    foreach($this->sets as $set=>$v){
      if($v->styles === null){ continue; }
      foreach($v->styles as $s){
        if(strtolower(get_class($style)) == "style"){
          if($s->name == $style->name){
            return $set;
          }
        }
        else {
          if($s->name == $style){
            return $set;
          }
        }
      }
    }
    return false;
  }
  
  function _getStyle(){
    $site = "";
    if(isset($_COOKIE[$this->cookieName])){
      if($_COOKIE[$this->cookieName] !== null){
        $site = $_COOKIE[$this->cookieName];
      }
    }
    // Check for multiple styles
    if(strpos($site, "+")!==false){
      $styles = explode("+", $site);
      $s = array();
      foreach($styles as $style){
        $s[] = new Style($style);
      }
      return $s;
    }
    return new Style($site);
  }
  
  function _inSet($style){
    if(is_object($style)){ $style = $style->name; }
    reset($this->sets);
    foreach($this->sets as $s){
      foreach($s->styles as $st){
        if($style == $st->name){ return true; }
      }
    }
    return false;
  }
  
  function _printLink($style, $alt=false){
    if($style instanceof Style){
      if($style->file == ""){ return false; }
      print "<link rel=\"";
      if($alt){ print "alternate stylesheet"; }
      else { print "stylesheet"; }
      print "\" href=\"". $style->file ."\" ";
      if($style->title){ print "title=\"". $style->title ."\" "; }
      if(!$alt){
        if($style->media){ print "media=\"". $style->media ."\" "; }
      }
      if($this->includeType){ print "type=\"text/css\" "; }
      print "/>\n";
      return true;
    }
    return false;
  }
  
  function _setCookie($s){
    if(headers_sent()){
      print "<p><strong>Styleswitcher Error</strong><br />".
          "The HTTP headers have already been to the client. The style cookie could not be set.</p>";
      return false;
    }
    setcookie($this->cookieName, $s, time()+31536000, '/', $this->cookieDomain, '0');
    return true;
  }
}
?>
