<?php
/**
 * @package        Joomla.Administrator
 * @subpackage     com_modules
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$fieldSets = $that->form->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet) :
    if (in_array($name,array('roksprocket','advanced'))) continue;
    ?>
<div data-panel="<?php echo $name;?>" class="panel options">
    <ul>
        <?php $hidden_fields = ''; ?>
        <?php foreach ($that->form->getFieldset($name) as $field) : ?>
        <?php if (!$field->hidden) : ?>
            <li>
                <?php echo $field->label; ?>
                <?php echo $field->input; ?>
            </li>
            <?php else : $hidden_fields .= $field->input; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
    <?php echo $hidden_fields; ?>
</div>
<?php endforeach; ?>
