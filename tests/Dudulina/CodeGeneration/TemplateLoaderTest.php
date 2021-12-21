<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Dudulina\CodeGeneration;

use Dudulina\CodeGeneration\TemplateLoader;


class TemplateLoaderTest extends \PHPUnit\Framework\TestCase
{

    public function test()
    {
        $sut = new TemplateLoader();

        $input = <<<'TAG'
<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Dudulina\CodeGeneration\Event;

/**
 * --- This is just a template ---
 */
class QueryAskersMapTemplate
{
    public function getMap():array
    {
        return [/*do not modify this line!*/];
    }
}
TAG;

        $generated = $sut->loadTemplate(
            $input, 'QueryAskersMapTemplate', 'Dudulina\CodeGeneration\Event', 'SomeClass', 'Some\\Namespace'
        );

        $this->assertStringContainsString('class SomeClass', $generated);
        $this->assertStringContainsString('namespace Some\\Namespace;', $generated);
    }
}
