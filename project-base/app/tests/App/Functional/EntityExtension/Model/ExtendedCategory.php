<?php

declare(strict_types=1);

namespace Tests\App\Functional\EntityExtension\Model;

use App\Model\Category\CategoryData;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Category\Category;

/**
 * @ORM\Entity
 * @ORM\Table(name="categories")
 */
class ExtendedCategory extends Category
{
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $stringField;

    /**
     * @ORM\ManyToOne(targetEntity="UnidirectionalEntity")
     * @ORM\JoinColumn(nullable=true, name="manyToOneUnidirectionalEntity_id", referencedColumnName="id")
     */
    protected UnidirectionalEntity $manyToOneUnidirectionalEntity;

    /**
     * @ORM\OneToOne(targetEntity="UnidirectionalEntity")
     * @ORM\JoinColumn(nullable=true, name="oneToOneUnidirectionalEntity_id", referencedColumnName="id")
     */
    protected UnidirectionalEntity $oneToOneUnidirectionalEntity;

    /**
     * @ORM\OneToOne(targetEntity="CategoryOneToOneBidirectionalEntity", mappedBy="category")
     * @ORM\JoinColumn(nullable=true)
     */
    protected CategoryOneToOneBidirectionalEntity $oneToOneBidirectionalEntity;

    /**
     * @ORM\OneToOne(targetEntity="ExtendedCategory")
     * @ORM\JoinColumn(nullable=true, name="oneToOneSelfReferencing_id", referencedColumnName="id")
     */
    protected ExtendedCategory $oneToOneSelfReferencingEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\CategoryOneToManyBidirectionalEntity[]
     * @ORM\OneToMany(targetEntity="CategoryOneToManyBidirectionalEntity", mappedBy="category")
     */
    protected Collection|array $oneToManyBidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity[]
     * @ORM\ManyToMany(targetEntity="UnidirectionalEntity")
     * @ORM\JoinTable(name="categories_oneToManyUnidirectionalWithJoinTableEntity",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="oneToManyUnidirectionalWithJoinTableEntity_id", referencedColumnName="id", unique=true)}
     *      )
     */
    protected Collection|array $oneToManyUnidirectionalWithJoinTableEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ExtendedCategory[]
     * @ORM\OneToMany(targetEntity="ExtendedCategory", mappedBy="oneToManySelfReferencingInverseEntity")
     */
    protected Collection|array $oneToManySelfReferencingEntities;

    /**
     * @ORM\ManyToOne(targetEntity="ExtendedCategory", inversedBy="oneToManySelfReferencingEntities")
     * @ORM\JoinColumn(nullable=true, name="oneToManySelfReferencingParent_id", referencedColumnName="id")
     */
    protected Collection|ExtendedCategory $oneToManySelfReferencingInverseEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity[]
     * @ORM\ManyToMany(targetEntity="UnidirectionalEntity")
     * @ORM\JoinTable(name="categories_manyToManyUnidirectionalEntity",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manyToManyUnidirectionalEntity_id", referencedColumnName="id")}
     *      )
     */
    protected Collection|array $manyToManyUnidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\CategoryManyToManyBidirectionalEntity[]
     * @ORM\ManyToMany(targetEntity="CategoryManyToManyBidirectionalEntity", inversedBy="categories")
     * @ORM\JoinTable(name="categories_manyToManyBidirectionalEntity")
     */
    protected Collection|array $manyToManyBidirectionalEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ExtendedCategory[]
     * @ORM\ManyToMany(targetEntity="ExtendedCategory", mappedBy="manyToManySelfReferencingInverseEntities")
     */
    protected Collection|array $manyToManySelfReferencingEntities;

    /**
     * @var \Doctrine\Common\Collections\Collection|\Tests\App\Functional\EntityExtension\Model\ExtendedCategory[]
     * @ORM\ManyToMany(targetEntity="ExtendedCategory", inversedBy="manyToManySelfReferencingEntities")
     * @ORM\JoinTable(name="categories_manyToManySelfReferencing",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manyToManySelfReferencing_id", referencedColumnName="id")}
     *      )
     */
    protected Collection|array $manyToManySelfReferencingInverseEntities;

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     */
    public function __construct(CategoryData $categoryData)
    {
        parent::__construct($categoryData);

        $this->oneToManyBidirectionalEntities = new ArrayCollection();
        $this->oneToManyUnidirectionalWithJoinTableEntities = new ArrayCollection();
        $this->oneToManySelfReferencingEntities = new ArrayCollection();
        $this->manyToManyUnidirectionalEntities = new ArrayCollection();
        $this->manyToManyBidirectionalEntities = new ArrayCollection();
        $this->manyToManySelfReferencingEntities = new ArrayCollection();
        $this->manyToManySelfReferencingInverseEntities = new ArrayCollection();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity
     */
    public function getManyToOneUnidirectionalEntity(): UnidirectionalEntity
    {
        return $this->manyToOneUnidirectionalEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity $manyToOneUnidirectionalEntity
     */
    public function setManyToOneUnidirectionalEntity(UnidirectionalEntity $manyToOneUnidirectionalEntity): void
    {
        $this->manyToOneUnidirectionalEntity = $manyToOneUnidirectionalEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity
     */
    public function getOneToOneUnidirectionalEntity(): UnidirectionalEntity
    {
        return $this->oneToOneUnidirectionalEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity $oneToOneUnidirectionalEntity
     */
    public function setOneToOneUnidirectionalEntity(UnidirectionalEntity $oneToOneUnidirectionalEntity): void
    {
        $this->oneToOneUnidirectionalEntity = $oneToOneUnidirectionalEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\CategoryOneToOneBidirectionalEntity
     */
    public function getOneToOneBidirectionalEntity(): CategoryOneToOneBidirectionalEntity
    {
        return $this->oneToOneBidirectionalEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\CategoryOneToOneBidirectionalEntity $oneToOneBidirectionalEntity
     */
    public function setOneToOneBidirectionalEntity(
        CategoryOneToOneBidirectionalEntity $oneToOneBidirectionalEntity,
    ): void {
        $oneToOneBidirectionalEntity->setCategory($this);
        $this->oneToOneBidirectionalEntity = $oneToOneBidirectionalEntity;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory
     */
    public function getOneToOneSelfReferencingEntity(): self
    {
        return $this->oneToOneSelfReferencingEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory $oneToOneSelfReferencing
     */
    public function setOneToOneSelfReferencingEntity(self $oneToOneSelfReferencing): void
    {
        $this->oneToOneSelfReferencingEntity = $oneToOneSelfReferencing;
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\CategoryOneToManyBidirectionalEntity[]
     */
    public function getOneToManyBidirectionalEntities(): array
    {
        return $this->oneToManyBidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\CategoryOneToManyBidirectionalEntity $oneToManyBidirectionalEntity
     */
    public function addOneToManyBidirectionalEntity(
        CategoryOneToManyBidirectionalEntity $oneToManyBidirectionalEntity,
    ): void {
        $oneToManyBidirectionalEntity->setCategory($this);
        $this->oneToManyBidirectionalEntities->add($oneToManyBidirectionalEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity[]
     */
    public function getOneToManyUnidirectionalWithJoinTableEntities(): array
    {
        return $this->oneToManyUnidirectionalWithJoinTableEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity $oneToManyUnidirectionalWithJoinTableEntity
     */
    public function addOneToManyUnidirectionalWithJoinTableEntity(
        UnidirectionalEntity $oneToManyUnidirectionalWithJoinTableEntity,
    ): void {
        $this->oneToManyUnidirectionalWithJoinTableEntities->add($oneToManyUnidirectionalWithJoinTableEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory[]
     */
    public function getOneToManySelfReferencingEntities(): array
    {
        return $this->oneToManySelfReferencingEntities->getValues();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory
     */
    public function getOneToManySelfReferencingInverseEntity(): self
    {
        return $this->oneToManySelfReferencingInverseEntity;
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory $oneToManySelfReferencing
     */
    public function addOneToManySelfReferencingEntity(self $oneToManySelfReferencing): void
    {
        $oneToManySelfReferencing->oneToManySelfReferencingInverseEntity = $this;
        $this->oneToManySelfReferencingEntities->add($oneToManySelfReferencing);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity[]
     */
    public function getManyToManyUnidirectionalEntities(): array
    {
        return $this->manyToManyUnidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\UnidirectionalEntity $manyToManyUnidirectionalEntity
     */
    public function addManyToManyUnidirectionalEntity(UnidirectionalEntity $manyToManyUnidirectionalEntity): void
    {
        $this->manyToManyUnidirectionalEntities->add($manyToManyUnidirectionalEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\CategoryManyToManyBidirectionalEntity[]
     */
    public function getManyToManyBidirectionalEntities(): array
    {
        return $this->manyToManyBidirectionalEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\CategoryManyToManyBidirectionalEntity $manyToManyBidirectionalEntity
     */
    public function addManyToManyBidirectionalEntity(
        CategoryManyToManyBidirectionalEntity $manyToManyBidirectionalEntity,
    ): void {
        $manyToManyBidirectionalEntity->addCategory($this);
        $this->manyToManyBidirectionalEntities->add($manyToManyBidirectionalEntity);
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory[]
     */
    public function getManyToManySelfReferencingEntities(): array
    {
        return $this->manyToManySelfReferencingEntities->getValues();
    }

    /**
     * @return \Tests\App\Functional\EntityExtension\Model\ExtendedCategory[]
     */
    public function getManyToManySelfReferencingInverseEntities(): array
    {
        return $this->manyToManySelfReferencingInverseEntities->getValues();
    }

    /**
     * @param \Tests\App\Functional\EntityExtension\Model\ExtendedCategory $manyToManySelfReferencing
     */
    public function addManyToManySelfReferencingEntity(self $manyToManySelfReferencing): void
    {
        $manyToManySelfReferencing->manyToManySelfReferencingInverseEntities->add($this);
        $this->manyToManySelfReferencingEntities->add($manyToManySelfReferencing);
    }

    /**
     * @return string|null
     */
    public function getStringField()
    {
        return $this->stringField;
    }

    /**
     * @param string|null $stringField
     */
    public function setStringField($stringField): void
    {
        $this->stringField = $stringField;
    }
}
