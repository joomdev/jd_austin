<?php
/*------------------------------------------------------------------------
# default_head.php - OT Testimonials Component
# ------------------------------------------------------------------------
# author    Vishal Dubey
# copyright Copyright (C) 2014 OurTeam. All Rights Reserved
# license   GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
# website   www.ourteam.co.in
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<tr>
	<th width="5">
		<?php echo JText::_('ID'); ?>
	</th>
	<th width="20">
		<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
	</th>
	<th>
		<?php echo JText::_('Client Name'); ?>
	</th>
	<th>
		<?php echo JText::_('Email'); ?>
	</th>
	<th>
		<?php echo JText::_('Rate'); ?>
	</th>
	<th>
		<?php echo JText::_('Title'); ?>
	</th>
	<th>
		<?php echo JText::_('Status'); ?>
	</th>
</tr>