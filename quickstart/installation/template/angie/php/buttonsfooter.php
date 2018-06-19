<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

$buttons = $this->getButtons();

// For better management, footer buttons are simply aliases of the top ones

if(!empty($buttons)) foreach($buttons as $button):

    // Ok I have an ID, so I can simply forward the click to the top button
    if ($button['id'])
    {
        $link = 'href="javascript:jQuery(\'#'.$button['id'].'\').simulate(\'click\');void(0)"';
    }
    else
    {
        // No ID, let's copy the same logic
        if (!empty($button['url']))
        {
            $link = 'href = "' . htmlentities($button['url'], ENT_COMPAT, 'UTF-8') . '"';
        }
        else
        {
            $link = 'href="#" onclick="' . htmlentities($button['onclick'], ENT_COMPAT, 'UTF-8') . '"';
        }
    }

	$class = "btn";

	if (!empty($button['types'])) foreach($button['types'] as $type)
	{
		$class .= " btn-$type";
	}

	$iconclass = "";

	if (!empty($button['icons'])) foreach($button['icons'] as $type)
	{
		$iconclass .= " icon-$type";
	}
?>
						<a <?php echo $link ?> class="<?php echo $class ?>">
<?php if(!empty($iconclass)):?>
							<span class="<?php echo $iconclass ?>"></span>
<?php endif; ?>
							<?php echo $button['message']; ?>
						</a>
<?php
endforeach;

