<?php

namespace App;

use Helpers\AuthHelper;
use Helpers\ErrorHelper;

class Handler
{
    public function run()
    {
        ErrorHelper::showErrors();
        AuthHelper::auth();

        $task = new Task();
        $task->createTask();
    }
}