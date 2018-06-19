<?php
/**
 * @version   $Id: item.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

?>
<li class="sprocket-tables-block <?php echo $item->getParam('tables_item_class'); ?>" data-tables-item>
	<div class="sprocket-tables-item" data-tables-content>
		<?php if ($item->getPrimaryImage()) :?>
			<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" class="sprocket-tables-image" alt="<?php echo strip_tags((!$item->getPrimaryImage()->getAlttext()) ? $item->getTitle() : $item->getPrimaryImage()->getAlttext()); ?>" />
		<?php endif; ?>
		<?php if (($item->getTitle()) or ($item->getText())) : ?>
		<div class="sprocket-tables-desc sprocket-tables-cell sprocket-tables-bg1">
			<?php if ($item->getTitle()) : ?>
			<h4 class="sprocket-tables-title" data-tables-toggler>
				<?php if ($item->getPrimaryLink()) : ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
					<?php echo $item->getTitle();?>
				<?php if ($item->getPrimaryLink()) : ?></a><?php endif; ?>
			</h4>
			<?php endif; ?>
			<?php if ($item->getText()) :?>
				<span class="sprocket-tables-text">
					<?php echo $item->getText(); ?>
				</span>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if ($item->getParam('tables_item_price')) : ?>
			<span class="sprocket-tables-price sprocket-tables-cell sprocket-tables-bg2">
				<?php echo $item->getParam('tables_item_price'); ?>
			</span>
		<?php endif; ?>
		<?php if ($item->getParam('tables_item_feature_1')) : ?>
			<span class="sprocket-tables-feature sprocket-tables-cell sprocket-tables-bg1">
				<?php echo $item->getParam('tables_item_feature_1'); ?>
			</span>
		<?php endif; ?>
		<?php if ($item->getParam('tables_item_feature_2')) : ?>
			<span class="sprocket-tables-feature sprocket-tables-cell sprocket-tables-bg1">
				<?php echo $item->getParam('tables_item_feature_2'); ?>
			</span>
		<?php endif; ?>
		<?php if ($item->getParam('tables_item_feature_3')) : ?>
			<span class="sprocket-tables-feature sprocket-tables-cell sprocket-tables-bg1">
				<?php echo $item->getParam('tables_item_feature_3'); ?>
			</span>
		<?php endif; ?>
		<?php if ($item->getParam('tables_item_feature_4')) : ?>
			<span class="sprocket-tables-feature sprocket-tables-cell sprocket-tables-bg1">
				<?php echo $item->getParam('tables_item_feature_4'); ?>
			</span>
		<?php endif; ?>
		<?php if ($item->getPrimaryLink()) : ?>
		<div class="sprocket-tables-link sprocket-tables-cell sprocket-tables-bg1">
			<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="readon"><?php echo $item->getParam('tables_item_link_text'); ?></a>
		</div>
		<?php endif; ?>
	</div>
</li>
