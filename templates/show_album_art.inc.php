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

// Gotta do some math here!
$total_images = count($images);
$rows = floor($total_images/4);
$i = 0;
?>
<?php show_box_top(); ?>
<table class="table-data">
<tr>
<?php 
while ($i <= $rows) { 
	$j=0;
	while ($j < 4) { 
		$key = $i*4+$j;
		$image_url = conf('web_path') . '/image.php?type=session&amp;image_index=' . $key; 
		if (!isset($images[$key])) { echo "<td>&nbsp;</td>\n"; } 
		else { 
?>
			<td align="center">
				<a href="<?php echo $image_url; ?>" target="_blank">
				<img src="<?php echo $image_url; ?>" border="0" height="175" width="175" /><br />
				</a>
				<p align="center">
				[<a href="<?php echo conf('web_path'); ?>/albums.php?action=select_art&amp;image=<?php echo $key; ?>&amp;album_id=<?php echo urlencode($_REQUEST['album_id']); ?>">Select</a>]
				</p>
			</td>
<?php 
		} // end else
		$j++;
	} // end while cells
	echo "</tr>\n<tr>";
	$i++;
} // end while
?>
</table>
<?php show_box_bottom(); ?>
