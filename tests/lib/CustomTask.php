<?php

namespace Arcus;


class CustomTask extends TaskAbstract {

    public $custom;

    public function __construct($custom) {
        $this->custom = $custom;
    }
}