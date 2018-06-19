<?php
/**
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @var $that->perItemForm JForm
 */

// No direct access.
defined('_JEXEC') or die;

?>

<div class="spacer-wrapper first">
	<h6>
		<?php 	// Until we have paging we are just gonna show a title
				// There are <span>5</span> articles of <span>15</span> displaying ?>
		Filtered Article List
		<ul class="articles-view-option">
			<li data-original-title="Hide per-article fields" class="sprocket-tip<?php echo ($that->showitems) ? '' : ' active';?>" data-placement="above"><i class="icon article-without-items"></i></li>
			<li data-original-title="Show per-article fields" class="sprocket-tip<?php echo ($that->showitems) ? ' active' : '';?>" data-placement="above"><i class="icon article-with-items"></i></li>
		</ul>
		<i class="right icon spinner spinner-16"></i>
	</h6>
</div>
<div class="clearfix provider-<?php echo $that->provider;?> articles<?php echo (!count($that->articles) ? ' no-articles': '');?><?php echo (!$that->showitems ? ' hide-items': '');?>" data-roksprocket-articles>
	<div class="article-description">
		<span class="article-instructions"></span>
		<div class="article-text">
			<h2><?php echo JText::_("ROKSPROCKET_INSTRUCTIONS_TITLE"); ?></h2>
			<p><?php echo JText::_("ROKSPROCKET_INSTRUCTIONS_TEXT"); ?></p>
			<p><img src="<?php echo JURI::base(true); ?>/components/com_roksprocket/assets/images/sample.png" class="sample" /></p>
		</div>
	</div>
	<?php
	$order = 0;
	/** @var $article RokSprocket_Item */
	foreach ($that->articles as $article):
		/** @var $per_item_form RokCommon_Config_Form */
		$per_item_form = $that->perItemForm;
		$per_item_form->setFormControl(sprintf('items[%s]', $article->getArticleId()));
		$per_item_form->bind(array('params'=>$article->getParams()));

		$limit = $that->form->getField('display_limit', 'params')->value;

		?>
		<?php echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_article.php', array(
		                                                                                              'itemform' => $per_item_form,
		                                                                                              'article'  => $article,
		                                                                                              'order'    => $order,
		                                                                                              'limit' 	 => $limit
		                                                                                         )); ?>
		<?php
		$order++;
	endforeach; ?>
</div>
<div class="load-more btn hide-load-more">
	<span><span class="text">load more</span><span class="info">HOLD <strong>SHIFT</strong> KEY TO LOAD ALL</span></span>
</div>
