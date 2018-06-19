<?php
/**
 * @version   $Id: index.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $layout     RokSprocket_Layout_Tabs
 * @var $items      RokSprocket_Item[]
 * @var $parameters RokCommon_Registry
 */
?>
<div data-tabs="<?php echo $parameters->get('module_id'); ?>">
	<div class="sprocket-tabs layout-<?php echo $parameters->get('tabs_position'); ?> animation-<?php echo $parameters->get('tabs_animation'); ?>">
		<?php if ($parameters->get('tabs_position')!='bottom') : ?>
			<ul class="sprocket-tabs-nav">
				<?php foreach ($items as $item): ?>
				<li data-tabs-navigation><span class="sprocket-tabs-inner">
					<?php if ($item->getParam('tabs_item_icon')) : ?><img src="<?php echo $item->getParam('tabs_item_icon')->getSource(); ?>" class="sprocket-tabs-icon" alt="icon" /><?php endif; ?>
					<span class="sprocket-tabs-text">
						<?php echo $item->getTitle();?>
					</span>
				</span></li>
				<?php endforeach;?>
			</ul>
		<?php endif; ?>
		<div class="sprocket-tabs-panels">
			<?php foreach ($items as $item):
			echo $layout->getThemeContext()->load('item.php', array('item'=> $item,'parameters'=>$parameters));
			endforeach;?>
		</div>
		<?php if ($parameters->get('tabs_position')=='bottom') : ?>
			<ul class="sprocket-tabs-nav">
				<?php foreach ($items as $item): ?>
				<li data-tabs-navigation><span class="sprocket-tabs-inner">
					<?php if ($item->getParam('tabs_item_icon')) : ?><img src="<?php echo $item->getParam('tabs_item_icon')->getSource(); ?>" class="sprocket-tabs-icon" alt="icon" /><?php endif; ?>
					<span class="sprocket-tabs-text">
						<?php echo $item->getTitle();?>
					</span>
				</span></li>
				<?php endforeach;?>
			</ul>
		<?php endif; ?>
	</div>
</div>
