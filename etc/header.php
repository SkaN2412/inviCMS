<?php
$templater->load('header');
$vars = array(
	'title' => $title,
	'sitename' => $_config->get("sitename"),
	'sitedesc' => $_config->get("sitedesc")
);
switch ($_reg->is_authed())
{
	case true:
		$data = $_reg->getuserdata();
		$vars['username'] = $data['name'];
		$vars['usergroup'] = $data['group'];
		break;
	case false:
		$vars['username'] = "false";
		$vars['usergroup'] = "users";
		break;
}
print($templater->parse($vars)); ?>
