<?php

declare(strict_types=1);

namespace Thirdknown\ClassGenerator;

use ICanBoogie\Inflector;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Nette\PhpGenerator\Type;
use Nette\Utils\Json;

class ClassGenerator
{
    private const INDEXES_TYPE_ONLY_INT = 0;
    private const INDEXES_TYPE_ONLY_STRING = 1;
    private const INDEXES_TYPE_INT_AND_STRING = 2;

    private \ICanBoogie\Inflector $inflector;

    private string $generateToDir;

    public function __construct(string $generateToDir)
    {
        $this->generateToDir = $generateToDir;

        $this->inflector = Inflector::get('en');
    }

    public function generatePhpFilesFromJson(string $json, string $namespaceName, string $rootClassName): void
    {
        $array = Json::decode($json, Json::FORCE_ARRAY);

        $this->generatePhpFilesFromArray($array, $namespaceName, $rootClassName);
    }

    /**
     * @param mixed[] $array
     */
    public function generatePhpFilesFromArray(array $array, string $namespaceName, string $rootClassName): void
    {
        $phpFiles = $this->getPhpFilesFromArray($array, $namespaceName, $rootClassName);

        /** @var \Nette\PhpGenerator\PhpFile $phpFile */
        foreach ($phpFiles as $phpFile) {
            foreach ($phpFile->getNamespaces() as $phpNamespace) {
                foreach ($phpNamespace->getClasses() as $phpClass) {
                    $fileName = $phpClass->getName();
                    $fileContent = (new PsrPrinter())->printFile($phpFile);

                    file_put_contents($this->generateToDir . '/' . $fileName . '.php', $fileContent);
                }
            }
        }
    }

    /**
     * @return \Nette\PhpGenerator\PhpFile[]
     */
    public function getPhpFilesFromJson(string $json, string $namespaceName, string $rootClassName): array
    {
        $array = Json::decode($json, Json::FORCE_ARRAY);

        return $this->getPhpFilesFromArray($array, $namespaceName, $rootClassName);
    }

    /**
     * @param mixed[] $array
     * @return \Nette\PhpGenerator\PhpFile[]
     */
    public function getPhpFilesFromArray(array $array, string $namespaceName, string $rootClassName): array
    {
        $allPhpFiles = [];

        $rootArrayIndexesType = $this->getIndexesType($array);
        if ($rootArrayIndexesType === self::INDEXES_TYPE_ONLY_INT) {
            $array = array_shift($array);
        }

        $phpFile = new PhpFile();
        $phpFile->setStrictTypes();

        $allPhpFiles[] = $phpFile;

        $classType = $phpFile->addClass($namespaceName . '\\' . $rootClassName);

        foreach ($array as $index => $value) {
            $propertyName = $this->inflector->camelize($index, Inflector::DOWNCASE_FIRST_LETTER);

            $newPhpFiles = [];

            if (is_array($value)) {
                $indexesType = $this->getIndexesType($value);

                if ($indexesType === self::INDEXES_TYPE_ONLY_INT) {
                    $newPhpFiles = $this->processCollectionArray($namespaceName, $classType, $index, $value);
                } elseif ($indexesType === self::INDEXES_TYPE_ONLY_STRING) {
                    $newPhpFiles = $this->processArray($namespaceName, $classType, $index, $value);
                }
            } elseif (is_int($value)) {
                $classType->addProperty($propertyName)
                    ->setPrivate()
                    ->setType('int');
            } elseif (is_string($value)) {
                $classType->addProperty($propertyName)
                    ->setPrivate()
                    ->setType('string');
            } elseif (is_bool($value)) {
                $classType->addProperty($propertyName)
                    ->setPrivate()
                    ->setType('bool');
            } elseif (is_float($value)) {
                $classType->addProperty($propertyName)
                    ->setPrivate()
                    ->setType('float');
            }

            foreach ($newPhpFiles as $newPhpFile) {
                $allPhpFiles[] = $newPhpFile;
            }
        }

        return $allPhpFiles;
    }

    /**
     * @param mixed $value
     * @return \Nette\PhpGenerator\PhpFile[]
     */
    private function processCollectionArray(string $namespaceName, ClassType $classType, string $index, $value): array
    {
        $firstChildValue = array_shift($value);

        $propertyName = $this->inflector->camelize($index, Inflector::DOWNCASE_FIRST_LETTER);

        $newPhpFiles = [];
        if (is_string($firstChildValue)) {
            $typeName = 'string';
        } elseif (is_int($firstChildValue)) {
            $typeName = 'int';
        } elseif (is_float($firstChildValue)) {
            $typeName = 'float';
        } elseif (is_bool($firstChildValue)) {
            $typeName = 'bool';
        } else {
            $typeName = $this->inflector->camelize($index);
            $typeName = $this->inflector->singularize($typeName);

            $newPhpFiles = $this->getPhpFilesFromArray($firstChildValue, $namespaceName, $typeName);
        }

        $propertyName = $this->inflector->pluralize($propertyName);

        $classType->addProperty($propertyName)
            ->setComment('@var ' . $typeName . '[]')
            ->setPrivate()
            ->setType(Type::ARRAY);

        return $newPhpFiles;
    }

    /**
     * @param mixed $value
     * @return \Nette\PhpGenerator\PhpFile[]
     */
    private function processArray(string $namespaceName, ClassType $classType, string $index, $value): array
    {
        $propertyName = $this->inflector->camelize($index, Inflector::DOWNCASE_FIRST_LETTER);
        $typeName = $this->inflector->camelize($index);

        $classType->addProperty($propertyName)
            ->setType($typeName)
            ->setPrivate();

        return $this->getPhpFilesFromArray($value, $namespaceName, $typeName);
    }

    /**
     * @param mixed[] $array
     */
    private function getIndexesType(array $array): int
    {
        $intIndexesCount = 0;
        $stringIndexesCount = 0;
        foreach (array_keys($array) as $index) {
            if (is_int($index)) {
                $intIndexesCount++;
            } else {
                $stringIndexesCount++;
            }
        }

        $indexesType = self::INDEXES_TYPE_INT_AND_STRING;

        if ($intIndexesCount > 0 && $stringIndexesCount === 0) {
            $indexesType = self::INDEXES_TYPE_ONLY_INT;
        } elseif ($stringIndexesCount > 0 && $intIndexesCount === 0) {
            $indexesType = self::INDEXES_TYPE_ONLY_STRING;
        }

        return $indexesType;
    }
}
