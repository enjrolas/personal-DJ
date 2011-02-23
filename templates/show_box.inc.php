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
?>
	<span class="box-title"><?php echo $title; ?></span>
	<ol class="box-list">
	<?php if (count($items)) { ?>
	<?php
		foreach ($items as $item) {
		    echo "<li>".$item->link."</li>\n";
		}
	?>
	<?php } else { echo '<span class="error">' . _('Not Enough Data') . '</span>'; } ?>
	</ol>
