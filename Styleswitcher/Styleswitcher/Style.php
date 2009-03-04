<?php
/**
 * A class that represents an individual stylesheet for the Styleswitcher
 * 
 * @package Styleswitcher
 */
class Style {
  /**
   * Name for this style
   * 
   * This is used internally for ID-ing this style
   * 
   * @var string
   */
  var $name = '';
  
  /**
   * The URL for the file
   * 
   * This can either be relative or absolute
   * 
   * @var string
   */
  var $file = '';
  
  /**
   * The media type for the style
   * @var string
   */
  var $media = '';
  
  /**
   * Whether or not this is a static style
   * 
   * Static styles are always included as "activated"
   * 
   * @var bool
   */
  var $static = false;
  
  /**
   * The title attribute for this style
   * @var string
   */
  var $title = '';

  /**
   * Create a new style
   * 
   * @see Styleswitcher::addStyle()
   */
  public function __construct__($name, $file="", $media="", $title="", $static=false){
    $this->name = $name;
    $this->file = $file;
    $this->media = $media;
    $this->static = $static;
    $this->title  = $title;
  }
}
?>