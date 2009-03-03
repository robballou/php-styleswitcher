<?php
require_once("Styleswitcher.php");

$ss = new Styleswitcher('/switcher/v2/');

$ss->addStyle("blue", "blue.css");
$ss->addStyle("green", "green.css");
$ss->addStyle("large", "large.css");
$ss->addStyle("normal", "small.css");

$ss->createSet("fonts");
$ss->addStyleToSet("fonts", "large");
$ss->addStyleToSet("fonts", "normal", true);

$ss->createSet("style");
$ss->addStyleToSet("style", "blue", true);
$ss->addStyleToSet("style", "green");

$ss->cookieDomain = ".robballou.com";
$ss->cookieName = "cwStyle";

$ss->start();
?>
