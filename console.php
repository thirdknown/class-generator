<?php

declare(strict_types=1);

use Symfony\Component\Console\Application;
use Thirdknown\ClassGenerator\GenerateClassesFromJsonCommand;

require __DIR__ . '/bootstrap.php';

$application = new Application();



$application->add(new GenerateClassesFromJsonCommand());

$application->run();
