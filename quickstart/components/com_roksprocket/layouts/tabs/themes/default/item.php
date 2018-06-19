<?php
/**
 * @version   $Id: item.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $item RokSprocket_Item
 * @var $parameters RokCommon_Registry
 */
?>
<div class="sprocket-tabs-panel" data-tabs-panel>
	<?php echo $item->getText(); ?>
	<?php if ($item->getPrimaryLink()) : ?>
	<a href="<?php echo $item->getPrimaryLink()->getUrl(); ?>" class="readon"><span><?php rc_e('READ_MORE'); ?></span></a>
	<?php endif; ?>
</div>
