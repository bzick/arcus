<?php

namespace Arcus;


abstract class TaskAbstract {
    /**
     * @var Origin
     */
    public $origin;

    public function setOrigin(Origin $origin) : self {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return Origin
     */
    public function getOrigin() : Origin {
        return $this->origin;
    }

    /**
     * Имя команды
     * @return string
     */
    public function getName() : string {
        return get_called_class();
    }
}