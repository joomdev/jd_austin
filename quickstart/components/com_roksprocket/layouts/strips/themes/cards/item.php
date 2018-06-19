<?php
/**
 * @version   $Id: item.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

?>
<div class="sprocket-strips-c-block" data-strips-item>
	<!--Plain-->
	<?php if (!$item->getPrimaryImage()) :?>
			<div class="plain">
	<?php endif; ?>
	<!--Plain-->
	<div class="sprocket-strips-c-item" data-strips-content>
		<?php if ($item->getPrimaryImage()) :?>
			<img src="<?php echo $item->getPrimaryImage()->getSource(); ?>" class="sprocket-strips-c-image" alt="<?php echo strip_tags((!$item->getPrimaryImage()->getAlttext()) ? $item->getTitle() : $item->getPrimaryImage()->getAlttext()); ?>" />
		<?php endif; ?>
		<div class="sprocket-strips-c-content">
			<?php if ($item->getTitle()) : ?>
			<h4 class="sprocket-strips-c-title" data-strips-toggler>
				<?php if ($item->getPrimaryLink()) : ?><a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>"><?php endif; ?>
					<?php echo $item->getTitle();?>
				<?php if ($item->getPrimaryLink()) : ?></a><?php endif; ?>
			</h4>
			<?php endif; ?>
			<div class="sprocket-strips-c-extended">
				<div class="sprocket-strips-c-extended-info">
					<?php if ($item->getText()) :?>
						<span class="sprocket-strips-c-text">
							<?php echo $item->getText(); ?>
						</span>
					<?php endif; ?>
					<?php if ($item->getPrimaryLink()) : ?>
					<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="sprocket-strips-c-readon"><?php rc_e('READ_MORE'); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<!--Plain-->
	<?php if (!$item->getPrimaryImage()) :?>
			</div>
	<?php endif; ?>
	<!--Plain-->
</div>
