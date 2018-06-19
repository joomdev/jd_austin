<?php
/**
 * @package   angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

/** @var AngieViewSession $this */

$rootBasename = basename(dirname(APATH_BASE));
$rootBasename = empty($rootBasename) ? 'public_html' : $rootBasename;
$mySessionId = AApplication::getInstance()->getContainer()->session->getSessionKey();
?>
<div id="angie-session-blocked">
    <h2><?php echo AText::_('SESSIONBLOCKED_HEADER_IN_USE') ?></h2>

    <div class="alert alert-warning">
        <p class="small-text">
	        <?php echo AText::_('SESSIONBLOCKED_LBL_IN_USE_TEXT') ?>
        </p>
    </div>

    <h2>
	    <?php echo AText::_('SESSIONBLOCKED_HEADER_INERROR') ?></h2>
    <div class="well">
        <ol>
            <li><?php echo AText::_('SESSIONBLOCKED_LBL_INSTRUCTIONS_CONNECT') ?></li>
            <li><?php echo AText::sprintf('SESSIONBLOCKED_LBL_INSTRUCTIONS_GOTOROOT', $rootBasename) ?></li>
            <li><?php echo AText::_('SESSIONBLOCKED_LBL_INSTRUCTIONS_GOTOINSTALLER') ?></li>
            <li><?php echo AText::_('SESSIONBLOCKED_LBL_INSTRUCTIONS_GOTOTMP') ?></li>
            <li><?php echo AText::sprintf('SESSIONBLOCKED_LBL_INSTRUCTIONS_KEEPTHIS', $mySessionId) ?></li>
            <li><?php echo AText::_('SESSIONBLOCKED_LBL_INSTRUCTIONS_DELETETHESE') ?></li>
            <li><?php echo AText::_('SESSIONBLOCKED_LBL_INSTRUCTIONS_RELOAD') ?></li>
        </ol>
    </div>

    <h4><?php echo AText::_('SESSIONBLOCKED_HEADER_WHY_AM_I_SEEING_THIS') ?></h4>
    <p class="small-text"><?php echo AText::_('SESSIONBLOCKED_LBL_BECAUSE_SECURITY') ?></p>
    <p class="small-text"><?php echo AText::_('SESSIONBLOCKED_LBL_BECAUSE_WE_CARE') ?></p>

    <?php if (!defined('AKEEBA_PASSHASH')): ?>
    <h4><?php echo AText::_('SESSIONBLOCKED_HEADER_BEST_WAY_TO_AVOID') ?></h4>
    <p class="small-text"><?php echo AText::_('SESSIONBLOCKED_LBL_BEST_WAY_TO_AVOID') ?></p>
    <?php endif; ?>
</div>
