<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Prezent\Doctrine\Translatable\Annotation as Prezent;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Localization\AbstractTranslatableEntity;

/**
 * @ORM\Table(name="flags")
 * @ORM\Entity
 * @method \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation translation(?string $locale = null)
 */
class Flag extends AbstractTranslatableEntity
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation[]|\Doctrine\Common\Collections\Collection
     * @Prezent\Translations(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation")
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $translations;

    /**
     * @var string
     * @ORM\Column(type="string", length=7)
     */
    protected $rgbColor;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $visible;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     */
    public function __construct(FlagData $flagData)
    {
        $this->uuid = $flagData->uuid ?: Uuid::uuid4()->toString();

        $this->translations = new ArrayCollection();
        $this->setData($flagData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     */
    public function edit(FlagData $flagData)
    {
        $this->setData($flagData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     */
    protected function setData(FlagData $flagData): void
    {
        $this->setTranslations($flagData);
        $this->rgbColor = $flagData->rgbColor;
        $this->visible = $flagData->visible;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string|null $locale
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->translation($locale)->getName();
    }

    /**
     * @return string
     */
    public function getRgbColor()
    {
        return $this->rgbColor;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagData $flagData
     */
    protected function setTranslations(FlagData $flagData)
    {
        foreach ($flagData->name as $locale => $name) {
            $this->translation($locale)->setName($name);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagTranslation
     */
    protected function createTranslation()
    {
        return new FlagTranslation();
    }
}
