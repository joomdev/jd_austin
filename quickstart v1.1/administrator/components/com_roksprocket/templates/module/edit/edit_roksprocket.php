<?php
/**
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$fieldSet      = $that->form->getFieldset('roksprocket');
$hidden_fields = '';
$container = $that->container['roksprocket.layouts.' . $that->layout];
$composite = 'roksprocket_layout_' . $that->layout;
foreach ($container->paths as $path) {
	RokCommon_Composite::addPackagePath($composite, $path, 0);
}

$iconURL = RokCommon_Composite::get($composite)->getUrl($container->icon);
if (empty($iconURL)) $iconURL = "components/com_roksprocket/assets/images/default_layout_icon.png";
$css[] = sprintf('#module-form i.layout.%s {background-image: url(%s);background-position: 0 0;}', $that->layout, $iconURL);
?>

<style><?php echo implode("\n", $css); ?></style>

<div class="panel-left">
	<div class="panel-left-wrapper">
    	<?php  echo RokCommon_Composite::get('roksprocket.module.edit')->load('edit_articles.php', array('that'=>$that)); ?>
	</div>
</div>
<div class="panel-right">
	<ul>
	    <?php
	    	foreach ($fieldSet as $field) :
            foreach(array('group_open','group_bit', 'group_close','group_class') as $group){
                ${$group} = $that->form->getFieldAttribute($field->fieldname, $group, false, 'params');
            }
	    ?>
	    <?php if (!$field->hidden) : ?>
	        <?php
	        	if ($group_open) echo "<li".($group_class ? " class=\"".$group_class."\"" : "").">".$field->label.$field->input;
	        	else if ($group_bit) echo "<div class=\"group-bit\"><div class=\"group-label\">".$field->label."</div><div class=\"group-field\">".$field->input."</div></div>";
	    		else if ($group_close) echo "<div class=\"group-bit\"><div class=\"group-label\">".$field->label."</div><div class=\"group-field\">".$field->input."</div></div></li>";
	        	else echo "<li>".$field->label.$field->input."</li>";
	    	?>

	    <?php else : $hidden_fields .= $field->input; ?>
	    <?php endif; ?>
	    <?php endforeach; ?>
	</ul>
	<?php echo $hidden_fields; ?>
</div>
<div class="clr"></div>
