<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('_JEXEC') or die;
?>
<ul class="latestnews<?php echo $moduleclass_sfx; ?>">
<?php foreach ($list as $item) : ?>
	<li>

	  <?php if ($params->get('show_image') == 1 AND !empty($images->image_intro) ) : ?>
		<?php if(isset($images->image_intro)) : ?>
		<div class="image-figure">
		<a href="<?php echo $item->link; ?>">
		  <figure>
			<img class="qx-img-responsive" src="<?php echo JURI::base().$images->image_intro; ?>" alt="<?php echo $images->image_intro_alt; ?>">
		  </figure> <!-- end of media -->
		</a>
		<?php endif; ?>
	  <?php endif; ?> <!--//end show_image-->
	
		<a href="<?php echo $item->link; ?>">
			<?php echo $item->title; ?>
			<span><?php echo \JHtml::_('date', $item->created, 'DATE_FORMAT_LC3'); ?></span>
		</a>
	</li>
<?php endforeach; ?>
</ul>
