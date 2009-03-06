<?php
if(isset($_ENV['SIMPLETEST_PATH'])){
  ini_set('include_path', $_ENV['SIMPLETEST_PATH'] . PATH_SEPARATOR . ini_get('include_path'));
}

require_once("simpletest/unit_tester.php");
require_once("simpletest/reporter.php");
require_once('Styleswitcher.php');

class TestStyleswitcher extends UnitTestCase {
  public function setup(){
    $this->ss = new Styleswitcher();
    $this->ss->addStyle("basic", "basic.css", "", "", true);
    $this->ss->addStyle("normal", "normal.css", '', 'Normal');
    $this->ss->addStyle("high", "high.css", '', 'High Contrast');
    $this->ss->addStyle("large", "large.css", '', 'Large Text');
    $this->ss->addStyle("small", "small.css", '', 'Small Text');
    $this->ss->createSet("fonts");
    $this->ss->addStyleToSet("fonts", "large");
    $this->ss->addStyleToSet("fonts", "small", true);
    $this->ss->createSet("style");
    $this->ss->addStyleToSet("style", "normal", true);
    $this->ss->addStyleToSet("style", "high");
  }
  
  public function testAddToCookieArray(){
    $cookie = array();
    $this->ss->addToCookieArray($cookie, 'small');
    $this->assertEqual(implode('+', $cookie), 'small');
    
    $this->ss->addToCookieArray($cookie, 'large');
    $this->assertEqual(implode('+', $cookie), 'large');
    
    $this->ss->addToCookieArray($cookie, 'normal');
    $this->assertEqual(implode('+', $cookie), 'large+normal');
  }
  
  public function testGetStyleCookie(){
    // by default the method should return the default set styles
    $this->assertEqual($this->ss->getStyleCookie(), 'small+normal');
    $this->assertEqual($this->ss->sets['fonts']->set, 'small');
    $this->assertEqual($this->ss->sets['style']->set, 'normal');
    
    // set the previously existing styles
    $this->ss->cookie = array('sitestyle' => 'small+high');
    $this->assertEqual($this->ss->getStyleCookie(), 'small+high');
    $this->assertEqual($this->ss->sets['fonts']->set, 'small');
    $this->assertEqual($this->ss->sets['style']->set, 'high');
    
    // set a new value via an input
    $this->assertEqual($this->ss->getStyleCookie(array('large')), 'large+high');
    $this->assertEqual($this->ss->sets['fonts']->set, 'large');
    $this->assertEqual($this->ss->sets['style']->set, 'high');
    
    $this->ss->cookie = array('sitestyle' => 'large+normal');
    $this->assertEqual($this->ss->getStyleCookie(array('high')), 'large+high');
    $this->assertEqual($this->ss->sets['fonts']->set, 'large');
    $this->assertEqual($this->ss->sets['style']->set, 'high');
  }
}

$test = &new TestStyleswitcher();
$test->run(new TextReporter());

?>