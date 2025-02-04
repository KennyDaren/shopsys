<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Image;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageEntityConfigNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageSizeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigDefinition;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageConfigLoader;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use stdClass;
use Symfony\Component\Filesystem\Filesystem;

class ImageLocatorTest extends TestCase
{
    /**
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     */
    private function getBaseImageConfig()
    {
        $inputConfig = [
            [
                ImageConfigDefinition::CONFIG_CLASS => stdClass::class,
                ImageConfigDefinition::CONFIG_ENTITY_NAME => 'Name_1',
                ImageConfigDefinition::CONFIG_MULTIPLE => false,
                ImageConfigDefinition::CONFIG_SIZES => [
                    [
                        ImageConfigDefinition::CONFIG_SIZE_NAME => null,
                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                        ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                        ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [],
                    ],
                    [
                        ImageConfigDefinition::CONFIG_SIZE_NAME => 'SizeName_0_1',
                        ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                        ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                        ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                        ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                        ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [],
                    ],
                ],
                ImageConfigDefinition::CONFIG_TYPES => [
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_1',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                        ImageConfigDefinition::CONFIG_SIZES => [
                            [
                                ImageConfigDefinition::CONFIG_SIZE_NAME => 'SizeName_1_1',
                                ImageConfigDefinition::CONFIG_SIZE_WIDTH => null,
                                ImageConfigDefinition::CONFIG_SIZE_HEIGHT => null,
                                ImageConfigDefinition::CONFIG_SIZE_CROP => false,
                                ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                                ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [],
                            ],
                            [
                                ImageConfigDefinition::CONFIG_SIZE_NAME => null,
                                ImageConfigDefinition::CONFIG_SIZE_WIDTH => 200,
                                ImageConfigDefinition::CONFIG_SIZE_HEIGHT => 100,
                                ImageConfigDefinition::CONFIG_SIZE_CROP => true,
                                ImageConfigDefinition::CONFIG_SIZE_OCCURRENCE => null,
                                ImageConfigDefinition::CONFIG_SIZE_ADDITIONAL_SIZES => [],
                            ],
                        ],
                    ],
                    [
                        ImageConfigDefinition::CONFIG_TYPE_NAME => 'TypeName_2',
                        ImageConfigDefinition::CONFIG_MULTIPLE => false,
                        ImageConfigDefinition::CONFIG_SIZES => [],
                    ],
                ],
            ],
        ];

        $filesystem = new Filesystem();
        $entityNameResolver = new EntityNameResolver([]);
        $imageConfigLoader = new ImageConfigLoader($filesystem, $entityNameResolver);
        $imageEntityConfigByClass = $imageConfigLoader->loadFromArray($inputConfig);

        return new ImageConfig($imageEntityConfigByClass, $entityNameResolver);
    }

    public function getRelativeImagePathProvider()
    {
        return [
            [
                'Name_1',
                'TypeName_1',
                'SizeName_1_1',
                'Name_1/TypeName_1/SizeName_1_1/',
            ],
            [
                'Name_1',
                'TypeName_1',
                null,
                'Name_1/TypeName_1/' . ImageConfig::DEFAULT_SIZE_NAME . '/',
            ],
            [
                'Name_1',
                null,
                'SizeName_0_1',
                'Name_1/SizeName_0_1/',
            ],
            [
                'Name_1',
                null,
                null,
                'Name_1/' . ImageConfig::DEFAULT_SIZE_NAME . '/',
            ],
        ];
    }

    /**
     * @dataProvider getRelativeImagePathProvider
     * @param mixed $entityName
     * @param mixed $type
     * @param mixed $sizeName
     * @param mixed $expectedPath
     */
    public function testGetRelativeImagePath($entityName, $type, $sizeName, $expectedPath)
    {
        $filesystemMock = $this->createMock(FilesystemOperator::class);
        $imageLocator = new ImageLocator('imageDir', $this->getBaseImageConfig(), $filesystemMock);

        $this->assertSame($expectedPath, $imageLocator->getRelativeImagePath($entityName, $type, $sizeName));
    }

    public function getRelativeImagePathExceptionProvider()
    {
        return [
            [
                'NonexistentName',
                null,
                null,
                ImageEntityConfigNotFoundException::class,
            ],
            [
                'Name_1',
                'NonexistentTypeName',
                null,
                ImageTypeNotFoundException::class,
            ],
            [
                'Name_1',
                null,
                'NonexistentSizeName',
                ImageSizeNotFoundException::class,
            ],
        ];
    }

    /**
     * @dataProvider getRelativeImagePathExceptionProvider
     * @param mixed $entityName
     * @param mixed $type
     * @param mixed $sizeName
     * @param mixed $exceptionClass
     */
    public function testGetRelativeImagePathException($entityName, $type, $sizeName, $exceptionClass)
    {
        $filesystemMock = $this->createMock(FilesystemOperator::class);
        $imageLocator = new ImageLocator('imageDir', $this->getBaseImageConfig(), $filesystemMock);

        $this->expectException($exceptionClass);
        $imageLocator->getRelativeImagePath($entityName, $type, $sizeName);
    }
}
