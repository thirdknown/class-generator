<?php

declare(strict_types=1);

use Nette\Utils\Json;
use Robo\Result;

class RoboFile extends \Robo\Tasks
{
    public function tests(): void
    {
        $this->exitWithResultExitCodeIfNotSuccessful(
            $this
                ->taskExec(__DIR__ . '/vendor/bin/parallel-lint --exclude app --exclude vendor .')
                ->run()
        );

        $this->exitWithResultExitCodeIfNotSuccessful(
            $this
                ->taskExec(__DIR__ . '/vendor/bin/ecs check --config ./config/ecs.yaml .')
                ->run()
        );

        $this->exitWithResultExitCodeIfNotSuccessful(
            $this
                ->taskExec(__DIR__ . '/vendor/bin/phpstan analyse --level=8 src')
                ->run()
        );

        $rectorOutputFilePath = __DIR__ . '/temp/rector-output';
        $this
            ->taskExec(__DIR__ . '/vendor/bin/rector process src --config ./config/rector.yaml --dry-run --output-format=json > ' . $rectorOutputFilePath)
            ->run();
        $this->processRectorOutputAndExitWithResultExitCodeIfRectorNotSuccessful($rectorOutputFilePath);

        $this->exitWithResultExitCodeIfNotSuccessful(
            $this
                ->taskPhpUnit(__DIR__ . '/vendor/bin/phpunit')
                ->file(__DIR__ . '/tests')
                ->run()
        );

        exit(0);
    }

    public function fixStandards(): void
    {
        $fixStandardsTask = $this
            ->taskExec(__DIR__ . '/vendor/bin/ecs check --config ./config/ecs.yaml --fix .');
        $fixStandardsTask->run();

        $this
            ->taskExec(__DIR__ . '/vendor/bin/rector process src --config ./config/rector.yaml')
            ->run();

        $fixStandardsTask->run();
    }

    private function exitWithResultExitCodeIfNotSuccessful(Result $result): void
    {
        if ($result->wasSuccessful() === false) {
            exit($result->getExitCode());
        }
    }

    private function processRectorOutputAndExitWithResultExitCodeIfRectorNotSuccessful(string $rectorOutputFilePath): void
    {
        $rectorOutput = file_get_contents($rectorOutputFilePath);
        unlink($rectorOutputFilePath);

        $rectorOutputArray = Json::decode($rectorOutput, Json::FORCE_ARRAY);

        foreach ($rectorOutputArray['totals'] as $total) {
            if ((int) $total > 0) {
                echo 'run ./runFixStandards.sh for upgrade source files by Rector' . "\n";
                exit(1);
            }
        }
    }
}
