<?php
/**
 * @version   $Id: item.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

?>
<li data-quotes-item>
	<div class="sprocket-quotes-item quotes<?php echo $item->getParam('quotes_item_direction'); ?>arrow" data-quotes-content>
		<?php if ($item->getText()) :?>
			<span class="sprocket-quotes-text">
				<?php echo $item->getText(); ?>
			</span>
			<?php if ($item->getPrimaryLink()) : ?>
			<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="readon"><span><?php rc_e('READ_MORE'); ?></span></a>
			<?php endif; ?>
		<?php endif; ?>
		<?php if (($item->getPrimaryImage()) or ($item->getParam('quotes_item_author')) or ($item->getParam('quotes_item_subtext'))) :?>
		<div class="sprocket-quotes-info">
			<?php if ($item->getPrimaryImage()) :?>
				<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" class="sprocket-quotes-image" alt="<?php echo strip_tags((!$item->getPrimaryImage()->getAlttext()) ? $item->getTitle() : $item->getPrimaryImage()->getAlttext()); ?>" />
			<?php endif; ?>
			<?php if ($item->getParam('quotes_item_author')) :?>
				<span class="sprocket-quotes-author">
					<?php echo $item->getParam('quotes_item_author'); ?>
				</span>
			<?php endif; ?>
			<?php if ($item->getParam('quotes_item_subtext')) :?>
				<span class="sprocket-quotes-subtext">
					<?php echo $item->getParam('quotes_item_subtext'); ?>
				</span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</li>
