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
$css = array();
$provider = null;
$layout   = null;
$jform = $that->form->getFormControl();
$jform = ($jform == 'jform') ? 'jform[params]' : $jform;
?>

<div class="panel create-new">
    <div class="panel-wrapper">
        <h1 class="step pick-layout"><span>1</span> Pick a Layout</h1>
        <div class="description">The Layout is what defines the style and animations of the articles.</div>
        <ul>
        <?php foreach($that->container['roksprocket.layouts'] as $layout_id => $layout_info):?>
            <?php
                $active = $that->layout == $layout_id;
                if ($active) $layout = $layout_id;

                $layout_composite_path = 'roksprocket_layout_' . $layout_id;
                $priority              = 0;
                foreach ($layout_info->paths as $path) {
                    RokCommon_Composite::addPackagePath($layout_composite_path, $path, $priority);
                    $priority++;
                }

                $recommended = !isset($layout_info->recommended) ? false : $layout_info->recommended;

                $recommended = htmlspecialchars(json_encode((!isset($layout_info->recommended)) ? false : $layout_info->recommended), ENT_QUOTES, 'UTF-8');

                $iconurl = RokCommon_Composite::get($layout_composite_path)->getUrl($layout_info->icon);
                if (empty($iconurl)) {
                    $iconurl = "components/com_roksprocket/assets/images/default_layout_icon.png";
                }
                $css[] = sprintf('#module-form i.layout.%s {background-image: url(%s);background-position: 0 0;}', $layout_id, $iconurl);
                $style = sprintf('background-image: url(%s);background-position: 0 0;', $iconurl);
            ?>
            <li<?php echo $active ? ' class="active"' : ''?> data-sprocket-layout="<?php echo $layout_id;?>" data-sprocket-recommended="<?php echo $recommended; ?>"><i class="icon layout layout_<?php echo $layout_id;?> <?php echo $layout_id;?>" style="<?php echo $style; ?>"></i> <span><?php echo $layout_info->displayname?></span></li>
        <?php endforeach; ?>
        </ul>
        <div class="alert alert-warning" data-sprocket-notice>For <strong></strong> layout, we recommend the use of <strong></strong><span class="star">*</span> provider</div>

        <h1 class="step pick-provider"><span>2</span> Pick a Content Provider</h1>
        <div class="description">The Content Provider drives where to read content from.</div>
        <ul>
        <?php foreach($that->container['roksprocket.providers.registered'] as $provider_id => $provider_info): ?>
            <?php
                $provider_class = $that->container[sprintf('roksprocket.providers.registered.%s.class', $provider_id)];
                $available      = call_user_func(array($provider_class, 'isAvailable'));
                if (!$available) continue;
                $active = $that->provider == $provider_id;
                if ($active) $provider = $provider_id;
            ?>
            <li<?php echo $active ? ' class="active"' : ''?> data-sprocket-provider="<?php echo $provider_id;?>"><i class="icon provider provider_<?php echo $provider_id;?> <?php echo $provider_id;?>"></i> <span><?php echo $provider_info->displayname;?></span></li>
        <?php endforeach; ?>
        </ul>

        <h1 class="step continue-new"><span>3</span> Create Module</h1>
        <div class="description">Now you have picked a Provider and Layout, click Continue to create the module and configure it!</div>
        <div class="btn btn-large btn-primary">Continue</div>
        <input name="<?php echo $jform; ?>[provider]" id="create-new-provider" value="<?php echo $provider; ?>" type="hidden" />
        <input name="<?php echo $jform; ?>[layout]" id="create-new-layout" value="<?php echo $layout; ?>" type="hidden" />
    </div>
</div>
<div class="clr"></div>
<style>
<?php
    echo implode("\n", $css);
?>
</style>
