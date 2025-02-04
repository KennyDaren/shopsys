<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Pricing;

use Doctrine\ORM\EntityManager;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPriceRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\FrameworkBundle\Unit\TestCase;

class ProductPriceRecalculatorTest extends TestCase
{
    public function testRunImmediatelyRecalculations()
    {
        $productMock = $this->getMockBuilder(Product::class)->setMethods(
            null,
        )->disableOriginalConstructor()->getMock();

        $this->setValueOfProtectedProperty($productMock, 'variantType', Product::VARIANT_TYPE_NONE);

        $pricingGroupMock = $this->getMockBuilder(PricingGroup::class)->setMethods(
            null,
        )->disableOriginalConstructor()->getMock();

        $this->setValueOfProtectedProperty($pricingGroupMock, 'domainId', Domain::FIRST_DOMAIN_ID);

        $emMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['clear', 'flush'])
            ->getMock();
        $productPriceCalculationMock = $this->getMockBuilder(ProductPriceCalculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['calculatePrice'])
            ->getMock();
        $productPrice = new ProductPrice(Price::zero(), false);
        $productPriceCalculationMock->expects($this->once())->method('calculatePrice')->willReturn($productPrice);
        $productCalculatedPriceRepositoryMock = $this->getMockBuilder(ProductCalculatedPriceRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveCalculatedPrice'])
            ->getMock();
        $productPriceRecalculationSchedulerMock = $this->getMockBuilder(ProductPriceRecalculationScheduler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductsForImmediateRecalculation'])
            ->getMock();
        $productPriceRecalculationSchedulerMock->expects($this->once())->method(
            'getProductsForImmediateRecalculation',
        )->willReturn(
            [$productMock],
        );
        $pricingGroupFacadeMock = $this->getMockBuilder(PricingGroupFacade::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAll'])
            ->getMock();
        $pricingGroupFacadeMock->expects($this->once())->method('getAll')->willReturn([$pricingGroupMock]);

        $productPriceRecalculator = new ProductPriceRecalculator(
            $emMock,
            $productPriceCalculationMock,
            $productCalculatedPriceRepositoryMock,
            $productPriceRecalculationSchedulerMock,
            $pricingGroupFacadeMock,
        );

        $productPriceRecalculator->runImmediateRecalculations();
    }
}
