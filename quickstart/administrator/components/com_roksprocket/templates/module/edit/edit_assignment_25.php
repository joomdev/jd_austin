<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steph
 * Date: 10/17/12
 * Time: 9:50 AM
 * To change this template use File | Settings | File Templates.
 */

// Initiasile related data.
require_once JPATH_ADMINISTRATOR.'/components/com_menus/helpers/menus.php';
$menuTypes = MenusHelper::getMenuLinks();
?>
		<script type="text/javascript">
			window.addEvent('domready', function(){
				validate();
				document.getElements('select').addEvent('change', function(e){validate();});
			});
			function validate(){
				var value	= document.id('jform_assignment').value;
				var list	= document.id('menu-assignment');
				if(value == '-' || value == '0'){
					$$('.jform-assignments-button').each(function(el) {el.setProperty('disabled', true); });
					list.getElements('input').each(function(el){
						el.setProperty('disabled', true);
						if (value == '-'){
							el.setProperty('checked', false);
						} else {
							el.setProperty('checked', true);
						}
					});
				} else {
					$$('.jform-assignments-button').each(function(el) {el.setProperty('disabled', false); });
					list.getElements('input').each(function(el){
						el.setProperty('disabled', false);
					});
				}
			}
		</script>

		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_MODULES_MENU_ASSIGNMENT'); ?></legend>
			<label id="jform_menus-lbl" for="jform_menus"><?php echo JText::_('COM_MODULES_MODULE_ASSIGN'); ?></label>

			<fieldset id="jform_menus" class="radio">
				<?php
					$options = ModulesHelper::getAssignmentOptions($that->item->client_id);
					$displayValue = $displayText = $list = "";
					foreach($options as $option){
						$text = JText::_($option->text);

						if ((string)$that->item->assignment == (string)$option->value){
							$displayValue = $option->value;
							$displayText = $text;
						}
						$list .= '<li data-value="'.$option->value.'" data-text="'.$text.'" data-icon="" data-dynamic="">'."\n";
						$list .= '	<a href="#"><span>'.$text.'</span></a>'."\n";
						$list .= '</li>'."\n";
					}
				?>
				<div class="sprocket-dropdown">
					<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
						<span><?php echo $displayText; ?></span>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<?php echo $list; ?>
					</ul>
					<div class="dropdown-original">
						<select name="jform[assignment]" id="jform_assignment">
							<?php echo JHtml::_('select.options', $options, 'value', 'text', $that->item->assignment, true);?>
						</select>
					</div>
				</div>
			</fieldset>

			<label id="jform_menuselect-lbl" for="jform_menuselect"><?php echo JText::_('JGLOBAL_MENU_SELECTION'); ?></label>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = !el.checked; });">
				<?php echo JText::_('JGLOBAL_SELECTION_INVERT'); ?>
			</button>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = false; });">
				<?php echo JText::_('JGLOBAL_SELECTION_NONE'); ?>
			</button>

			<button type="button" class="jform-assignments-button jform-rightbtn" onclick="$$('.chk-menulink').each(function(el) { el.checked = true; });">
				<?php echo JText::_('JGLOBAL_SELECTION_ALL'); ?>
			</button>

			<div class="clr"></div>

			<div id="menu-assignment">

			<?php echo JHtml::_('tabs.start', 'module-menu-assignment-tabs', array('useCookie'=>1));?>

			<?php foreach ($menuTypes as &$type) :
				echo JHtml::_('tabs.panel', $type->title ? $type->title : $type->menutype, $type->menutype.'-details');

				$count 	= count($type->links);
				$i		= 0;
				if ($count) :
				?>
				<ul class="menu-links">
					<?php
					foreach ($type->links as $link) :
						if (trim($that->item->assignment) == '-'):
							$checked = '';
						elseif ($that->item->assignment == 0):
							$checked = ' checked="checked"';
						elseif ($that->item->assignment < 0):
							$checked = in_array(-$link->value, $that->item->assigned) ? ' checked="checked"' : '';
						elseif ($that->item->assignment > 0) :
							$checked = in_array($link->value, $that->item->assigned) ? ' checked="checked"' : '';
						endif;
					?>
					<li class="menu-link">
						<input type="checkbox" class="chk-menulink" name="jform[assigned][]" value="<?php echo (int) $link->value;?>" id="link<?php echo (int) $link->value;?>"<?php echo $checked;?>/>
						<label for="link<?php echo (int) $link->value;?>">
							<?php echo $link->text; ?>
						</label>
					</li>
					<?php if ($count > 20 && ++$i == ceil($count/2)) :?>
					</ul><ul class="menu-links">
					<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<div class="clr"></div>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php echo JHtml::_('tabs.end');?>

			</div>
		</fieldset>
