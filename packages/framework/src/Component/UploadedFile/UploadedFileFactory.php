<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\UploadedFile;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\FileUpload\FileUpload;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\FileUpload $fileUpload
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly FileUpload $fileUpload,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param string $temporaryFilename
     * @param string $uploadedFilename
     * @param int $position
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile
     */
    public function create(
        string $entityName,
        int $entityId,
        string $type,
        string $temporaryFilename,
        string $uploadedFilename,
        int $position = 0,
    ): UploadedFile {
        $temporaryFilepath = $this->fileUpload->getTemporaryFilepath($temporaryFilename);

        $classData = $this->entityNameResolver->resolve(UploadedFile::class);

        return new $classData($entityName, $entityId, $type, pathinfo(
            $temporaryFilepath,
            PATHINFO_BASENAME,
        ), $uploadedFilename, $position);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @param string $type
     * @param array $temporaryFilenames
     * @param array $uploadedFilenames
     * @param int $existingFilesCount
     * @return \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFile[]
     */
    public function createMultiple(
        string $entityName,
        int $entityId,
        string $type,
        array $temporaryFilenames,
        array $uploadedFilenames,
        int $existingFilesCount,
    ): array {
        $files = [];

        foreach ($temporaryFilenames as $key => $temporaryFilename) {
            $files[] = $this->create(
                $entityName,
                $entityId,
                $type,
                $temporaryFilename,
                $uploadedFilenames[$key],
                $existingFilesCount++,
            );
        }

        return $files;
    }
}
