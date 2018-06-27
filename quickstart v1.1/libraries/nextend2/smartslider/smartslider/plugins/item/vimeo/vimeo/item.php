<?php

N2Loader::import('libraries.renderable.layers.itemFactory', 'smartslider');

class N2SSItemVimeo extends N2SSItemAbstract {

    protected $type = 'vimeo';

    public function render() {
        $owner = $this->layer->getOwner();

        $this->data->set("vimeocode", preg_replace('/\D/', '', $owner->fill($this->data->get("vimeourl"))));

        $style = '';

        $hasImage  = 0;
        $image     = $this->data->get('image');
        $playImage = '';
        if (!empty($image)) {
            $style    = 'cursor:pointer; background: URL(' . N2ImageHelper::fixed($image) . ') no-repeat 50% 50%; background-size: cover';
            $hasImage = 1;
            if ($this->data->get('playbutton', 1) == 1) {

                $playWidth  = intval($this->data->get('playbuttonwidth', '48'));
                $playHeight = intval($this->data->get('playbuttonheight', '48'));
                if ($playWidth > 0 && $playHeight > 0) {

                    $attributes = array(
                        'class' => 'n2-video-play n2-ow',
                        'style' => ''
                    );

                    $attributes['style'] .= 'width:' . $playWidth . 'px;';
                    $attributes['style'] .= 'height:' . $playHeight . 'px;';
                    $attributes['style'] .= 'margin-left:' . ($playWidth / -2) . 'px;';
                    $attributes['style'] .= 'margin-top:' . ($playHeight / -2) . 'px;';

                    $playButtonImage = $this->data->get('playbuttonimage', '');
                    if (!empty($playButtonImage)) {
                        $src = N2ImageHelper::fixed($this->data->get('playbuttonimage', ''));
                    } else {
                        $src = N2ImageHelperAbstract::SVGToBase64('$ss$/images/play.svg');
                    }

                    $playImage = N2Html::image($src, 'Play', $attributes);
                }
            }
        }

        $owner->addScript('new N2Classes.FrontendItemVimeo(this, "' . $this->id . '", "' . $owner->getElementID() . '", ' . $this->data->toJSON() . ', ' . $hasImage . ', ' . $owner->fill($this->data->get('start', '0')) . ');');

        return N2Html::tag('div', array(
            'id'    => $this->id,
            'class' => 'n2-ow',
            'style' => 'position: absolute; top: 0; left: 0; width: 100%; height: 100%;' . $style
        ), $playImage);
    }

    public function _renderAdmin() {

        return N2Html::tag('div', array(
            "class" => 'n2-ow',
            "style" => 'width: 100%; height: 100%; background: URL(' . N2ImageHelper::fixed($this->data->getIfEmpty('image', '$system$/images/placeholder/video.png')) . ') no-repeat 50% 50%; background-size: cover;'
        ), '<div class="n2-video-play n2-ow">' . file_get_contents(N2ImageHelperAbstract::fixed('$ss$/images/play.svg', true)) . '</div>');
    }

    public function needSize() {
        return true;
    }
}