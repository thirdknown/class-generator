<?php

declare(strict_types=1);

namespace Thirdknown\ClassGenerator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateClassesFromJsonCommand extends Command
{
    protected function configure(): void
    {
        self::$defaultName = 'generate:classes-from-json';

        $this->setDescription('Generates classes from array or JSON.');

        $this->setHelp('php ./console.php generate:classes-from-json generateToDir=temp namespaceName="\\Thirdknown\\GoPay" sourceJsonFile=temp/payment.json');

        $this
            ->addArgument('generateToDir', InputArgument::REQUIRED, 'Where do you want to generate files to?')
            ->addArgument('namespaceName', InputArgument::REQUIRED, 'Namespace name')
            ->addArgument('rootClassName', InputArgument::REQUIRED, 'Root class name')
            ->addArgument('sourceJsonFile', InputArgument::REQUIRED, 'Source JSON file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classFromArrayGenerator = new ClassGenerator(
            $input->getArgument('generateToDir')
        );

        $classFromArrayGenerator->generatePhpFilesFromJson(
            file_get_contents($input->getArgument('sourceJsonFile')),
            $input->getArgument('namespaceName'),
            $input->getArgument('rootClassName'),
        );

        return 0;
    }
}
