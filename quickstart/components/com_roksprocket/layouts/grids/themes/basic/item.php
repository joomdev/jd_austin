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
<li data-grids-item>
	<div class="sprocket-grids-b-item<?php if (!$item->getPrimaryImage()) :?> panel-color<?php endif; ?>" data-grids-content>
		<?php if ($item->getPrimaryImage()) :?>
		<div class="sprocket-grids-b-image-container">
			<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" alt="<?php echo strip_tags((!$item->getPrimaryImage()->getAlttext()) ? $item->getTitle() : $item->getPrimaryImage()->getAlttext()); ?>" class="sprocket-grids-b-image" />
			<?php if ($item->getTitle() or $item->getPrimaryLink() or $item->getText()): ?>
			<div class="sprocket-grids-b-effect"></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ($item->getTitle() or $item->getPrimaryLink() or $item->getText()): ?>
		<div class="sprocket-grids-b-content<?php if ($item->getPrimaryImage()) :?> overlay-mode<?php endif; ?>">
			<?php if ($item->getTitle()): ?>
			<h2 class="sprocket-grids-b-title">
				<?php if ($item->getPrimaryLink()): ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
					<?php echo $item->getTitle();?>
				<?php if ($item->getPrimaryLink()): ?></a><?php endif; ?>
			</h2>
			<?php endif; ?>

			<?php if ($item->getText()): ?>
			<div class="sprocket-grids-b-text">
				<?php echo $item->getText(); ?>
			</div>
			<?php endif; ?>

			<?php if ($item->getPrimaryLink()) : ?>
			<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="sprocket-grids-b-readon"><?php rc_e('READ_MORE'); ?></a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</li>
