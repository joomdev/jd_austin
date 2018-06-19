<?php
/**
 * @package		Joomla.Administrator
 * @subpackage     com_modules
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<?php $advanced_hidden_fields = '';?>
<ul>
	<li>
		<?php echo $that->form->getLabel('ordering'); ?>
		<?php echo $that->form->getInput('ordering'); ?>
	</li>

	<li>
		<?php echo $that->form->getLabel('publish_up'); ?>
		<?php echo $that->form->getInput('publish_up'); ?>
	</li>

	<li>
		<?php echo $that->form->getLabel('publish_down'); ?>
		<?php echo $that->form->getInput('publish_down'); ?>
	</li>

	<li>
		<?php echo $that->form->getLabel('language'); ?>
		<?php echo $that->form->getInput('language'); ?>
	</li>

	<li>
		<?php echo $that->form->getLabel('note'); ?>
		<?php echo $that->form->getInput('note'); ?>
	</li>

	<?php
	$advanced_fieldset = $that->form->getFieldset('advanced');
	foreach ($advanced_fieldset as $field) : ?>
		<?php if (!$field->hidden) : ?>
			<li>
				<?php echo $field->label; ?>
				<?php echo $field->input; ?>
			</li>
			<?php else : $advanced_hidden_fields .= $field->input; ?>
			<?php endif; ?>
		<?php endforeach; ?>

	<?php if ($that->item->id) : ?>
	<li>
		<?php echo $that->form->getLabel('id'); ?>
		<?php echo $that->form->getInput('id'); ?>
	</li>
	<?php endif; ?>

	<li>
		<?php echo $that->form->getLabel('module'); ?>
		<?php echo $that->form->getInput('module'); ?>
		<input type="text" size="35"
		       value="<?php if ($that->item->xml) echo ($text = (string)$that->item->xml->name) ? JText::_($text) : $that->item->module; else echo JText::_('COM_MODULES_ERR_XML');?>"
		       class="readonly" readonly="readonly"/>
	</li>

	<li>
		<?php echo $that->form->getLabel('client_id'); ?>
		<input type="text" size="35"
		       value="<?php echo $that->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	"
		       class="readonly" readonly="readonly"/>
		<?php echo $that->form->getInput('client_id'); ?>
	</li>

</ul>
<?php echo $advanced_hidden_fields; ?>
