<?php
/**
 * @version   $Id: index.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $layout     RokSprocket_Layout_Tables
 * @var $items      RokSprocket_Item[]
 * @var $parameters RokCommon_Registry
 * @var $pages      int
 */

?>
<div class="sprocket-tables" data-tables="<?php echo $parameters->get('module_id'); ?>">
	<div class="sprocket-tables-overlay"><div class="css-loader-wrapper"><div class="css-loader"></div></div></div>
	<ul class="sprocket-tables-container cols-<?php echo $parameters->get('tables_items_per_row'); ?>" data-tables-items>
		<?php
			$index = 0;
			foreach ($items as $item){
				echo $layout->getThemeContext()->load('item.php', array('item'=> $item,'parameters'=>$parameters,'index'=>$index));
				$index++;
			}
		?>
	</ul>
	<div class="sprocket-tables-nav">
		<ul class="sprocket-tables-pagination<?php echo !$parameters->get('tables_show_pagination') || $pages <= 1 ? '-hidden' : '';?>">
			<?php for ($i = 1, $l = $pages;$i <= $pages;$i++): ?>
				<?php
					$class = ($i == 1) ? ' class="active"' : '';
				?>
		    	<li<?php echo $class; ?> data-tables-page="<?php echo $i; ?>"><span><?php echo $i; ?></span></li>
			<?php endfor; ?>
		</ul>
		<?php if ($parameters->get('tables_show_arrows')!='hide' && $pages > 1) : ?>
		<div class="sprocket-tables-arrows">
			<span class="arrow next" data-tables-next></span>
			<span class="arrow prev" data-tables-previous></span>
		</div>
		<?php endif; ?>
	</div>
</div>
