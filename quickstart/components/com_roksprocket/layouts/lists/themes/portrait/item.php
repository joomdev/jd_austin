<?php
/**
 * @version   $Id: item.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $item RokSprocket_Item
 */
?>
<li <?php if (!$parameters->get('lists_enable_accordion') || $index == 0): ?>class="active" <?php endif;?>data-lists-item data-lists-toggler>
	<?php if ($item->getPrimaryImage()) :?>
	<span class="portrait-image">
	<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" class="sprocket-lists-portrait-image" alt="<?php echo strip_tags((!$item->getPrimaryImage()->getAlttext()) ? $item->getTitle() : $item->getPrimaryImage()->getAlttext()); ?>" />
    </span>
	<?php endif; ?>
	<?php if ($item->custom_can_show_title): ?>
	<h4 class="sprocket-lists-portrait-title">
		<?php if ($item->custom_can_have_link): ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
			<?php echo $item->getTitle();?>
		<?php if ($item->custom_can_have_link): ?></a><?php endif; ?>
	</h4>
	<?php endif; ?>
	<div class="sprocket-lists-portrait-item" data-lists-content>
		<p class="portrait-text">
			<?php echo $item->getText(); ?>
		</p>
		<?php if ($item->getPrimaryLink()) : ?>
			<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><span><?php rc_e('READ_MORE'); ?></span></a>
		<?php endif; ?>
	</div>
</li>