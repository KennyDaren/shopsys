<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class PriceRangeRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
     */
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly QueryBuilderExtender $queryBuilderExtender,
    ) {
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRangeInCategory($domainId, PricingGroup $pricingGroup, Category $category)
    {
        $productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRangeForBrand(int $domainId, PricingGroup $pricingGroup, Brand $brand): PriceRange
    {
        $productsQueryBuilder = $this->productRepository->getListableForBrandQueryBuilder(
            $domainId,
            $pricingGroup,
            $brand,
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRangeForAll(int $domainId, PricingGroup $pricingGroup): PriceRange
    {
        $productsQueryBuilder = $this->productRepository->getAllListableQueryBuilder(
            $domainId,
            $pricingGroup,
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    public function getPriceRangeForSearch($domainId, PricingGroup $pricingGroup, $locale, $searchText)
    {
        $productsQueryBuilder = $this->productRepository
            ->getListableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    protected function getPriceRangeByProductsQueryBuilder(
        QueryBuilder $productsQueryBuilder,
        PricingGroup $pricingGroup,
    ) {
        $queryBuilder = clone $productsQueryBuilder;

        $this->queryBuilderExtender
            ->addOrExtendJoin($queryBuilder, ProductCalculatedPrice::class, 'pcp', 'pcp.product = p')
            ->andWhere('pcp.pricingGroup = :pricingGroup')
            ->setParameter('pricingGroup', $pricingGroup)
            ->resetDQLPart('groupBy')
            ->resetDQLPart('orderBy')
            ->select('MIN(pcp.priceWithVat) AS minimalPrice, MAX(pcp.priceWithVat) AS maximalPrice');

        $priceRangeData = $queryBuilder->getQuery()->execute();
        $priceRangeDataRow = reset($priceRangeData);

        return new PriceRange(
            Money::create($priceRangeDataRow['minimalPrice'] ?? 0),
            Money::create($priceRangeDataRow['maximalPrice'] ?? 0),
        );
    }
}
