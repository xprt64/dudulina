<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Cqrs\Saga\SagaPersistence;


use Gica\Cqrs\Saga\SagaPersistence;
use Gica\FileSystem\FileSystemInterface;

class ToJsonFilePersistence implements SagaPersistence
{
    /**
     * @var FileSystemInterface
     */
    private $fileSystem;
    /**
     * @var string
     */
    private $directory;

    public function __construct(
        FileSystemInterface $fileSystem,
        string $directory
    )
    {
        $this->fileSystem = $fileSystem;
        $this->directory = $directory;
    }

    public function loadData(string $sagaId):?array
    {
        try {
            $fileContents = $this->fileSystem->fileGetContents($this->getFilePath($sagaId));
            return null !== $fileContents ? json_decode($fileContents, true) : null;

        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function saveData(string $sagaId, ?array $data)
    {
        $this->fileSystem->filePutContents($this->getFilePath($sagaId), json_encode($data));
    }

    private function getFilePath(string $sagaId): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $sagaId;
    }
}