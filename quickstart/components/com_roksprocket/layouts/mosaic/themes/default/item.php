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
<li<?php echo strlen($item->custom_tags) ? ' class="'.$item->custom_tags.'"' : ''; ?> data-mosaic-item>
	<div class="sprocket-mosaic-item" data-mosaic-content>
		<?php echo $item->custom_ordering_items; ?>
		<div class="sprocket-padding">
			<?php if ($item->getPrimaryImage()) :?>
			<div class="sprocket-mosaic-image-container">
				<?php if ($item->getPrimaryLink()) : ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
				<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" alt="<?php echo strip_tags((!$item->getPrimaryImage()->getAlttext()) ? $item->getTitle() : $item->getPrimaryImage()->getAlttext()); ?>" class="sprocket-mosaic-image" />
				<?php if ($item->getPrimaryLink()) : ?>
					<span class="sprocket-mosaic-hover"></span>
					<span class="sprocket-mosaic-hovercontent"><span>+</span><?php rc_e('READ_MORE'); ?></span>
				</a>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="sprocket-mosaic-head">
				<?php if ($item->getTitle()): ?>
				<h2 class="sprocket-mosaic-title">
					<?php if ($item->getPrimaryLink()): ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
						<?php echo $item->getTitle();?>
					<?php if ($item->getPrimaryLink()): ?></a><?php endif; ?>
				</h2>
				<?php endif; ?>

				<?php if ($parameters->get('mosaic_article_details')): ?>
				<div class="sprocket-mosaic-infos">
					<?php if (($parameters->get('mosaic_article_details')=='1') or ($parameters->get('mosaic_article_details') == 'author')): ?>
					By 	<span class="author"><?php echo $item->getAuthor(); ?></span>
					<?php endif; ?>
					<?php if ($parameters->get('mosaic_article_details')=="1"): ?> / <?php endif; ?>
					<?php if (($parameters->get('mosaic_article_details')=="1") or ($parameters->get('mosaic_article_details') == 'date')): ?>
					<span class="date"><?php echo $item->getDate();?></span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<div class="sprocket-mosaic-text">
				<?php echo $item->getText(); ?>
			</div>

			<?php if ($item->getPrimaryLink()) : ?>
			<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="sprocket-readmore"><span><?php rc_e('READ_MORE'); ?></span></a>
			<?php endif; ?>

			<?php if (count($item->custom_tags_list)) : ?>
				<ul class="sprocket-mosaic-tags">
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
