<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;

class ProductSellingDeniedRecalculator
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->em = $entityManager;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function calculateSellingDeniedForProduct(Product $product)
    {
        $products = $this->getProductsForCalculations($product);
        $this->calculate($products);
    }

    public function calculateSellingDeniedForAll()
    {
        $this->calculate();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    protected function calculate(array $products = [])
    {
        $this->calculateIndependent($products);
        $this->propagateMainVariantSellingDeniedToVariants($products);
        $this->propagateVariantsSellingDeniedToMainVariant($products);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getProductsForCalculations(Product $product)
    {
        $products = [$product];

        if ($product->isMainVariant()) {
            $products = array_merge($products, $product->getVariants());
        } elseif ($product->isVariant()) {
            $products[] = $product->getMainVariant();
        }

        return $products;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    protected function calculateIndependent(array $products)
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedSellingDenied', '
                CASE
                    WHEN p.usingStock = TRUE
                        AND p.stockQuantity <= 0
                        AND p.outOfStockAction = :outOfStockActionExcludeFromSale
                    THEN TRUE
                    ELSE p.sellingDenied
                END
            ')
            ->setParameter('outOfStockActionExcludeFromSale', Product::OUT_OF_STOCK_ACTION_EXCLUDE_FROM_SALE);

        if (count($products) > 0) {
            $qb->andWhere('p IN (:products)')->setParameter('products', $products);
        }
        $qb->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    protected function propagateMainVariantSellingDeniedToVariants(array $products)
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedSellingDenied', 'TRUE')
            ->andWhere('p.variantType = :variantTypeVariant')
            ->andWhere('p.calculatedSellingDenied = FALSE')
            ->andWhere(
                'EXISTS (
                    SELECT 1
                    FROM ' . Product::class . ' m
                    WHERE m = p.mainVariant
                        AND m.calculatedSellingDenied = TRUE
                )',
            )
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT);

        if (count($products) > 0) {
            $qb->andWhere('p IN (:products)')->setParameter('products', $products);
        }
        $qb->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    protected function propagateVariantsSellingDeniedToMainVariant(array $products)
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedSellingDenied', 'TRUE')
            ->andWhere('p.variantType = :variantTypeMain')
            ->andWhere('p.calculatedSellingDenied = FALSE')
            ->andWhere(
                'NOT EXISTS (
                    SELECT 1
                    FROM ' . Product::class . ' v
                    WHERE v.mainVariant = p
                        AND v.calculatedSellingDenied = FALSE
                )',
            )
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN);

        if (count($products) > 0) {
            $qb->andWhere('p IN (:products)')->setParameter('products', $products);
        }
        $qb->getQuery()->execute();
    }
}
