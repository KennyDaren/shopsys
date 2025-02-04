<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Image\Processing;

use League\Flysystem\FilesystemOperator;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Image\Processing\Exception\OriginalSizeImageCannotBeGeneratedException;

class ImageGenerator
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Processing\ImageProcessor $imageProcessor
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageLocator $imageLocator
     * @param \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig $imageConfig
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly ImageProcessor $imageProcessor,
        protected readonly ImageLocator $imageLocator,
        protected readonly ImageConfig $imageConfig,
        protected readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @param string|null $sizeName
     * @return string
     */
    public function generateImageSizeAndGetFilepath(Image $image, $sizeName)
    {
        $this->checkSizeNameIsNotOriginal($image, $sizeName);

        $sourceImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, ImageConfig::ORIGINAL_SIZE_NAME);
        $targetImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, $sizeName);
        $sizeConfig = $this->imageConfig->getImageSizeConfigByImage($image, $sizeName);

        $interventionImage = $this->imageProcessor->createInterventionImage($sourceImageFilepath);
        $this->imageProcessor->resizeBySizeConfig($interventionImage, $sizeConfig);

        $interventionImage->encode();

        $this->filesystem->delete($targetImageFilepath);
        $this->filesystem->write($targetImageFilepath, $interventionImage->getEncoded());

        return $targetImageFilepath;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @param int $additionalIndex
     * @param string|null $sizeName
     * @return string
     */
    public function generateAdditionalImageSizeAndGetFilepath(Image $image, int $additionalIndex, ?string $sizeName)
    {
        $this->checkSizeNameIsNotOriginal($image, $sizeName);

        $sourceImageFilepath = $this->imageLocator->getAbsoluteImageFilepath($image, ImageConfig::ORIGINAL_SIZE_NAME);
        $targetImageFilepath = $this->imageLocator->getAbsoluteAdditionalImageFilepath(
            $image,
            $additionalIndex,
            $sizeName,
        );
        $sizeConfig = $this->imageConfig->getImageSizeConfigByImage($image, $sizeName);
        $additionalSizeConfig = $sizeConfig->getAdditionalSize($additionalIndex);

        $interventionImage = $this->imageProcessor->createInterventionImage($sourceImageFilepath);
        $this->imageProcessor->resizeByAdditionalSizeConfig($interventionImage, $sizeConfig, $additionalSizeConfig);

        $interventionImage->encode();

        $this->filesystem->delete($targetImageFilepath);
        $this->filesystem->write($targetImageFilepath, $interventionImage->getEncoded());

        return $targetImageFilepath;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\Image $image
     * @param string|null $sizeName
     */
    protected function checkSizeNameIsNotOriginal(Image $image, ?string $sizeName): void
    {
        if ($sizeName === ImageConfig::ORIGINAL_SIZE_NAME) {
            throw new OriginalSizeImageCannotBeGeneratedException($image);
        }
    }
}
