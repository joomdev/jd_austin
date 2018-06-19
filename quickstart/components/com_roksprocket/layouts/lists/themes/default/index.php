<?php
/**
 * @version   $Id: index.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

/**
 * @var $layout     RokSprocket_Layout_Lists
 * @var $items      RokSprocket_Item[]
 * @var $parameters RokCommon_Registry
 * @var $pages      int
 */

?>
<div class="sprocket-lists" data-lists="<?php echo $parameters->get('module_id'); ?>">
	<ul class="sprocket-lists-container" data-lists-items>
		<?php
			$index = 0;
			foreach ($items as $item){
				echo $layout->getThemeContext()->load('item.php', array('item'=> $item,'parameters'=>$parameters,'index'=>$index));
				$index++;
			}
		?>
	</ul>
	<div class="sprocket-lists-nav">
		<div class="sprocket-lists-pagination<?php echo !$parameters->get('lists_show_pagination') || $pages <= 1 ? '-hidden' : '';?>">
			<ul>
			<?php for ($i = 1, $l = $pages;$i <= $pages;$i++): ?>
				<?php
					$class = ($i == 1) ? ' class="active"' : '';
				?>
		    	<li<?php echo $class; ?> data-lists-page="<?php echo $i; ?>"><span><?php echo $i; ?></span></li>
			<?php endfor; ?>
			</ul>
		</div>
		<div class="spinner"></div>
		<?php if ($parameters->get('lists_show_arrows')!='hide' && $pages > 1) : ?>
		<div class="sprocket-lists-arrows">
			<span class="arrow next" data-lists-next><span>&rsaquo;</span></span>
			<span class="arrow prev" data-lists-previous><span>&lsaquo;</span></span>
		</div>
		<?php endif; ?>
	</div>
</div>
