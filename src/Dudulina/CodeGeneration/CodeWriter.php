<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

namespace Dudulina\CodeGeneration;

use Dudulina\CodeGeneration\Lib\DirectoriesToIterator;

class CodeWriter
{

    /**
     * @var CodeGenerator
     */
    private $codeGenerator;
    /**
     * @var string
     */
    private $templateClass;
    /**
     * @var string
     */
    private $outputShortClassName;

    public function __construct(
        CodeGenerator $codeGenerator,
        string $templateClass,
        string $outputShortClassName
    )
    {
        $this->codeGenerator = $codeGenerator;
        $this->templateClass = $templateClass;
        $this->outputShortClassName = $outputShortClassName;
    }

    public function writeCode(
        string $outputPath,
        bool $append,
        array $srcFolders
    )
    {
        $code = $this->getMapCode($srcFolders);

        if ($outputPath === '-') {
            die($code);
        }

        if ($append) {
            $existingFileContents = @file_get_contents($outputPath);
        } else {
            $existingFileContents = false;
        }

        if ($existingFileContents === false || $existingFileContents === '' || !$append) {
            $code = '<?php ' . $code;
        }

        $ok = file_put_contents($outputPath, $code, $append ? FILE_APPEND : 0);

        exit($ok !== false ? 0 : 1);

    }

    private function getMapCode(array $srcFolders)
    {
        $srcIterator = $this->getSourceFoldersIterator($srcFolders);
        $template = $this->getTemplateCode();
        return $this->codeGenerator->generateClass(
            $template,
            $srcIterator
        );
    }

    private function getSourceFoldersIterator(array $srcFolders): \AppendIterator
    {
        return new DirectoriesToIterator($srcFolders);
    }

    private function getTemplateCode(): string
    {
        $templateLoader = new TemplateLoader();
        $template = $templateLoader->loadTemplate(
            $this->getDefaultTemplateContent(),
            $this->getDefaultTemplateName(),
            $this->getDefaultTemplateNamespace(),
            $this->getNamespaceAndClassShortName($this->outputShortClassName)[1],
            $this->getNamespaceAndClassShortName($this->outputShortClassName)[0]
        );
        $PHP_FILE_STARTER = '<?php';
        $PHP_FILE_STARTER_LENGTH = 5;
        if (\substr($template, 0, $PHP_FILE_STARTER_LENGTH) === $PHP_FILE_STARTER) {
            return \substr($template, $PHP_FILE_STARTER_LENGTH);
        }
        return $template;
    }

    private function getNamespaceAndClassShortName(string $fqn)
    {
        $parts = explode('\\', $fqn);
        $name = array_pop($parts);
        $namespace = implode('\\', $parts);
        return [$namespace, $name];
    }

    private function getDefaultTemplateContent(): string
    {
        $classInfo = new \ReflectionClass($this->templateClass);
        return file_get_contents($classInfo->getFileName());
    }

    private function getDefaultTemplateName(): string
    {
        $classInfo = new \ReflectionClass($this->templateClass);
        return $classInfo->getShortName();
    }

    private function getDefaultTemplateNamespace(): string
    {
        $classInfo = new \ReflectionClass($this->templateClass);
        return $classInfo->getNamespaceName();
    }
}