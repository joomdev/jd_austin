<?php
/**
 * @version   $Id: edit_article_preview.php 10885 2013-05-30 06:31:41Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */


/** @var $article RokSprocket_Item */
?>
<div class="article-preview">
    <h1 class="sprocket-section">Article Text</h1>
    <?php echo $article->getText(); ?>
</div>
