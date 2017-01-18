<?php
/******************************************************************************
 * Copyright (c) 2017 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace Gica\Cqrs\Command\CodeAnalysis;



use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerClassValidator;
use Gica\CodeAnalysis\MethodListenerDiscovery\ListenerMethod;
use Gica\CodeAnalysis\Traits\FilesInDirectoryExtracter;

class AggregateEventHandlersValidator
{
    use FilesInDirectoryExtracter;

    protected $eventToListenerMap = [];

    /** @var ListenerClassValidator */
    private $classValidator;

    /** @var ListenerMethod[] */
    private $allEventsListeners = [];

    public function __construct(
        ListenerClassValidator $classValidator
    )
    {
        $this->classValidator = $classValidator;
    }


    public function validateEventHandlers($directory)
    {
        $files = $this->getFilesInDirectory($directory);

        $files = $this->filterFiles($files);

        foreach ($files as $file) {
            $fullFilePath = $file;

            $this->validateFile($fullFilePath);
        }
    }

    /**
     * @param string $filePath
     * @return bool
     */
    protected function isListenerFileName($filePath)
    {
        return preg_match('#\.php$#ims', $filePath);
    }

    /**
     * @param $fullFilePath
     */
    protected function validateFile($fullFilePath)
    {
        $content = $this->readFile($fullFilePath);

        if (!preg_match('#class\s+(?P<className>\S+)\s#ims', $content, $m)) {
            return;
        }

        $unqualifiedClassName = $m['className'];

        if (!preg_match('#namespace\s+(?P<namespace>\S+);#ims', $content, $m)) {
            return;
        }

        $namespace = $m['namespace'];
        if ($namespace)
            $namespace = '\\' . $namespace;


        $fqn = $namespace . '\\' . $unqualifiedClassName;

        if (!class_exists($fqn)) {
            $this->evaluateCode($content);
        }

        $this->validateEventHandlersInClass($fqn);
    }

    protected function readFile($fullFilePath)
    {
        return file_get_contents($fullFilePath);
    }

    protected function addListenerToEvents(ListenerMethod $listener)
    {
        $this->eventToListenerMap[$listener->getEventClassName()][] = $listener;
        $this->allEventsListeners[] = $listener;
    }

    public function getEventToListenerMap()
    {
        return $this->eventToListenerMap;
    }

    /**
     * @return ListenerMethod[]
     */
    public function getAllEventsListeners(): array
    {
        return $this->allEventsListeners;
    }

    protected function filterFiles(array $files)
    {
        return array_filter($files, [$this, 'isListenerFileName']);
    }

    /**
     * @param $className
     * @throws \Exception
     */
    private function validateEventHandlersInClass($className)
    {
        $reflectionClass = new \ReflectionClass($className);

        if (!$this->classValidator->isClassAccepted($reflectionClass)) {
            return;
        }

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {

            if (!$this->isValidListenerMethod($reflectionMethod)) {
                continue;
            }

            $eventClass = $this->getMessageClassFromMethod($reflectionMethod);

            if ($eventClass) {

                $validMethodName = $this->getMethodNameFromEventClass($eventClass);

                if ($reflectionMethod->getName() != $validMethodName) {
                    throw new \Exception("Method's name is invalid: {$reflectionMethod->getName()} for event $eventClass in\n" .
                        "{$reflectionClass->getFileName()}:{$reflectionMethod->getStartLine()}\n" .
                        "should be $validMethodName");
                } else {
                }
            }
        }
    }

    private function getMessageClassFromMethod(\ReflectionMethod $reflectionMethod)
    {
        $reflectionParameter = $reflectionMethod->getParameters()[0];

        $typeHintedClass = $reflectionParameter->getClass();

        if ($typeHintedClass) {
            return $typeHintedClass->getName();
        }

        throw new \Exception("Method parameter is not type hinted");
    }

    private function isValidListenerMethod(\ReflectionMethod $reflectionMethod)
    {
        if ($reflectionMethod->getNumberOfParameters() == 0) {
            return false;
        }

        return 0 === stripos($reflectionMethod->getName(), 'apply');
    }

    private function evaluateCode($content)
    {
        $content = str_replace('<?php', '', $content);
        eval($content);
    }

    private function getMethodNameFromEventClass($className)
    {
        $parts = explode('\\', $className);

        return 'apply' . end($parts);
    }
}