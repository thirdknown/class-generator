<?php

declare(strict_types=1);

namespace Thirdknown\ClassGenerator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateClassesFromJsonCommand extends Command
{
    public function __construct()
    {
        parent::__construct('generate:classes-from-json');
    }

    protected function configure(): void
    {
        $this->setDescription('Generates classes from array or JSON.');

        $this->setHelp('php ./console.php generate:classes-from-json temp Thirdknown\\GoPay GoPay temp/payment.json');

        $this
            ->addArgument('generateToDir', InputArgument::REQUIRED, 'Where do you want to generate files to?')
            ->addArgument('namespaceName', InputArgument::REQUIRED, 'Namespace name')
            ->addArgument('rootClassName', InputArgument::REQUIRED, 'Root class name')
            ->addArgument('sourceJsonFile', InputArgument::REQUIRED, 'Source JSON file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $generatoToDir */
        $generatoToDir = $input->getArgument('generateToDir');

        /** @var string $sourceJsonFile */
        $sourceJsonFile = $input->getArgument('sourceJsonFile');

        /** @var string $namespaceName */
        $namespaceName = $input->getArgument('namespaceName');

        /** @var string $rootClassName */
        $rootClassName = $input->getArgument('rootClassName');

        /** @var string $sourceJsonFileContent */
        $sourceJsonFileContent = file_get_contents($sourceJsonFile);

        $classFromArrayGenerator = new ClassGenerator($generatoToDir);

        $classFromArrayGenerator->generatePhpFilesFromJson(
            $sourceJsonFileContent,
            $namespaceName,
            $rootClassName,
        );

        return 0;
    }
}
