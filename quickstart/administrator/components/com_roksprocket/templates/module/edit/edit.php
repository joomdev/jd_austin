<?php
/**
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */


?>

<div id="system-message-container" class="sprocket-messages">
	<dl id="system-message">
		<dt class="error">Error</dt>
		<dd class="error message">
			<ul></ul>
		</dd>
		<dt class="message">Message</dt>
		<dd class="message message">
			<ul></ul>
		</dd>
	</dl>
</div>

<div id="module-form">
	<form autocomplete="off" action="<?php echo JRoute::_('index.php?option=com_roksprocket&layout=edit&id=' . (int)$that->item->id); ?>"
		  method="post" name="adminForm" id="adminForm" class="form-validate">
		<?php echo $that->form->getInput('uuid'); ?>
		<div id="details">
			<ul>
				<?php if ($that->item->edit_display_options->get('showTitle',true)):?>
				<li>
					<?php echo $that->form->getLabel('title'); ?>
					<?php echo $that->form->getInput('title'); ?>
				</li>
				<?php endif;?>
				<?php if ($that->item->edit_display_options->get('showShowTitle',true)):?>
				<li class="details-25">
					<?php echo $that->form->getLabel('showtitle'); ?>
					<?php echo $that->form->getInput('showtitle'); ?>
				</li>
				<?php endif;?>
				<?php if ($that->item->edit_display_options->get('showAccess',true)):?>
				<li class="details-25">
					<?php echo $that->form->getLabel('access'); ?>
					<?php echo $that->form->getInput('access'); ?>
				</li>
				<?php endif;?>
				<?php if ($that->item->edit_display_options->get('showPosition',true)):?>
				<li>
                <?php $version = new JVersion();?>
                <?php if (version_compare($version->getShortVersion(), '3.0', '>=')) :?>
                    <?php echo $that->form->getLabel('position'); ?>
                    <?php echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_positions.php', array('that'=>$that));?>
                <?php else:?>
                    <?php echo $that->form->getLabel('position'); ?>
                    <?php echo $that->form->getInput('position'); ?>
                <?php endif;?>
				</li>
				<?php endif;?>
				<?php if ($that->item->edit_display_options->get('showPublished',true)):?>
				<li class="details-25">
					<?php echo $that->form->getLabel('published'); ?>
					<?php echo $that->form->getInput('published'); ?>
				</li>
				<?php endif;?>
				<?php if ($that->item->edit_display_options->get('showShortcode',true)):?>
				<?php if ($that->item->id > 0) : ?>
				<li class="details-25">
					<label class="sprocket-tip" data-original-title="<?php echo JText::_('ROKSPROCKET_SHORTCODE_DESC'); ?>"><?php echo JText::_('ROKSPROCKET_SHORTCODE_LABEL'); ?></label>
					<div class="shortcode">
						[module-<?php echo $that->item->id; ?>]
					</div>
					<a class="copy-to-clipboard sprocket-tip" data-original-title="Copy to Clipboard" data-placement="above" href="#"><i class="icon tool clipboard"></i></a>
				</li>
				<?php endif; ?>
				<?php endif;?>
			</ul>
		</div>

		<div id="tabs-container">
			<div class="roksprocket-version">RokSprocket <span>v<?php echo str_replace("\2.1.23", "DEV", ROKSPROCKET_VERSION); ?></span></div>
			<ul class="tabs">
				<li class="tab active" data-tab="options">
					<i class="icon options"></i>
					<span>Options</span>
				</li>

				<?php if ($that->item->client_id == 0) : ?>
				<li class="tab" data-tab="menu-assignment">
					<i class="icon menu-assignment"></i>
					<span><?php rc_e('Assignments');?></span>
				</li>
				<?php endif; ?>

				<li class="tab" data-tab="advanced">
					<i class="icon advanced"></i>
					<span>Advanced</span>
				</li>

				<?php
				$fieldSets = $that->form->getFieldsets('params');
				foreach ($fieldSets as $name => $fieldSet) :
					if (in_array($name,array('roksprocket','advanced'))) continue;
					$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MODULES_' . $name . '_FIELDSET_LABEL';
					// TODO: Order the fieldset tips better
	//                if (isset($fieldSet->description) && trim($fieldSet->description)) :
	//                    echo '<p class="tip">' . $that->escape(JText::_($fieldSet->description)) . '</p>';
	//                endif;
					?>
					<li class="tab" data-tab="<?php echo $name;?>">
						<i class="icon other"></i>
						<span><?php echo $label;?></span>
					</li>
				 <?php endforeach; ?>
				<li class="separator"></li>
				<li class="badge">
					<ul>
						<?php foreach($that->container['roksprocket.providers.registered'] as $provider_id => $provider_info):?>
						<li style="display: <?php echo ($that->provider == $provider_id)?"block":"none";?>;"><i class="icon provider provider_<?php echo $provider_id;?> <?php echo $provider_id;?>"></i> <span><?php echo $provider_info->displayname;?> Provider</span></li>
						<?php endforeach; ?>
						<?php foreach($that->container['roksprocket.layouts'] as $layout_id => $layout_info):?>
						<li style="display: <?php echo ($that->layout == $layout_id)?"block":"none";?>;"><i class="icon layout layout_<?php echo $layout_id;?> <?php echo $layout_id;?>"></i> <span><?php echo $layout_info->displayname?> Layout</span></li>
						<?php endforeach; ?>
					</ul>
				</li>
			</ul>
			<div class="panels clearfix">
				<div data-panel="options" class="panel options active">
					<?php if ($that->item->id > 0): ?>
					<?php echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_roksprocket.php', array('that'=>$that)); ?>
					<?php else: ?>
					<?php echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_roksprocket_new.php', array('that'=>$that)); ?>
					<?php endif; ?>
				</div>

				<div data-panel="menu-assignment" class="panel options">
					<?php if ($that->item->client_id == 0) : ?>
					<?php  echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_assignment.php', array('that'=>$that)); ?>
					<?php endif; ?>
				</div>

				<?php $advanced_hidden_fields = '';?>
				<div data-panel="advanced" class="panel options">
					<?php  echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_advanced.php', array('that'=>$that)); ?>
				</div>
				<?php  echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_additional.php', array('that'=>$that)); ?>
			</div>
		</div>

		<div>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
