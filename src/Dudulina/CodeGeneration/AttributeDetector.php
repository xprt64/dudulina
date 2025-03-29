<?php
/*
 * Copyright (c) 2021 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration;

class AttributeDetector
{
    public static function hasAttribute(\ReflectionMethod $reflectionMethod, string $attributeClass)
    {
        if (version_compare(PHP_VERSION, '8', '>=')) {
            if ($reflectionMethod->getAttributes($attributeClass)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     * @param string $attributeClass
     * @return \ReflectionAttribute[]
     */
    public static function getAttributes(\ReflectionMethod $reflectionMethod, string $attributeClass)
    {
        if (version_compare(PHP_VERSION, '8', '>=')) {
            return $reflectionMethod->getAttributes($attributeClass);
        }
        return [];
    }
}