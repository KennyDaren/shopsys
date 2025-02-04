<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Config;

use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageAdditionalSizeNotFoundException;

class ImageSizeConfig
{
    protected ?string $name = null;

    protected ?int $width = null;

    protected ?int $height = null;

    protected bool $crop;

    protected ?string $occurrence = null;

    /**
     * @param string|null $name
     * @param int|null $width
     * @param int|null $height
     * @param bool $crop
     * @param string|null $occurrence
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig[] $additionalSizes
     */
    public function __construct($name, $width, $height, $crop, $occurrence, protected readonly array $additionalSizes)
    {
        $this->name = $name;
        $this->width = $width;
        $this->height = $height;
        $this->crop = $crop;
        $this->occurrence = $occurrence;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return bool
     */
    public function getCrop()
    {
        return $this->crop;
    }

    /**
     * @return string|null
     */
    public function getOccurrence()
    {
        return $this->occurrence;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig[]
     */
    public function getAdditionalSizes(): array
    {
        return $this->additionalSizes;
    }

    /**
     * @param int $additionalIndex
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageAdditionalSizeConfig
     */
    public function getAdditionalSize(int $additionalIndex): ImageAdditionalSizeConfig
    {
        if (!isset($this->additionalSizes[$additionalIndex])) {
            throw new ImageAdditionalSizeNotFoundException($this->name, $additionalIndex);
        }

        return $this->additionalSizes[$additionalIndex];
    }
}
