<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * @ORM\Table(name="product_manual_input_prices")
 * @ORM\Entity
 */
class ProductManualInputPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Product")
     * @ORM\JoinColumn(nullable=false, name="product_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup")
     * @ORM\JoinColumn(nullable=false, name="pricing_group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $pricingGroup;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     * @ORM\Column(type="money", precision=20, scale=6, nullable=true)
     */
    protected $inputPrice;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $inputPrice
     */
    public function __construct(Product $product, PricingGroup $pricingGroup, ?Money $inputPrice)
    {
        $this->product = $product;
        $this->pricingGroup = $pricingGroup;
        $this->setInputPrice($inputPrice);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup()
    {
        return $this->pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public function getInputPrice(): ?Money
    {
        return $this->inputPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $inputPrice
     */
    public function setInputPrice(?Money $inputPrice)
    {
        $this->inputPrice = $inputPrice;
    }
}
