<?php
/*

 Copyright (c) 2001 - 2006 Ampache.org
 All rights reserved.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License v2
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

*/


// Allows users to search for duplicate songs in their catalogs

require_once ('../lib/init.php');
require_once( conf('prefix').'/lib/duplicates.php');

if (!$GLOBALS['user']->has_access(100)) {
	access_denied(); 
	exit;
}

$action = scrub_in($_REQUEST['action']);
$search_type = scrub_in($_REQUEST['search_type']);

show_template('header');

/* Switch on Action */
switch ($action) {
	case 'search':
        	$flags = get_duplicate_songs($search_type);
	        show_duplicate_songs($flags,$search_type);
	break;
	default:
	        show_duplicate_searchbox($search_type);
	break;
} // end switch on action

show_footer();
?>
