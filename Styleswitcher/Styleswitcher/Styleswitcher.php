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
    $set = $this->_getSetName($style);
    $setFound = false;
    foreach($cookie as $c){
      $thisSet = $this->_getSetName($c);
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
  
  /*
    createSet()
  */
  public function createSet($setName, $setItems=""){
    $this->sets[$setName] = new StyleSet("", $setName);
    if(is_array($setItems)){
      foreach($setItems as $item){
        $this->sets[$setName]->addStyle($item);
      }
    }
  }
  
  public function findStyle($style){
    foreach(array_keys($this->sets) as $set){
      if($this->sets[$set]->getStyle($style)){
        return $this->sets[$set]->getStyle($style);
      }
    }
    return false;
  }
  
  /**
   * 
   */
  public function getStyleCookie($inputs=array()){
    $cookie = array();
    
    foreach($this->sets as $set){
      $this->addToCookieArray($cookie, $set->getCurrentStyle());
    }
    
    // get the previously set style
    $style = $this->_getStyle();
    if($style instanceof Style){
      $set = $this->_getSetName($style);
      if($set){
        $this->sets[$set]->set = $style->name;
      }
      $this->addToCookieArray($cookie, $style->name);
    }
    elseif(is_array($style)) {
      foreach($style as $s){
        $set = $this->_getSetName($s);
        if($set){
          $this->sets[$set]->set = $s->name;
        }
        $this->addToCookieArray($cookie, $s->name);
      }
    }
    
    // set the cookie to the current selection
    foreach($inputs as $i){
      $this->addToCookieArray($cookie, $i);
      if($this->_inSet($i)){
        $set = $this->_getSetName($i);
        if($set){
          $this->sets[$set]->set = $i;
        }
      }
    }
    return rtrim(implode('+', $cookie), '+');
  }
  
  /**
   * @param string $style
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
        if(!$s->static){ $this->_printLink($s, true); }
      }
    }
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
  public function printSetStyles(){
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
      if($s->static){ $this->_printLink($s); }
    }
  }
  
  /*
    printUserStyles()
  */
  public function printUserStyles(){
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
  public function setHome($home){
    if(!is_string($home)){ return false; }
    $this->home = $home;
    return true;
  }
  
  /*
    start()
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
      $this->_setCookie($cookie);
    }
    $this->_bounce($referer);
  }
  
  public function styleCookieSet(){
    if(isset($_COOKIE[$this->cookieName])){
      return true;
    }
    return false;
  }
  
  public function switcherPath($path){
    $this->switcher = $path;
  }
  
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
  
  protected function _bounce($r){
    header("Location: $r");
    exit;
  }
  
  protected function _getSetName($style){
    foreach($this->sets as $set=>$v){
      if($v->styles === null){ continue; }
      foreach($v->styles as $s){
        if($style instanceof Style){
          if($s->name == $style->name){
            return $set;
          }
        }
        else {
          if($s->name == $style){ return $set; }
        }
      }
    }
    return false;
  }

  /**
   * Get the style for this request
   */
  protected function _getStyle(){
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
  
  protected function _inSet($style){
    if(is_object($style)){ $style = $style->name; }
    reset($this->sets);
    foreach($this->sets as $s){
      foreach($s->styles as $st){
        if($style == $st->name){ return true; }
      }
    }
    return false;
  }
  
  protected function _printLink($style, $alt=false){
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
  
  protected function _setCookie($s){
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
