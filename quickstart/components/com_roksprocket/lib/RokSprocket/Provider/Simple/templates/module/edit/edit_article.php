<?php
/**
 * @version   $Id: edit_article.php 30593 2018-05-26 07:41:08Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * @var $article RokSprocket_Item
 * @var $itemform RokCommon_Config_Form
 */
$amount_of_items = 0;
$limit = isset($limit) ? $limit : 0;
$item_fieldsets = $itemform->getFieldsets('params');

$class = (!$order) ? ' first-child' : '';
$class .= ($order < (int)$limit) ? ' display-limit-flag' : '';
$class .= ($order == ((int)$limit) - 1) ? ' last-child' : '';
?>
<div class="clearfix article<?php echo $class?>" data-article-id="<?php echo $article->getArticleId();?>">
	<input type="hidden" name="items[<?php echo $article->getArticleId();?>][order]" value="<?php echo $order;?>" data-order="true">
	<!--<div class="graphic"></div>-->
    <div class="summary">
        <div class="title">
            <h1 data-article-title>
                <span><?php echo $article->getTitle(); ?></span>
                <input type="hidden" name="items[<?php echo $article->getArticleId();?>][params][_article_title]" value="<?php echo $article->getTitle();?>" data-order="true" data-article-title-input>
                <i class="icon tool edit" data-article-title-edit></i>
                <i class="icon tool cross" data-article-title-cross></i>
                <i class="icon tool check" data-article-title-check></i>
            </h1>
            <div class="details"><span class="remove-wrapper"><span class="confirm">Click again to continue ...</span><span class="remove">&times;</span></div>
            <span class="deleting icon spinner spinner-16"></span>
        </div>
        <!-- <div class="category"><?php echo $article->getCategory(); ?></div> -->
        <!-- <div class="date"><?php echo $article->getDate();?>2013-05-25 00:57:08</div> -->
    </div>
	<?php

	$hidden_fields = '';

	foreach ($item_fieldsets as $name => $fieldSet):?>
		<ul class="item-params">
	        <?php foreach ($itemform->getFieldsetWithGroup($name, 'params') as $field):?>
            <?php
                // caching $field->input
                $input = $field->input;
                $icon = $itemform->getFieldAttribute($field->fieldname, 'icon', 'text', 'params');
            ?>
	        <?php if (!$field->hidden) : ?>
			<li class="<?php //echo $type; ?>">
				<span class="input-name sprocket-tip" data-original-title="<?php rc_e($field->description); ?>"><?php echo $field->title; ?></span>
				<div class="input-prepend">
					<span class="add-on"><i class="tool <?php echo $icon; ?>"></i></span><?php echo $input; ?>
				</div>
			</li>
	        <?php else : $hidden_fields .= $input; ?>
	        <?php endif; ?>
	        <?php endforeach; ?>
	    </ul>
	    <?php echo $hidden_fields; ?>
	<?php endforeach; ?>
    <div class="handle"></div>
    <div class="clr"></div>
</div>
