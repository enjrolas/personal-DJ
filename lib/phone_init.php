<?php
/*

 Copyright (c) 2001 - 2007 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/

/***
 * DO NOT EDIT THIS FILE 
 ***/

// Set the Error level manualy... I'm to lazy to fix notices
error_reporting(E_ALL ^ E_NOTICE);

// This makes this file nolonger need customization
// the config file is in the same dir as this (init.php) file.
$ampache_path = dirname(__FILE__);
$prefix = realpath($ampache_path . "/../");
$configfile = "$prefix/config/ampache.cfg.php";
require_once($prefix . "/lib/general.lib.php");

/*
 Check to see if this is Http or https
*/
if ($_SERVER['HTTPS'] == 'on') { 
	$http_type = "https://";
}
else { 
	$http_type = "http://";
}

/*
 See if the Config File Exists if it doesn't
 then go ahead and move them over to the install
 script
*/
if (!file_exists($configfile)) { 
        $path = preg_replace("/(.*)\/(\w+\.php)$/","\${1}", $_SERVER['PHP_SELF']);
	$link = $http_type . $_SERVER['HTTP_HOST'] . $path . "/install.php";
	header ("Location: $link");
	exit();
}

/* 
 Try to read the config file, if it fails give them
 an explanation
*/
if (!$results = read_config($configfile,0)) {
	$path = preg_replace("/(.*)\/(\w+\.php)$/","\${1}", $_SERVER['PHP_SELF']);
	$link = $http_type . $_SERVER['HTTP_HOST'] . $path . "/test.php";
	header ("Location: $link");
	exit();
} 

/** This is the version.... fluf nothing more... **/
$results['version']		= '3.3.3.5';
$results['int_config_version']	= '3'; 

$results['raw_web_path']	= $results['web_path'];
$results['web_path']		= $http_type . $_SERVER['HTTP_HOST'] . $results['web_path'];
$results['http_port']		= $_SERVER['SERVER_PORT'];
$results['prefix'] = $prefix;
$results['stop_auth'] = $results['prefix'] . "/modules/vauth/gone.fishing";
if (!$results['http_port']) { 
	$results['http_port']	= '80';
} 
if (!$results['site_charset']) { 
	$results['site_charset'] = "UTF-8";
}
if (!$results['raw_web_path']) { 
	$results['raw_web_path'] = '/';
}
if (!$_SERVER['SERVER_NAME']) { 
	$_SERVER['SERVER_NAME'] = '';
}
if (!isset($results['auth_methods'])) { 
	$results['auth_methods'] = array('mysql');
}
if (!is_array($results['auth_methods'])) { 
	$results['auth_methods'] = array($results['auth_methods']);
}
if (!$results['user_ip_cardinality']) { 
	$results['user_ip_cardinality'] = 42;
}
if (!$results['local_length']) { 
	$results['local_length'] = '9000';
}

/* Variables needed for vauth Module */
$results['cookie_path'] 	= $results['raw_web_path'];
$results['cookie_domain']	= $_SERVER['SERVER_NAME'];
$results['cookie_life']		= $results['sess_cookielife'];
$results['session_name']	= $results['sess_name'];
$results['cookie_secure']	= $results['sess_cookiesecure'];
$results['session_length']	= $results['local_length'];
$results['mysql_password']	= $results['local_pass'];
$results['mysql_username']	= $results['local_username'];
$results['mysql_hostname']	= $results['local_host'];
$results['mysql_db']		= $results['local_db'];

/* Temp Fixes */
$results = fix_preferences($results);

// Setup Static Arrays
conf($results);

// Vauth Requires
require_once(conf('prefix') . '/modules/vauth/init.php');

// Librarys
require_once(conf('prefix') . '/lib/album.lib.php');
require_once(conf('prefix') . '/lib/artist.lib.php');
require_once(conf('prefix') . '/lib/song.php');
require_once(conf('prefix') . '/lib/phone_search.php');
require_once(conf('prefix') . '/lib/preferences.php');
require_once(conf('prefix') . '/lib/rss.php');
require_once(conf('prefix') . '/lib/log.lib.php');
require_once(conf('prefix') . '/lib/localplay.lib.php');
require_once(conf('prefix') . '/lib/phone_ui.lib.php');
require_once(conf('prefix') . '/lib/gettext.php');
require_once(conf('prefix') . '/lib/batch.lib.php');
require_once(conf('prefix') . '/lib/themes.php');
require_once(conf('prefix') . '/lib/stream.lib.php');
require_once(conf('prefix') . '/lib/playlist.lib.php');
require_once(conf('prefix') . '/lib/democratic.lib.php');
require_once(conf('prefix') . '/modules/catalog.php');
require_once(conf('prefix') . "/modules/id3/getid3/getid3.php");
require_once(conf('prefix') . '/modules/id3/vainfo.class.php');
require_once(conf('prefix') . '/modules/infotools/Snoopy.class.php');
require_once(conf('prefix') . '/modules/infotools/AmazonSearchEngine.class.php');
require_once(conf('prefix') . '/modules/infotools/lastfm.class.php'); 
//require_once(conf('prefix') . '/modules/infotools/jamendoSearch.class.php');
require_once(conf('prefix') . '/lib/xmlrpc.php');
require_once(conf('prefix') . '/modules/xmlrpc/xmlrpc.inc');

// Modules (These are conditionaly included depending upon config values)
if (conf('ratings')) { 
	require_once(conf('prefix') . '/lib/class/rating.class.php');
	require_once(conf('prefix') . '/lib/rating.lib.php');
}


// Classes
require_once(conf('prefix') . '/lib/class/localplay.class.php');
require_once(conf('prefix') . '/lib/class/plugin.class.php');
require_once(conf('prefix') . '/lib/class/stats.class.php');
require_once(conf('prefix') . '/lib/class/catalog.class.php');
require_once(conf('prefix') . '/lib/class/stream.class.php');
require_once(conf('prefix') . '/lib/class/playlist.class.php');
require_once(conf('prefix') . '/lib/class/tmp_playlist.class.php');
require_once(conf('prefix') . '/lib/class/song.class.php');
require_once(conf('prefix') . '/lib/class/view.class.php');
require_once(conf('prefix') . '/lib/class/update.class.php');
require_once(conf('prefix') . '/lib/class/user.class.php');
require_once(conf('prefix') . '/lib/class/album.class.php');
require_once(conf('prefix') . '/lib/class/artist.class.php');
require_once(conf('prefix') . '/lib/class/access.class.php');
require_once(conf('prefix') . '/lib/class/error.class.php');
require_once(conf('prefix') . '/lib/class/genre.class.php');
require_once(conf('prefix') . '/lib/class/flag.class.php');
require_once(conf('prefix') . '/lib/class/audioscrobbler.class.php');


/* Set a new Error Handler */
$old_error_handler = set_error_handler("ampache_error_handler");

/* Initilize the Vauth Library */
vauth_init($results);

/* Check their PHP Vars to make sure we're cool here */
$post_size = @ini_get('post_max_size');
if (substr($post_size,strlen($post_size)-1,strlen($post_size)) != 'M') { 
	/* Sane value time */
	ini_set('post_max_size','8M');
}

if ($results['memory_limit'] < 24) { 
	$results['memory_limit'] = 24;
}
set_memory_limit($results['memory_limit']);

// Check Session GC mojo, increase if need be
$gc_probability = @ini_get('session.gc_probability');
$gc_divisor     = @ini_get('session.gc_divisor');

if (!$gc_divisor) { 
	$gc_divisor = '100';
}
if (!$gc_probability) { 
	$gc_probability = '1';
}

// Force GC on 1:5 page loads 
if (($gc_divisor / $gc_probability) > 5) {
	$new_gc_probability = $gc_divisor * .2;
	ini_set('session.gc_probability',$new_gc_probability);
}

/* Seed the random number */
srand((double) microtime() * 1000003);

/**** END Set PHP Vars ****/

/* Check to see if they've tried to set no_session via get/post */
if (isset($_POST['no_session']) || isset($_GET['no_session'])) { 
	/* just incase of register globals */
	unset($no_session);
	debug_event('no_session','No Session passed as get/post','1');
}

/* We have to check for HTTP Auth */
if (in_array("http",$results['auth_methods'])) { 

	$results = vauth_http_auth("alex");

		vauth_session_cookie();
		vauth_session_create($results);
		$session_name = vauth_conf('session_name');
		$_SESSION['userdata'] = $results;
		$_COOKIE[$session_name] = session_id();

} // end if http auth

// If we don't want a session
if (NO_SESSION != '1' AND conf('use_auth')) { 
	/* Verify Their session */
	if (!vauth_check_session()) { logout(); exit; }

	/* Create the new user */
	$user = get_user_from_username($_SESSION['userdata']['username']);

	/* If they user ID doesn't exist deny them */
	if (!$user->uid AND !conf('demo_mode')) { logout(); exit; } 

	/* Load preferences and theme */
	init_preferences();
	set_theme();	
	$user->set_preferences();
	$user->update_last_seen();
}
elseif (!conf('use_auth')) { 
	$auth['success'] = 1;
	$auth['username'] = '-1';
	$auth['fullname'] = "Ampache User";
	$auth['id'] = -1;
	$auth['access'] = "admin";
	$auth['offset_limit'] = 50;
	if (!vauth_check_session()) { vauth_session_create($auth); }
	$user 			= new User(-1);
	$user->fullname 	= 'Ampache User';
	$user->offset_limit 	= $auth['offset_limit'];
	$user->username 	= '-1';
	$user->access 		= $auth['access'];
	$_SESSION['userdata']['username'] 	= $auth['username'];
	$user->set_preferences();
	init_preferences();
	set_theme();
}
else { 
	if (isset($_REQUEST['sessid'])) { 
		$sess_results = vauth_get_session($_REQUEST['sessid']);	
		session_id(scrub_in($_REQUEST['sessid']));
		session_start();
	}
	$user = get_user_from_username($sess_results['username']);
	init_preferences();
}

/* Add in some variables for ajax done here because we need the user */
$ajax_info['ajax_url']		= $results['web_path'] . '/server/ajax.server.php';
$ajax_info['ajax_info']		= '&amp;user_id=' . $user->id . '&amp;sessid=' . session_id();
conf($ajax_info);
unset($ajax_info);

// Load gettext mojo
load_gettext();

/* Set CHARSET */
header ("Content-Type: text/html; charset=" . conf('site_charset'));

/* Clean up a bit */
unset($array);
unset($results);

/* Setup the flip class */
flip_class(array('odd','even')); 

/* Setup the Error Class */
$error = new Error();

/* Set the Theme */
$theme = get_theme(conf('theme_name'));

if (! preg_match('/update\.php/', $_SERVER['PHP_SELF'])) {
	$update = new Update();
	if ($update->need_update()) {
		header("Location: " . conf('web_path') . "/update.php");
		exit();
	}
}

unset($update);
?>