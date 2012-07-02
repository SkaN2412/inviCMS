<?php
$templater->load('footer');
$vars = array(
	'sitename' => $_config->get("sitename"),
	'sitedesc' => $_config->get("sitedesc")
);
print($templater->parse($vars));
