<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace tests\Gica\Cqrs\Saga\SagaPersistence;

use Gica\Cqrs\Saga\SagaPersistence\ToJsonFilePersistence;
use Gica\FileSystem\InMemoryFileSystem;

class ToJsonFilePersistenceTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $fileSystem = new InMemoryFileSystem();

        $fileSystem->makeDirectory('/some/dir', 0777, true);

        $sut = new ToJsonFilePersistence(
            $fileSystem,
            '/some/dir'
        );

        $this->assertNull($sut->loadData('someId'));

        $sut->saveData('someId', null);

        $this->assertNull($sut->loadData('someId'));

        $sut->saveData('someId', ['some' => 'data']);

        $this->assertEquals(['some' => 'data'], $sut->loadData('someId'));
    }
}
