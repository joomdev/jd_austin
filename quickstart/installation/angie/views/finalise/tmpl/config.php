<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

?>
<div class="alert alert-warning">
	<?php echo AText::_('FINALISE_LBL_DONTFORGETCONFIG'); ?>
</div>

<h3>
	<?php echo AText::_('FINALISE_HEADER_CONFIGURATION'); ?>
</h3>
<p>
	<?php echo AText::_('FINALISE_LBL_CONFIGINTRO'); ?>
</p>
<pre class="scrollmore">
<?php echo htmlentities($this->configuration) ?>
</pre>
<p>
	<?php echo AText::_('FINALISE_LBL_CONFIGOUTRO'); ?>
</p>

<h3>
	<?php echo AText::_('FINALISE_HEADER_AFTERCONFIGURATION'); ?>
</h3>
