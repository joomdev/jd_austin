<?php
/*------------------------------------------------------------------------
# default_body.php - OT Testimonials Component
# ------------------------------------------------------------------------
# author    Vishal Dubey
# copyright Copyright (C) 2014 OurTeam. All Rights Reserved
# license   GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
# website   www.ourteam.co.in
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$edit = "index.php?option=com_ottestimonials&view=ottestimonials&task=ottestimonial.edit";
$user = JFactory::getUser();
$userId = $user->get('id');
?>
<?php foreach($this->items as $i => $item){
	$canCheckin	= $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
	$userChkOut	= JFactory::getUser($item->checked_out);
	?>
	<tr class="row<?php echo $i % 2; ?>">
		<td>
			<?php echo $item->id; ?>
		</td>
		<td>
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td>
			<?php echo $item->clientname; ?> - (<a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo 'Edit'; ?></a>)
			<?php if ($item->checked_out){ ?>
				<?php echo JHtml::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'ottestimonials.', $canCheckin); ?>
			<?php } ?>
		</td>
		<td>
			<?php echo $item->email; ?>
		</td>
		<td>
			<?php echo $item->rate; ?>
		</td>
		<td>
			<?php echo $item->title; ?>
		</td>
        <td align="center"><?php // $published = JHTML::_('jgrid.published', $item, $i); echo $published;?>
        <?php echo JHtml::_('jgrid.published', $item->published, $i, 'ottestimonials.', $canChange ); ?></td>
	</tr>
<?php } ?>