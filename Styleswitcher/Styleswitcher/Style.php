<?php
/**
 * A class that represents an individual stylesheet for the Styleswitcher
 * 
 * @package Styleswitcher
 */
class Style {
  
  /**
   * @var bool
   */
  public $active = false;
  
  /**
   * Name for this style
   * 
   * This is used internally for ID-ing this style
   * 
   * @var string
   */
  public $name = '';
  
  /**
   * The URL for the file
   * 
   * This can either be relative or absolute
   * 
   * @var string
   */
  public $file = '';
  
  /**
   * The media type for the style
   * @var string
   */
  public $media = '';
  
  /**
   * Whether or not this is a static style
   * 
   * Static styles are always included as "activated"
   * 
   * @var bool
   */
  public $static = false;
  
  /**
   * The title attribute for this style
   * @var string
   */
  public $title = '';

  /**
   * Create a new style
   * 
   * @see Styleswitcher::addStyle()
   */
  public function __construct($name, $file="", $media="", $title="", $static=false){
    $this->name = $name;
    $this->file = $file;
    $this->media = $media;
    $this->static = $static;
    $this->title  = $title;
  }
  
  public function __toString(){
    return $this->name;
  }
}
?>