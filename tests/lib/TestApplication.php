<?php

namespace Arcus;


class TestApplication extends ApplicationAbstract
{

    public function dispatch(TaskAbstract $task)
    {

    }

    public function inspect(): array
    {
        return [];
    }
}