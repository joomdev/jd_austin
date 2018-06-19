<?php

class N2SSSlideComponentMain extends N2SSSlideComponent {

    protected $type = 'main';

    /**
     * N2SSSlideComponentMain constructor.
     *
     * @param N2SmartSliderSlide $slide
     * @param array              $layersArray
     */
    public function __construct($slide, $layersArray) {
        $this->data = new N2Data(array());

        if (!$slide->underEdit) {
            $layersArray = N2SSSlideComponent::translateUniqueIdentifier($layersArray, false);
        }

        $this->container = new N2SSLayersContainer($slide, $this, $layersArray, 'absolute');

        $this->container->addContentLayer($slide, $this);
    }

    public function render($isAdmin = false) {
        return $this->renderContainer($isAdmin);
    }
}