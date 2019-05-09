<?php

require "autoload.php";

use App\Handler;

define("SUBDOMAIN", "mozgivnoskesg");

$handler = new Handler();
$handler->run();