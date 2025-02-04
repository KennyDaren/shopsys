<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="vats")
 * @ORM\Entity
 */
class Vat
{
    public const SETTING_DEFAULT_VAT = 'defaultVatId';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    protected $percent;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $replaceWith;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     * @param int $domainId
     */
    public function __construct(VatData $vatData, int $domainId)
    {
        $this->percent = $vatData->percent;
        $this->domainId = $domainId;
        $this->setData($vatData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     */
    public function edit(VatData $vatData)
    {
        $this->setData($vatData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData $vatData
     */
    protected function setData(VatData $vatData): void
    {
        $this->name = $vatData->name;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPercent(): string
    {
        return $this->percent;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public function getReplaceWith()
    {
        return $this->replaceWith;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $newVat
     */
    public function markForDeletion(self $newVat)
    {
        $this->replaceWith = $newVat;
    }

    public function isMarkedAsDeleted()
    {
        return $this->replaceWith !== null;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }
}
