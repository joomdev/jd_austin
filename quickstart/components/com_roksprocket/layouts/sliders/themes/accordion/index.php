<?php
/**
 * @version   $Id: index.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $layout     RokSprocket_Layout_Sliders
 * @var $items      RokSprocket_Item[]
 * @var $parameters RokCommon_Registry
 */

?>
<div class="sprocket-accordion" data-accordion="<?php echo $parameters->get('module_id'); ?>">
	<?php if ($parameters->get('sliders_show_arrows')!='hide') : ?>
	<div class="sprocket-accordion-arrow arrow-up" data-accordion-previous></div>
	<?php endif; ?>
	<div class="sprocket-accordion-container">
		<?php
			$i = 0;
			$index = 0;
			foreach($items as $item){
				$class = (!$i) ? ' active' : '';
				$i++;
				$index++;
				echo $layout->getThemeContext()->load('item.php', array('item'=> $item,'parameters'=>$parameters,'index'=>$index,'class'=>$class,'layout'=>$layout));
			}
		?>
	</div>
	<?php if ($parameters->get('sliders_show_arrows')!='hide') : ?>
	<div class="sprocket-accordion-arrow arrow-down" data-accordion-next></div>
	<?php endif; ?>
</div>
