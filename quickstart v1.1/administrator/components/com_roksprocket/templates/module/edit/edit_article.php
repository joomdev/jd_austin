<?php
/**
 * @version   $Id: edit_article.php 30598 2018-05-26 15:28:48Z matias $
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
$class .= ($order < (int) $limit) ? ' display-limit-flag' : '';
$class .= ($order == ((int)$limit) - 1) ? ' last-child' : '';
?>

<div class="clearfix article<?php echo $class?>" data-article-id="<?php echo $article->getArticleId();?>">
	<input type="hidden" name="items[<?php echo $article->getArticleId();?>][order]" value="<?php echo $order;?>" data-order="true">
    <!--<div class="graphic"></div>-->
    <div class="summary">
        <div class="title">
            <h1><?php echo $article->getTitle(); ?></h1>
            <div class="details"><span class="preview-wrapper"><i class="icon tool preview"></i></span> <span class="info-wrapper"><i class="icon tool info"></i></span></div>
        </div>
        <div class="category"><?php echo $article->getCategory(); ?></div>
        <div class="date"><?php echo $article->getDate();?></div>
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
