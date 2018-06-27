<?php
/**
 * @version   $Id: item.php 29284 2015-12-14 19:11:37Z james $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $item RokSprocket_Item
 */
?>
<li <?php if (!$parameters->get('lists_enable_accordion') || $index == 0): ?>class="active" <?php endif;?>data-lists-item>
	<?php if ($item->custom_can_show_title): ?>
	<h4 class="sprocket-lists-title<?php if ($parameters->get('lists_enable_accordion')): ?> padding<?php endif; ?>" data-lists-toggler>
		<?php if ($item->custom_can_have_link): ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
			<?php echo $item->getTitle();?>
		<?php if ($item->custom_can_have_link): ?></a><?php endif; ?>
		<?php if ($parameters->get('lists_enable_accordion')): ?><span class="indicator"><span>+</span></span><?php endif; ?>
	</h4>
	<?php endif; ?>
	<div class="sprocket-lists-item" data-lists-content>
		<div class="sprocket-padding">
			<?php if ($item->getPrimaryImage()) :?>
			<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" class="sprocket-lists-image" alt="<?php echo strip_tags((!$item->getPrimaryImage()->getAlttext()) ? $item->getTitle() : $item->getPrimaryImage()->getAlttext()); ?>" />
			<?php endif; ?>
			<?php echo $item->getText(); ?>
			<?php if ($item->getPrimaryLink()) : ?>
			<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="readon"><span><?php rc_e('READ_MORE'); ?></span></a>
			<?php endif; ?>
		</div>
	</div>
</li>
