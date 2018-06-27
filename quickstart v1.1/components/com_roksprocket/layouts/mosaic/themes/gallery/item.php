<?php
/**
 * @version   $Id: item.php 18937 2014-02-21 22:54:29Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $item RokSprocket_Item
 */
?>
<li<?php echo strlen($item->custom_tags) ? ' class="'.$item->custom_tags.'"' : ''; ?> data-mosaic-item>
	<div class="sprocket-mosaic-g-item<?php if (!$item->getPrimaryImage()) :?> panel-color<?php endif; ?>" data-mosaic-content>
		<?php echo $item->custom_ordering_items; ?>
		<?php if ($item->getPrimaryImage()) :?>
		<div class="sprocket-mosaic-g-image-container">
			<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" alt="<?php echo strip_tags((!$item->getPrimaryImage()->getAlttext()) ? $item->getTitle() : $item->getPrimaryImage()->getAlttext()); ?>" class="sprocket-mosaic-g-image" />
			<?php if ($item->getTitle() or $item->getPrimaryLink() or $item->getText()): ?>
			<div class="sprocket-mosaic-g-effect"></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="sprocket-mosaic-g-content<?php if ($item->getPrimaryImage()) :?> overlay-mode<?php endif; ?>">
			<?php if ($item->getTitle()): ?>
			<h2 class="sprocket-mosaic-g-title">
				<?php if ($item->getPrimaryLink()): ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
					<?php echo $item->getTitle();?>
				<?php if ($item->getPrimaryLink()): ?></a><?php endif; ?>
			</h2>
			<?php endif; ?>

			<?php if ($parameters->get('mosaic_article_details')): ?>
			<div class="sprocket-mosaic-g-info">
				<?php if (($parameters->get('mosaic_article_details')=='1') or ($parameters->get('mosaic_article_details') == 'author')): ?>
				<span class="author"><?php echo $item->getAuthor(); ?></span>
				<?php endif; ?>
				<?php if ($parameters->get('mosaic_article_details')=="1"): ?> / <?php endif; ?>
				<?php if (($parameters->get('mosaic_article_details')=="1") or ($parameters->get('mosaic_article_details') == 'date')): ?>
				<span class="date"><?php echo $item->getDate();?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="sprocket-mosaic-g-text">
				<?php echo $item->getText(); ?>
			</div>

			<?php if ($item->getPrimaryLink()) : ?>
			<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="sprocket-mosaic-g-readon"><?php rc_e('READ_MORE'); ?></a>
			<?php endif; ?>

			<?php if (count($item->custom_tags_list)) : ?>
			<ul class="sprocket-mosaic-g-tags">
			<?php
				foreach($item->custom_tags_list as $key => $name){
			 		echo ' <li class="sprocket-tags-'.$key.'">'.$name.'</li>';
				}
			?>
			</ul>
		<?php endif; ?>
		</div>
	</div>
</li>
