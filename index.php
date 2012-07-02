<?php
/*
 * inviEngine 5.3
 * 5.0 What's new:
 *   custom work directory. You can set work directory via GET param "wd". Default is "./"
 * 5.2 What's new:
 *   fixed defining main page, when wd!=./
 *   fixed displaying error, when defining WD constant
 * 5.2.1 What's new:
 *   some bugfixes
 * @author: Andrey Kamozin
 */

define("DS", DIRECTORY_SEPARATOR);
//Defining work directory
if (isset($_GET['wd']) && is_dir($_GET['wd']))
{ //If there's wd GET param & dir exists, define WD with $_GET['wd']
	if ($_GET['wd'][strlen($_GET['wd'])-1] != DS)
	{ //If $_GET['wd'] doesn't end with DS, WD = $_GET['wd'] . DS
		define("WD", $_GET['wd'].DS);
	} else {
		define("WD", $_GET['wd']);
	}
} else {
	define("WD", ".".DS);
}

if (WD != ".".DS)
{ //If WD isn't root directory, attach system files from root dir
	$modules = scandir(".".DS."modules");
	for ($i=0; $i<count($modules); $i++)
	{ //Match scanned files & include them
		if ($modules[$i] == "." || $modules[$i] == "..") continue;
		preg_match("|([^А-Яа-я]+).module.php|", $modules[$i], $match);
		if ($match != array())
		{
			require_once(".".DS."modules".DS.$match[1].".module.php");
		}
	}
	$includes = scandir(".".DS."includes");
	for ($i=0; $i<count($includes); $i++)
	{
		if ($includes[$i] == "." || $includes[$i] == "..") continue;
		preg_match("|([^А-Яа-я]+).php|", $includes[$i], $match);
		if ($match != array())
		{
			include_once(".".DS."includes".DS.$match[1].".php");
		}
	}
}
//Attaching system files from WD
$modules = scandir(WD."modules");
for ($i=0; $i<count($modules); $i++)
{
	if ($modules[$i] == "." || $modules[$i] == "..") continue;
	preg_match("|([^А-Яа-я]+).module.php|", $modules[$i], $match);
	if ($match != array())
	{
		require_once(WD."modules".DS.$match[1].".module.php");
	}
}
$includes = scandir(WD."includes");
for ($i=0; $i<count($includes); $i++)
{
	if ($includes[$i] == "." || $includes[$i] == "..") continue;
	preg_match("|([^А-Яа-я]+).php|", $includes[$i], $match);
	if ($match != array())
	{
		include_once(WD."includes".DS.$match[1].".php");
	}
}
unset($includes, $modules, $i, $match);
//Know, which page to open
if (isset($_GET['id']))
{ //If there's id GET param, page is $_GET['id']
	$pageid = $_GET['id'];
} else { //Else, watch config
	if (isset($_GET['wd']))
	{
		$mp = $_GET['wd']."_main_page";
	} else {
		$mp = "main_page";
	}
	$pageid = $_config->get($mp);
}
//Include page
if (in_array($pageid, $accepted_list) && file_exists(WD."pages".DS.$pageid.".php"))
{ //If page's accepted & page exists, include it
	include(WD."pages".DS.$pageid.".php");
} else { //Else include 404 page
	include(WD."pages".DS."404.php");
}
?>