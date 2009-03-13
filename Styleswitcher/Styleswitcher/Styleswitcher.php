<?php
require_once('StyleSet.php');
require_once('Style.php');

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

  /**
   * Flag: Accept "query" style (or GET) input values
   * @var mixed
   */
  public $acceptQuery;

  /**
   * Flag: Accept POST style input values
   * @var mixed
   */
  public $acceptPost;

  /**
   * Flag: Bounce to the referring page (and not the default)
   * @var mixed
   */
  public $bounceToReferer;

  /**
   * When the styleswitcher is created, it will load $_COOKIE here.
   * 
   * @var array
   */
  public $cookie;
  
  /**
   * The domain for all cookies set
   * @var mixed
   */
  public $cookieDomain;

  /**
   * The name for the cookies
   * @var mixed
   */
  public $cookieName = "sitestyle";

  /**
   * The default page that the Styleswitcher bounces users to
   * @var mixed
   */
  public $home;

  /**
   * Array of style sets
   * @var mixed
   */
  public $sets;

  /**
   * The complete list of styles
   * @var mixed
   */
  public $styleSet;

  /**
   * The default style variable (used in POST or GET requests)
   * @var mixed
   */
  public $styleVariable;

  public $switcher = 'switcher.php';
  
  /**
   * The input name for link()
   */
  public $styleLink = 'style_add';
  
  /**
   * Flag: include type="text/css"
   * @var mixed
   */
  public $includeType;

  /**
   *
   */
  public function __construct($home="", $domain=""){
    // Check that we have all the classes we need
    if(!class_exists("StyleSet") || !class_exists("Style")){
      throw new Exception('The StyleSet and/or Style classes to not exist');
    }

    // Initalize
    $this->styleSet = new StyleSet();
    $this->acceptPost = true;
    $this->acceptQuery = true;
    $this->bounceToReferer = $this->bounceToReferrer = true;
    $this->cookieDomain = $domain;
    $this->home = $home;
    $this->styleVariable = "set";
    $this->sets = array();
    $this->includeType = true;
    $this->cookie = $_COOKIE;
  }
  
  /**
   * Create a new Style object and add it to the internal style set
   *
   * @param string $style
   * @param string $file
   * @param string $media
   * @param string $title
   * @param bool $static
   * @see StyleSet::addStyle()
   */
  public function addStyle($style, $file="", $media="", $title="", $static=false){
    return $this->styleSet->addStyle($style, $file, $media, $title, $static);
  }
  
  /**
   * Add a
   */
  public function addStyleToSet($setName, $style, $default=false){
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
  
  /**
   * 
   */
  public function addToCookieArray(&$cookie, $style){
    // if its already there, do nothing
    if(in_array($style, $cookie)){ return; }

    $new = array();
    $set = $this->getSetName($style);
    $setFound = false;
    foreach($cookie as $c){
      $thisSet = $this->getSetName($c);
      if($thisSet != $set){
        // different set, so add the existing value
        array_push($new, $c);
      }
      else {
        $setFound = true;
        // same set, overwrite the existing value
        array_push($new, $style);
      }
    }
    $cookie = $new;
    
    if(!$setFound){
      array_push($cookie, $style);
    }
  }
  
  /**
   * Send the user to the URL
   * @param string $r 
   */
  public function bounce($r){
    header("Location: $r");
    exit;
  }
  
  /**
   * Send the user to the URL
   * @param string $r 
   * @deprecated
   * @see bounce()
   */
  public function _bounce($r){
    return $this->bounce($r);
  }
  
  /**
   * Create a new style set for this switcher
   * @param string $setName
   * @param array $setItems Optional array of styles for the set 
   */
  public function createSet($setName, $setItems=null){
    $this->sets[$setName] = new StyleSet("", $setName);
    if(is_array($setItems)){
      foreach($setItems as $item){
        $this->sets[$setName]->addStyle($item);
      }
    }
  }
  
  /**
   * Find the requested style
   * 
   * Returns false if the style could not be found
   * 
   * @param string $style
   * @return mixed 
   */
  public function findStyle($style){
    foreach(array_keys($this->sets) as $set){
      if($this->sets[$set]->getStyle($style)){
        return $this->sets[$set]->getStyle($style);
      }
    }
    return false;
  }
  
  /**
   * Get the style for this request (e.g., from the cookies)
   * 
   * If a single style is set, then that style is returned. If more than one style is selected,
   * an array of those styles is returned.
   * 
   * @return mixed
   */
  public function getStyle(){
    $site = "";
    if(isset($this->cookie[$this->cookieName])){
      if($this->cookie[$this->cookieName] !== null){
        $site = $this->cookie[$this->cookieName];
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
  
  /**
   * Get the style for this request (e.g., from the cookies)
   * 
   * If a single style is set, then that style is returned. If more than one style is selected,
   * an array of those styles is returned.
   * 
   * @return mixed
   */
  public function _getStyle(){
    return $this->getStyle();
  }
  
  /**
   * Get the value for the style cookie
   * 
   * This does not actually set the cookie value, it is just responsible for
   * figuring out what the cookie value should be, taking into account all of the options
   * (set values, cookie values, input values).
   * 
   * @param array $inputs Optional array of inputs to consider when creating the value 
   */
  public function getStyleCookie($inputs=array()){
    $cookie = array();
    
    foreach($this->sets as $set){
      $this->addToCookieArray($cookie, $set->getCurrentStyle());
    }
    
    // get the previously set style
    $style = $this->getStyle();
    if($style instanceof Style){
      $set = $this->getSetName($style);
      if($set){
        $this->sets[$set]->set = $style->name;
      }
      $this->addToCookieArray($cookie, $style->name);
    }
    elseif(is_array($style)) {
      foreach($style as $s){
        $set = $this->getSetName($s);
        if($set){
          $this->sets[$set]->set = $s->name;
        }
        $this->addToCookieArray($cookie, $s->name);
      }
    }
    
    // set the cookie to the current selection
    foreach($inputs as $i){
      $this->addToCookieArray($cookie, $i);
      if($this->inSet($i)){
        $set = $this->getSetName($i);
        if($set){
          $this->sets[$set]->set = $i;
        }
      }
    }
    return rtrim(implode('+', $cookie), '+');
  }
  
  /**
   * Get the set name for the given style
   * 
   * Returns false if the style is not part of a style
   * 
   * @param mixed $style
   * @return mixed
   */
  public function getSetName($style){
    foreach($this->sets as $set=>$v){
      // skip empty sets
      if($v->styles === null){ continue; }
      if($v->exists($style)){
        return $set;
      }
    }
    return false;
  }
  
  /**
   * Get the set name for the given style
   * 
   * Returns false if the style is not part of a style
   * 
   * @param mixed $style
   * @return mixed
   * @deprecated
   * @see getSetName()
   */
  public function _getSetName($style){
    return $this->getSetName($style);
  }
  
  /**
   * Tests if the style is in a set
   * @param string $style 
   * @return bool
   */
  public function inSet($style){
    $set = $this->getSetName($style);
    if($set !== false){ return true; }
    return false;
  }
  
  /**
   * Tests if the style is in a set
   * @param string $style 
   * @return bool
   * @deprecated
   * @see inSet()
   */
  public function _inSet($style){
    return $this->inSet($style);
  }
  
  /**
   * Create an HTML link for the styleswitcher
   * @param string $style
   * @return string
   */
  public function link($style){
    $s = $this->findStyle($style);
    if(!$s){ return; }
    
    $sname = $style;
    if($s->title){ $sname = $s->title; }
    
    print '<a href="'. $this->switcher .'?'. $this->styleLink .'='. urlencode($style) .'">'. $sname .'</a>';
  }
  
  /*
    printAlternateStyles()
  */
  public function printAlternateStyles($printAll=true){
    if($printAll){
      foreach($this->styleSet->styles as $s){
        if(!$s->static){ $this->printLink($s, true); }
      }
    }
  }
  
  /**
   * Craft the CSS link for the given style
   * 
   * @param Style $style
   * @param bool $alt
   */
  public function printLink($style, $alt=false){
    if($style instanceof Style){
      if($style->file == ""){ return false; }
      print "<link rel=\"";
      if($alt){ print "alternate stylesheet"; }
      else { print "stylesheet"; }
      print "\" href=\"". $style->file ."\" ";
      if($style->title && $alt){ print "title=\"". $style->title ."\" "; }
      elseif(!$style->title && $alt){ print "title=\"". $style->file ."\" "; }
      if(!$alt){
        if($style->media){ print "media=\"". $style->media ."\" "; }
      }
      if($this->includeType){ print "type=\"text/css\" "; }
      print "/>\n";
      return true;
    }
    return false;
  }
  
  /**
   * Craft the CSS link for the given style
   * 
   * @param Style $style
   * @param bool $alt
   * @deprecated
   * @see printLink()
   */
  public function _printLink($style, $alt=false){
    return $this->printLink($style, $alt);
  }
  
  /*
    printSetInputChecked()
  */
  public function printSetInputChecked($set, $input){
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
            $styles = $this->getStyle();
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
  public function printSetStyles(){
    // Double check that all the "sets" have been "satisfied"
    foreach($this->sets as $name=>$set){
      if($set->set != ""){
        // This set has a chosen style
        $this->printLink($this->styleSet->getStyle($set->set));
      }
      else {
        // This set does not have a chose style, use the
        // default if it's available.
        if($set->default != ""){
          $this->printLink($this->styleSet->getStyle($set->default));
        }
      }
    }
  }
  
  /*
    printStyles()
  */
  public function printStyles($printAlt=true){
    $this->printStaticStyles();
    $this->printUserStyles();
    if($printAlt){ $this->printAlternateStyles(); }
  }
  
  /*
    printStaticStyles()
  */
  public function printStaticStyles(){
    foreach($this->styleSet->styles as $s){
      if($s->static){ $this->printLink($s); }
    }
  }
  
  /*
    printUserStyles()
  */
  public function printUserStyles(){
    $styles = $this->getStyle();
    if(!is_array($styles)){
      $styles = array($styles);
    }
    // More than one style has been passed in
    foreach($styles as $style){
      // Check if style exists
      if($this->styleSet->exists($style->name)){
        // Check if style is part of a set
        if($this->inSet($style)){
          // Get set name
          $set = $this->getSetName($style->name);
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
          $this->printLink($this->styleSet->getStyle($style->name));
        }
      }
    }
    $this->printSetStyles();
  }
  
  /**
   * Set the cookie for this request
   */
  public function setCookie($s){
    if(headers_sent()){
      print "<p><strong>Styleswitcher Error</strong><br />".
          "The HTTP headers have already been to the client. The style cookie could not be set.</p>";
      return false;
    }
    setcookie($this->cookieName, $s, time()+31536000, '/', $this->cookieDomain, '0');
    return true;
  }
  
  /**
   * Set the cookie for this request
   * @deprecated
   * @see setCookie()
   */
  public function _setCookie($s){
    return $this->setCookie($s);
  }
  
  /*
    setHome()
  */
  public function setHome($home){
    if(!is_string($home)){ return false; }
    $this->home = $home;
    return true;
  }
  
  /**
   * Process the switcher request
   */
  public function start(){
    // Check for info on what form information to accept
    $referer = "";
    $inputs = array();
    
    if($this->acceptPost){ // post information
      $this->updateInputs($_POST, $inputs);
    }
    
    if($this->acceptQuery){ // query information
      $this->updateInputs($_GET, $inputs);
    }
    
    // Check that we grabbed the referer (if we need to)
    if(($this->bounceToReferer) && $referer == ""){
      if(isset($_SERVER['HTTP_REFERER'])){
        $referer = $_SERVER['HTTP_REFERER'];
      }
    }
    
    // Decide what cookie to set
    $cookie = $this->getStyleCookie($inputs);
    
    // Set the cookie
    if($cookie != ""){
      // Remove trailing "+" from the cookie string
      $cookie = rtrim($cookie, '+');
      $this->setCookie($cookie);
    }
    $this->bounce($referer);
  }
  
  /**
   * Test if the style cookie has been set
   * @return bool
   */
  public function styleCookieSet(){
    if(isset($_COOKIE[$this->cookieName])){
      return true;
    }
    return false;
  }
  
  /**
   * Set the path (URL) to the switcher
   * @param string $path
   */
  public function switcherPath($path){
    $this->switcher = $path;
  }
  
  /**
   * Update the inputs array with information from the given array
   * 
   * This method is used to reduce the $_GET and $_POST array inputs into a single input array
   * 
   * Note, this method modifies $inputs
   * 
   * @param array $array The source array
   * @param array $inputs The inputs array
   */
  public function updateInputs(&$array, &$inputs){
    foreach($array as $name=>$value){
      if(strpos($name, "inputStyle") !== false){
        if(isset($array[$value])){
          $inputs[] = $array[$value];
        }
      }
      else if($name == "inputReferer" || $name == "inputreferer"){
        // Use the sent referer
        $referer = $array[$value];
      }
      else if($name == "referer" || $name == "ref"){ $referer = $value; }
    }
    if(isset($array[$this->styleVariable])){
      $inputs[] = $array[$this->styleVariable];
    }
    if(isset($array[$this->styleLink])){
      $inputs[] = $array[$this->styleLink];
    }
  }
}
?>
