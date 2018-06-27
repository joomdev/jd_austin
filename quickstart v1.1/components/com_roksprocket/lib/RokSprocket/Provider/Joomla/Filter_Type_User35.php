<?php
/**
 * @version	$Id: Filter_Type_User.php 10887 2013-05-30 06:31:57Z btowles $
 * @author	 RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokSprocket_Provider_Joomla_Filter_Type_User35 extends RokSprocket_Provider_Joomla_Filter_Type_User
{

    protected function getFieldHTML($id, $name, $value, $attr, $link, $table)
    {
        JHtml::_('script', 'jui/fielduser.min.js', false, true);
        $html = array();

        $html[] = '<span class="field-user-wrapper" data-url="' . $link . '" data-modal=".modal" data-modal-width="100%" data-modal-height="500px" data-input="#'. $id .'" data-input-name="[data-other]" data-button-select=".icon-select-user">';
        $html[] = ' <input type="text" data-other="true" disabled="disabled" value="' . htmlspecialchars($table->name, ENT_COMPAT, 'UTF-8') . '"' . ' ' . $attr . ' />';
        $html[] = '	<a class="icon-select-user" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="#"' . '>';
        $html[] = '		<i class="icon tool user"></i>';
        $html[] = '	</a>';
        $html[] = '';
        $html[] = JHtml::_(
            'bootstrap.renderModal',
            'userModal_' . $id,
            array(
                'title'  => JText::_('JLIB_FORM_CHANGE_USER'),
                'closeButton' => true,
                'footer' => '<button type="button" class="btn" data-dismiss="modal">' . JText::_('JCANCEL') . '</button>'
            )
        );
        $html[] = '';
        $html[] = '</span>';

        // Create the real field, hidden, that stored the user id.
        $html[] = '<input type="hidden" name="'.$name.'" id="'.$id.'" data-name="'.$name.'" data-key="'.$this->getChunkType().'" value="' . (int)$value . '" />';

        return $html;
    }
}
