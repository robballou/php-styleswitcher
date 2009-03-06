<?php
$ss = new Styleswitcher();

$ss->switcherPath('/switcher/switcher.php');
$ss->addStyle("basic", "/switcher/c/basic.css", "", "", true);

$ss->addStyle("normal", "/switcher/c/normal.css", '', 'Normal');
$ss->addStyle("high", "/switcher/c/high.css", '', 'High Contrast');
$ss->addStyle("large", "/switcher/c/large.css", '', 'Large Text');
$ss->addStyle("small", "/switcher/c/small.css", '', 'Small Text');

$ss->createSet("fonts");
$ss->addStyleToSet("fonts", "large");
$ss->addStyleToSet("fonts", "small", true);

$ss->createSet("style");
$ss->addStyleToSet("style", "normal", true);
$ss->addStyleToSet("style", "high");

$ss->cookieName = "styles";

?>