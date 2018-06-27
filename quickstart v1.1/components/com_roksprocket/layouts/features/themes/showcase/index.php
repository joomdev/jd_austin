<?php
/**
 * @version   $Id: index.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $layout     RokSprocket_Layout_Features
 * @var $items      RokSprocket_Item[]
 * @var $parameters RokCommon_Registry
 */

?>
<div class="sprocket-features layout-showcase <?php if ($parameters->get('features_show_arrows')!='hide') : ?>arrows-active<?php endif; ?> <?php if ($parameters->get('features_show_arrows')=='onhover') : ?>arrows-onhover<?php endif; ?> <?php if ($parameters->get('features_show_pagination')) : ?>pagination-active<?php endif; ?>" data-showcase="<?php echo $parameters->get('module_id'); ?>">
	<ul class="sprocket-features-list">
		<?php
		$index = 0;
			foreach($items as $item){
				$index++;
				echo $layout->getThemeContext()->load('item.php', array('item'=> $item,'parameters'=>$parameters,'index'=>$index,'layout'=>$layout));
			}
		?>
	</ul>
	<?php if ($parameters->get('features_show_arrows')!='hide') : ?>
	<div class="sprocket-features-arrows">
		<span class="arrow next" data-showcase-next><span>&rsaquo;</span></span>
		<span class="arrow prev" data-showcase-previous><span>&lsaquo;</span></span>
	</div>
	<?php endif; ?>
	<div class="sprocket-features-pagination<?php echo $parameters->get('features_show_pagination') ? '' : '-hidden'; ?>">
		<ul>
		<?php $i = 0; foreach ($items as $item): ?>
			<?php
				$class = (!$i) ? ' class="active"' : '';
				$i++;
			?>
	    	<li<?php echo $class; ?> data-showcase-pagination="<?php echo $i; ?>"><span><?php echo $i; ?></span></li>
		<?php endforeach; ?>
		</ul>
	</div>
</div>
