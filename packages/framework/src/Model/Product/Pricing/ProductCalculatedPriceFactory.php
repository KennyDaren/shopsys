<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductCalculatedPriceFactory implements ProductCalculatedPriceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $priceWithVat
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice
     */
    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        ?Money $priceWithVat,
    ): ProductCalculatedPrice {
        $classData = $this->entityNameResolver->resolve(ProductCalculatedPrice::class);

        return new $classData($product, $pricingGroup, $priceWithVat);
    }
}
