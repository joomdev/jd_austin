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

<div data-accordion-item class="sprocket-accordion-item<?php echo $class; ?>" style="<?php if ($item->getPrimaryImage()) :?>background-image: url(<?php echo $item->getPrimaryImage()->getSource(); ?>);<?php endif; ?>">
	<div class="sprocket-accordion-content" data-accordion-content>
		<?php if ($parameters->get('sliders_show_title') && $item->getTitle()) : ?>
			<h2 class="sprocket-accordion-title">
				<?php if ($item->getPrimaryLink()) : ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
					<?php echo $item->getTitle(); ?>
				<?php if ($item->getPrimaryLink()) : ?></a><?php endif; ?>
			</h2>
		<?php endif; ?>
		<?php if ($parameters->get('sliders_show_article_text') && ($item->getText() || $item->getPrimaryLink())) : ?>
			<div class="sprocket-accordion-desc">
				<span>
					<?php echo $item->getText(); ?>
				</span>
				<?php if ($item->getPrimaryLink()) : ?>
				<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="readon"><?php rc_e('READ_MORE'); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if ($parameters->get('sliders_show_overlay')) : ?>
	<div class="sprocket-accordion-overlay"></div>
	<?php endif; ?>
</div>
