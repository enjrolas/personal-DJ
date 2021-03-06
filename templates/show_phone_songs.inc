<?php
/*

 Copyright (c) 2001 - 2007 Ampache.org
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
$web_path = conf('web_path');

/* If it's a playlist and they've got rights */
if (is_object($playlist) && ($GLOBALS['user']->username == $playlist->user || $GLOBALS['user']->has_access('100'))) { 
	$tab = 1;
	$playlist_owner = true;
}

//Horrible hack!
if ($_SERVER['SCRIPT_NAME'] == '/browse.php') { 
	// Setup the links
	$link_end = '</a>';
	$title_start = '<a href="' . $web_path . '/browse.php?keep_view=true&amp;sort_type=song.title&amp;sort_order=0">';
	$track_start = '<a href="' . $web_path . '/browse.php?keep_view=true&amp;sort_type=song.track&amp;sort_order=0">';
	$bit_start = '<a href="' . $web_path . '/browse.php?keep_view=true&amp;sort_type=song.bitrate&amp;sort_order=0">';
}
?>
<?php require(conf('prefix') . '/templates/show_box_top.inc.php'); ?>
<form id="songs" method="post" enctype="multipart/form-data" action="#" style="Display:inline;">
<table class="tabledata" cellspacing="0" cellpadding="0" border="0">
<?php if (is_object($GLOBALS['view'])) { ?>
<tr class="table-header" align="center">
        <td colspan="12">
        <?php if ($GLOBALS['view']->offset_limit) { require (conf('prefix') . "/templates/list_header.inc"); } ?>
        </td>
</tr>
<?php } ?>
<tr class="table-header">
	<th>&nbsp;&nbsp;<a href="#" onclick="check_select('song'); return false;">Select</a></th>
	<?php if ($playlist_owner) { ?> 
		<th><?php echo _('Track'); ?></th> 
	<?php } ?>
	<th><?php echo $title_start . _('Song title') . $link_end; ?></th>
	<th><?php echo _('Action'); ?></th>
</tr>
<?php
/* FIXME: don't even get me started with how many things are wrong with this code */ 
foreach ($song_ids as $song_id) {
	
	/* Arr matey crapy code abounds! */
	if (is_object($playlist)) { 
		if ($song_id['song']) { 
			$song = new Song($song_id['song']);
			$track_id = $song_id['id'];
		}
		else { 
			$song = new Song();
			$song->title = 'Dynamic Song';
			$track_id = $song_id['id'];
		}
	} // end if playlist
	elseif (!is_object($song_id)) { 
		unset($text_class);
		$song = new Song($song_id);
		$track_id = $song->id;
	}
	else { 
		$song = $song_id;
		$track_id = $song->id;
	}
	$song->format_song();
	// Still needed crap
	$totalsize += $song->size;
	$totaltime += $song->time;
	/* If it's disabled */
	if ($song->status == "disabled") { $text_class = "class=\"disabled\""; }
?>
<tr class="<?php echo flip_class(); ?>">
	<td align="center">
		<input type="checkbox" name="song[]" value="<?php echo $track_id; ?>" id="song_<?php echo $track_id; ?>" />
	</td>
		<?php
		if ($playlist_owner) {
			$tracknum = $playlist->get_track($track_id); ?>
			<td>
			<input type="text" tabindex="<?php echo $tab; ?>" size="3" name="<?php echo "tr_" . $track_id; ?>" value="<?php echo $tracknum; ?>" onchange="<?php echo "document.getElementById('song_" . $track_id . "').checked='checked';"; ?>" />
			</td>
			<?php $tab++;
		} ?>
	<td>
		<?php if ($song->has_flag()) { echo "<strong>**</strong>"; } ?>
		<a href="<?php echo $web_path; ?>/song.php?action=single_song&amp;song_id=<?php echo $song->id; ?>" title="<?php echo scrub_out($song->title); ?>" <?php echo $text_class; ?>><?php echo scrub_out($song->f_title); ?></a>
	</td>
																													 <td>
		<?php if ($GLOBALS['user']->prefs['direct_link']) { ?>
																												       <?php echo $song->get_url() ?>
			<a href="<?php echo scrub_out($song->get_url()); ?>">
				<?php echo get_user_icon('link'); ?>
			</a>
		<?php } ?>
	</td>
</tr>
<?php
}// foreach loop

$time = floor($totaltime/60) . ":" . sprintf("%02d", ($totaltime%60) );
$megs = sprintf("%.2f", ($totalsize/1048576));
$num = count($song_ids);
?>
<tr class="table-header">
	<td></td>
	<?php if (is_object($playlist)) { ?> <td></td> <?php } ?>
	<td><?php echo _("Total"); ?>:</td>
	<td nowrap="nowrap"><?php echo $num; ?> song(s)</td>
	<td></td>
	<td></td>
	<td align="right" nowrap="nowrap"><?php echo $time; ?></td>
	<td align="right" nowrap="nowrap"><?php echo $megs; ?> MB</td>
	<td></td>
	<td></td>
	<td colspan="2"></td>
</tr>
<?php if (is_object($GLOBALS['view'])) { ?>
<tr class="even" align="center">
        <td colspan="12">
        <?php if ($GLOBALS['view']->offset_limit) { require (conf('prefix') . "/templates/list_header.inc"); } ?>
        </td>
</tr>
<?php } ?>
</table>
<br />
<?php show_play_selected(); ?>
</form>
<?php require(conf('prefix') . '/templates/show_box_bottom.inc.php'); ?>

