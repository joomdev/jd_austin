<?php defined('_JEXEC') or die('Restricted access');

if (JFactory::getApplication()->isSite()) {
    include_once(JPATH_COMPONENT_ADMINISTRATOR.'/helpers/legacy_class.php');
    rokgallery_checktoken('get') or die(JText::_('JINVALID_TOKEN'));
}
require_once JPATH_ROOT . '/components/com_content/helpers/route.php';

$function = JFactory::getApplication()->input->getCmd('function', 'jSelectUser_Sprocket');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>

<form
    action="<?php echo JRoute::_('index.php?option=com_roksprocket&view=zooitems&layout=model&tmpl=component&function=' . $function . '&' . JSession::getFormToken() . '=1');?>"
    method="post" name="adminForm" id="adminForm">
    <fieldset class="filter clearfix">
        <div class="left">
            <label for="filter_search">
                <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
            </label>
            <input type="text" name="filter_search" id="filter_search"
                   value="<?php echo $this->escape($this->state->get('filter.search')); ?>" size="30"
                   title="<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>"/>

            <button type="submit">
                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_search').value='';this.form.submit();">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>

        <div class="right">
            <select name="filter_access" data-chosen="skip" class="inputbox chzn-done" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
            </select>

            <select name="filter_published" data-chosen="skip" class="inputbox chzn-done" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
            </select>

            <select name="filter_category_id" data-chosen="skip" class="inputbox chzn-done" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
                <option value="0"><?php echo JText::_('Root');?></option>
                <?php echo JHtml::_('select.options', $this->categories, 'value', 'text', $this->state->get('filter.category_id'));?>
            </select>
<!--            <select name="filter_application_id" data-chosen="skip" class="inputbox chzn-done" onchange="this.form.submit()">-->
<!--                <option value="">--><?php //echo JText::_('ROKSPROCKET_SELECT_APP');?><!--</option>-->
<!--                --><?php //echo JHtml::_('select.options', $this->applications, 'value', 'text', $this->state->get('filter.application_id'));?>
<!--            </select>-->
<!--            <select name="filter_type" data-chosen="skip" class="inputbox chzn-done" onchange="this.form.submit()">-->
<!--                <option value="">--><?php //echo JText::_('ROKSPROCKET_SELECT_TYPE');?><!--</option>-->
<!--                --><?php //echo JHtml::_('select.options', $this->types, 'value', 'text', $this->state->get('filter.type'));?>
<!--            </select>-->

            <select name="filter_author_id" data-chosen="skip" class="inputbox chzn-done" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
            </select>
        </div>
    </fieldset>

    <table class="adminlist">
        <thead>
        <tr>
            <th class="title">
                <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'title', $listDirn, $listOrder); ?>
            </th>
            <th width="15%">
                <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
            </th>
            <th width="15%">
                <?php echo JHtml::_('grid.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?>
            </th>
            <th width="15%">
                <?php echo JHtml::_('grid.sort', 'ROKSPROCKET_APP', 'application_title', $listDirn, $listOrder); ?>
            </th>
            <th width="15%">
                <?php echo JHtml::_('grid.sort', 'ROKSPROCKET_TYPE', 'type', $listDirn, $listOrder); ?>
            </th>
            <th width="5%">
                <?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
            </th>
            <th width="1%" class="nowrap">
                <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="15">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
        <?php foreach ($this->items as $i => $item) : ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td>
                <a class="pointer"
                   onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->name)); ?>', '<?php echo $this->escape($item->catid); ?>', null, '<?php echo $this->escape(JRoute::_('index.php?option=com_zoo&task=item&item_id=' . $item->id)); ?>');">
                    <?php echo $this->escape($item->name); ?></a>
            </td>
            <td class="center">
                <?php echo $this->escape($item->access_level); ?>
            </td>
            <td class="center">
                <?php echo $this->escape($item->category_title); ?>
            </td>
            <td class="center">
                <?php echo $this->escape($item->application_title); ?>
            </td>
            <td class="center">
                <?php echo $this->escape($item->type); ?>
            </td>
            <td class="center nowrap">
                <?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
            </td>
            <td class="center">
                <?php echo (int)$item->id; ?>
            </td>
        </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
