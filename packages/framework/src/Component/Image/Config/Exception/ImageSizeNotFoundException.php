<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config\Exception;

use Exception;

class ImageSizeNotFoundException extends Exception implements ImageConfigException
{
    protected string $entityClass;

    protected string $sizeName;

    /**
     * @param string $entityClass
     * @param string $sizeName
     * @param \Exception|null $previous
     */
    public function __construct($entityClass, $sizeName, ?Exception $previous = null)
    {
        $this->entityClass = $entityClass;
        $this->sizeName = $sizeName;

        parent::__construct(
            'Image size "' . $sizeName . '" not found for entity "' . $entityClass . '".',
            0,
            $previous,
        );
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @return string
     */
    public function getSizeName()
    {
        return $this->sizeName;
    }
}
