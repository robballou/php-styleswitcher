<?php
/**
 * Class for making sets of styles
 * 
 * @package Styleswitcher
 */
class StyleSet {
  /**
   * The style array
   * @var mixed
   */
  public $styles;
  
  /**
   * The default style choice for this set
   * @var mixed
   */
  public $default;
  
  /**
   * Holder for the style name to be used
   * @var mixed
   */
  public $set;
  
  /**
   * @var string
   */
  public $name;
  
  /**
   * Create a new style set
   */
  function __construct($default="", $name=""){
    $this->default = $default;
    $this->styles = array();
    $this->set = "";
    $this->name = $name;
  }
  
  /**
   * Add a new style to this set
   * @param mixed $style
   * @param string $file
   * @param string $media
   * @param string $title
   * @param bool $static
   * @return true
   */
  function addStyle($style, $file="", $media="", $title="", $static=false){
    if(!is_object($style)){
      $style = new Style($style, $file, $media, $title, $static);
    }
    
    if($this->exists($style->name)){
      $tmp = array();
      
      foreach($this->styles as $s){
        if($s->name == $style->name){
          array_push($tmp, $style);
        }
        else {
          array_push($tmp, $s);
        }
      }
      $this->styles = $tmp;
    }
    else {
      $this->styles[] = $style;
    }
    return true;
  }
  
  /**
   * Tests if the requested style exists in the set
   * @param mixed $style 
   * @return bool
   */
  function exists($style){
    if(is_object($style) && $style instanceof Style){
      $style = strval($style->name);
    }
    else if(is_object($style)){ return false; }
    foreach($this->styles as $s){
      if(strval($s->name) == $style){ return true; }
    }
    return false;
  }
  
  /**
   * Get the currently assigned style for the set
   * 
   * If no style has been set, then we will try to use the default
   */
  public function getCurrentStyle(){
    $s = $this->set;
    if($s == ''){ 
      $s = $this->default; 
      $this->set = $s;
    }
    return $s;
  }
  
  /**
   * Find a style in the set
   * 
   * Returns false if the style is not found
   * 
   * @param string $style
   * @return mixed 
   */
  function getStyle($style){
    foreach($this->styles as $s){
      if($s->name == $style){ return $s; }
    }
    return false;
  }

  /**
   * Get a list of style names in this set
   * @return string
   */
  function getStyleNames(){
    reset($this->styles);
    $names = "";
    foreach($this->styles as $s){
      $names .= $s->name .", ";
    }
    if($names != ""){ 
      if(function_exists('mb_substr')){$names = mb_substr($names, 0, -2); }
      else { $names = substr($names, 0, -2); }
    }
    return $names;
  }
}
?>