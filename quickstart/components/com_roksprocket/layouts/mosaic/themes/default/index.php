<?php
/**
 * @version   $Id: index.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $layout     RokSprocket_Layout_Mosaic
 * @var $items      RokSprocket_Item[]
 * @var $parameters RokCommon_Registry
 * @var $pages      int
 */

?>
<div class="sprocket-mosaic" data-mosaic="<?php echo $parameters->get('module_id'); ?>">
	<div class="sprocket-mosaic-overlay"><div class="css-loader-wrapper"><div class="css-loader"></div></div></div>
	<?php if ((count($tagging) > 1) || count($ordering)): ?>
	<div class="sprocket-mosaic-header">
		<?php if (count($tagging) > 1): ?>
		<div class="sprocket-mosaic-filter">
			<ul>
				<?php foreach($tagging as $key => $filter): ?>
				<li class="<?php echo $key;?><?php echo $key == 'all' ? ' active' : '';?>" data-mosaic-filterby="<?php echo $key; ?>"><?php echo $filter; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<?php if (count((array)$ordering) > 1): ?>
		<div class="sprocket-mosaic-order">
			<ul>
				<?php foreach($ordering as $i => $order): ?>
				<li<?php echo !$i && $order != 'random' ? ' class="active"' : ''; ?> data-mosaic-orderby="<?php echo $order; ?>"><?php rc_e('ROKSPROCKET_MOSAIC_ORDERING_' . strtoupper($order)); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
		<div class="clear"></div>
	</div>
	<?php endif; ?>

	<ul class="sprocket-mosaic-container sprocket-mosaic-columns-<?php echo $parameters->get('mosaic_columns');?>" data-mosaic-items>
		<?php
			$index = 0;
			foreach ($items as $item){
				echo $layout->getThemeContext()->load('item.php', array('item'=> $item,'parameters'=>$parameters,'index'=>$index));
				$index++;
			}
		?>
	</ul>
	<?php if ($pages > 1): ?>
	<div class="sprocket-mosaic-loadmore" data-mosaic-loadmore>
		<span class="loadmore-more">
			<span class="loadmore-text"><?php rc_e('ROKSPROCKET_MOSAIC_LOADMORE'); ?></span>
			<span class="loadmore-info"><?php rc_e('ROKSPROCKET_MOSAIC_LOADMORE_TIP'); ?></span>
		</span>
		<span class="loadmore-all">
			<span class="loadmore-text"><?php rc_e('ROKSPROCKET_MOSAIC_LOADALL'); ?></span>
		</span>
	</div>
	<?php endif; ?>
</div>
