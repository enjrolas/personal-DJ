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
/*!
	@header Show Albums
	@discussion shows a list of albums
*/
$web_path = conf('web_path');
// Build array of the table classes we are using
$total_items = $view->total_items;
?>
<?php require(conf('prefix') . '/templates/show_box_top.inc.php'); ?>
<table class="tabledata" cellspacing="0" cellpadding="0" border="0">
<tr class="table-header">
	<td colspan="5">
	<?php if ($view->offset_limit) { require (conf('prefix') . "/templates/list_header.inc"); } ?>
	</td>
</tr>
<tr class="table-header">
	<td>
		<a href="<?php echo $web_path; ?>/<?php echo $_SESSION['view_script']; ?>?action=<?php echo $_REQUEST['action']; ?>&amp;keep_view=true&amp;sort_type=album.name&amp;sort_order=0"><?php echo _("Album"); ?></a>
	</td>
	<td>
		<?php echo _('Artist'); ?>
	</td>
	<td> <?php echo _('Songs'); ?>  </td>
	<td>
		<a href="<?php echo $web_path; ?>/<?php echo $_SESSION['view_script']; ?>?action=<?php echo $_REQUEST['action']; ?>&amp;keep_view=true&amp;sort_type=album.year&amp;sort_order=0"><?php echo _("Year"); ?></a>
	</td>
	<td> <?php echo _("Action"); ?> </td>
</tr>

<?php 
/* Foreach through the albums */
foreach ($albums as $album) { 
?>
	<tr class="<?php echo flip_class(); ?>">
			<td><?php echo $album->f_name; ?></td>
			<td><?php echo $album->f_artist; ?></td>
			<td><?php echo $album->songs; ?></td>
			<td><?php echo $album->year; ?></td>
			<td nowrap="nowrap"> 
			<a href="<?php echo $web_path; ?>/song.php?action=album&amp;album_id=<?php echo $album->id; ?>">
				<?php echo get_user_icon('all'); ?>
			</a>
			<a href="<?php echo $web_path; ?>/song.php?action=album_random&amp;album_id=<?php echo $album->id; ?>">
				<?php echo get_user_icon('random'); ?>
			</a>
			<?php if (batch_ok()) { ?>
				<a href="<?php echo $web_path; ?>/batch.php?action=alb&amp;id=<?php echo $album->id; ?>">
				<?php echo get_user_icon('batch_download'); ?>
				</a>
			<?php } ?>
			<?php if ($GLOBALS['user']->has_access('50')) { ?>
				<a href="<?php echo $web_path; ?>/admin/flag.php?action=show_edit_album&amp;album_id=<?php echo $album->id; ?>">
				<?php echo get_user_icon('edit'); ?>
				</a>
			<?php } ?>
			</td>
	</tr>
<?php } //end foreach ($albums as $album) ?>
<tr class="table-header">
	<td>
		<a href="<?php echo $web_path; ?>/<?php echo $_SESSION['view_script']; ?>?action=<?php echo $_REQUEST['action']; ?>&amp;keep_view=true&amp;sort_type=album.name&amp;sort_order=0"><?php echo _("Album"); ?></a>
	</td>
	<td>
		<?php echo _('Artist'); ?>
	</td>
	<td><?php echo _('Songs'); ?></td>
	<td>
		<a href="<?php echo $web_path; ?>/<?php echo $_SESSION['view_script']; ?>?action=<?php echo $_REQUEST['action']; ?>&amp;keep_view=true&amp;sort_type=album.year&amp;sort_order=0"><?php echo _("Year"); ?></a>
	</td>
	<td><?php echo _('Action'); ?></td>
</tr>
<tr class="even" align="center">
	<td colspan="5">
	<?php if ($view->offset_limit) { require (conf('prefix') . "/templates/list_header.inc"); } ?>
	</td>
</tr>
</table>
<?php require(conf('prefix') . '/templates/show_box_bottom.inc.php'); ?>
