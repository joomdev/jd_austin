<?php

abstract class N2SmartSliderRenderableAbstract {

    public $less = array();
    public $css = array();

    public $elementId = '';

    public $fontSize = 16;

    public function addLess($file, $context) {
        $this->less[$file] = $context;
    }

    public function addCSS($css) {
        $this->css[] = $css;
    }

    public function addFont($font, $mode, $pre = null) {
        $fontData = N2FontRenderer::_render($font, $mode, $pre == null ? 'div#' . $this->elementId . ' ' : $pre, $this->fontSize);
        if ($fontData) {
            $this->addCSS($fontData[1]);

            return $fontData[0];
        }

        return '';
    }

    public function addStyle($style, $mode, $pre = null) {
        $styleData = N2StyleRenderer::_render($style, $mode, $pre == null ? 'div#' . $this->elementId . ' ' : $pre);
        if ($styleData) {
            $this->addCSS($styleData[1]);

            return $styleData[0];
        }

        return '';
    }

    public abstract function render();
}