<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation;

class ProductFactory implements ProductFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation $productAvailabilityCalculation
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly ProductAvailabilityCalculation $productAvailabilityCalculation,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function create(ProductData $data): Product
    {
        $classData = $this->entityNameResolver->resolve(Product::class);

        $product = $classData::create($data);
        $this->setCalculatedAvailabilityIfMissing($product);

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function createMainVariant(ProductData $data, Product $mainProduct, array $variants): Product
    {
        $variants[] = $mainProduct;

        $classData = $this->entityNameResolver->resolve(Product::class);

        $mainVariant = $classData::createMainVariant($data, $variants);
        $this->setCalculatedAvailabilityIfMissing($mainVariant);

        return $mainVariant;
    }

    /**
     * When creating new product, ProductData::$availability is set to Product::$calculatedAvailability property as well.
     * This is a problem when ProductData::$availability === null as $calculatedAvailability is not nullable (unlike $availability).
     *
     * @see \Shopsys\FrameworkBundle\Model\Product\Product::__construct()
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    protected function setCalculatedAvailabilityIfMissing(Product $product)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Availability\Availability|null $calculatedAvailability */
        $calculatedAvailability = $product->getCalculatedAvailability();

        if ($calculatedAvailability !== null) {
            return;
        }

        $availability = $this->productAvailabilityCalculation->calculateAvailability($product);
        $product->setCalculatedAvailability($availability);
    }
}
