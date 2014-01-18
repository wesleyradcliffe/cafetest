<?php

require_once('apps/Config.php');

/* setup the configuration object */
$CFG = new Config();

$CFG->actualroot = dirname(__DIR__);
$CFG->dirroot = $CFG->actualroot . '/cafetest';



$CFG->baseurl = "http://" . ($_SERVER['HTTP_HOST'] ?: $CFG->host_name) . "/";

//DB SETUP - not needed

$CFG->libdir  = "$CFG->dirroot/apps";
$CFG->datadir = "$CFG->dirroot/data";



spl_autoload_register("classLoader");
function classLoader($class_name) {
	global $CFG;
	$filename = $CFG->libdir."/".($class_name).'.php';
	if(is_file($filename)){
		include_once $filename;
	}
}

