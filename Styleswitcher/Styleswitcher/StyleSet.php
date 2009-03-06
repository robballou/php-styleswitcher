<?php
/*-------------------------------------------------------
  StyleSet class
---------------------------------------------------------*/
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
   * 
   */
  function __construct($default="", $name=""){
    $this->default = $default;
    $this->styles = array();
    $this->set = "";
    $this->name = $name;
  }
  
  /**
   * 
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
  
  /*
    exists()
  */
  function exists($style){
    if(is_object($style) && strtolower(get_class($style)) == "style"){
      $style = strval($style->name);
    }
    else if(is_object($style)){ return false; }
    foreach($this->styles as $s){
      if(strval($s->name) == $style){ return true; }
    }
    return false;
  }
  
  public function getCurrentStyle(){
    $s = $this->set;
    if($s == ''){ 
      $s = $this->default; 
      $this->set = $s;
    }
    return $s;
  }
  
  /*
    getStyle()
  */
  function getStyle($style){
    foreach($this->styles as $s){
      if($s->name == $style){ return $s; }
    }
    return false;
  }

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