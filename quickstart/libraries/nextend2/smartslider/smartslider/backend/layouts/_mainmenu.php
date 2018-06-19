<?php
$cmd = N2Request::getVar("nextendcontroller", "sliders");
/**
 * @see Nav
 */

$views = array();
$views[] = N2Html::tag('a', array(
    'href'  => $this->appType->router->createUrl("settings/default"),
    'class' => 'n2-h4 n2-uc ' . ($cmd == "settings" ? "n2-active" : "")
), n2_('Settings'));

$views[] = N2Html::tag('a', array(
    'href'   => N2SS3::getProUrlPricing(array(
        'utm_source'   => 'go-pro-button-top-menu',
        'utm_medium'   => 'smartslider-' . N2Platform::getPlatform() . '-free',
        'utm_campaign' => N2SS3::$campaign
    )),
    'target' => '_blank',
    'class'  => 'n2-h4 n2-uc '
), n2_('Go Pro!'));



$help = N2Html::link(n2_('Docs'), 'https://smartslider3.helpscoutdocs.com/?utm_campaign=' . N2SS3::$campaign . '&utm_source=dashboard-documentation&utm_medium=smartslider-' . N2Platform::getPlatform() . '-' . N2SS3::$plan, array(
        'target' => '_blank',
        'class'  => 'n2-h4'
    )) . N2Html::link(n2_('Videos'), 'https://www.youtube.com/watch?v=lsq09izc1H4&list=PLSawiBnEUNfvzcI3pBHs4iKcbtMCQU0dB&utm_campaign=' . N2SS3::$campaign . '&utm_source=dashboard-watch-videos&utm_medium=smartslider-' . N2Platform::getPlatform() . '-' . N2SS3::$plan, array(
        'target' => '_blank',
        'class'  => 'n2-h4'
    )) . N2Html::link(n2_('Support'), 'https://smartslider3.com/contact-us/?utm_campaign=' . N2SS3::$campaign . '&utm_source=dashboard-write-support&utm_medium=smartslider-' . N2Platform::getPlatform() . '-' . N2SS3::$plan, array(
        'target' => '_blank',
        'class'  => 'n2-h4'
    )) . N2Html::link(n2_('Newsletter'), 'https://smartslider3.com/subscribe?u=a41cdf5c66c6a26c1002f5296&id=1cf1f54d9b?utm_campaign=' . N2SS3::$campaign . '&utm_source=dashboard-subscribe-newsletter&utm_medium=smartslider-' . N2Platform::getPlatform() . '-' . N2SS3::$plan, array(
        'target' => '_blank',
        'class'  => 'n2-h4'
    ));

$views[] = N2Html::tag('div', array(
    'class' => 'n2-menu-has-sub'
), N2Html::link(n2_('Help'), 'https://smartslider3.com/help/?utm_campaign=' . N2SS3::$campaign . '&utm_source=dashboard-write-support&utm_medium=smartslider-' . N2Platform::getPlatform() . '-' . N2SS3::$plan, array(
        'class'  => 'n2-h4 n2-uc',
        'target' => '_blank'
    )) . N2Html::tag('div', array('class' => 'n2-menu-sub'), $help));

N2Html::nav(array(
    'logoUrl'      => $this->appType->router->createUrl("sliders/index"),
    'logoImageUrl' => $this->appType->app->getLogo(),
    'views'        => $views,
    'actions'      => $this->getFragmentValue('actions')
));