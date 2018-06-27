<?php
/**
 * @version   $Id: index.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $layout     RokSprocket_Layout_Quotes
 * @var $items      RokSprocket_Item[]
 * @var $parameters RokCommon_Registry
 * @var $pages      int
 */

?>
<div class="sprocket-quotes" data-quotes="<?php echo $parameters->get('module_id'); ?>">
	<div class="sprocket-quotes-overlay"><div class="css-loader-wrapper"><div class="css-loader"></div></div></div>
	<ul class="sprocket-quotes-container cols-<?php echo $parameters->get('quotes_items_per_row'); ?>" data-quotes-items>
		<?php
			$index = 0;
			foreach ($items as $item){
				echo $layout->getThemeContext()->load('item.php', array('item'=> $item,'parameters'=>$parameters,'index'=>$index));
				$index++;
			}
		?>
	</ul>
	<div class="sprocket-quotes-nav">
		<div class="sprocket-quotes-pagination<?php echo !$parameters->get('quotes_show_pagination') || $pages <= 1 ? '-hidden' : '';?>">
			<ul>
			<?php for ($i = 1, $l = $pages;$i <= $pages;$i++): ?>
				<?php
					$class = ($i == 1) ? ' class="active"' : '';
				?>
		    	<li<?php echo $class; ?> data-quotes-page="<?php echo $i; ?>"><span><?php echo $i; ?></span></li>
			<?php endfor; ?>
			</ul>
		</div>
		<?php if ($parameters->get('quotes_show_arrows')!='hide' && $pages > 1) : ?>
		<div class="sprocket-quotes-arrows">
			<span class="arrow next" data-quotes-next></span>
			<span class="arrow prev" data-quotes-previous></span>
		</div>
		<?php endif; ?>
	</div>
</div>
