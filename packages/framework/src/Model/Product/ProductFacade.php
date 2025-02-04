<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

class ProductFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler $productPriceRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade $productManualInputPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator $productHiddenRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFactoryInterface $productFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFactoryInterface $productAccessoryFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactoryInterface $productCategoryDomainFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueFactoryInterface $productParameterValueFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFactoryInterface $productVisibilityFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductExportScheduler $productExportScheduler
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly Domain $domain,
        protected readonly ImageFacade $imageFacade,
        protected readonly ProductPriceRecalculationScheduler $productPriceRecalculationScheduler,
        protected readonly PricingGroupRepository $pricingGroupRepository,
        protected readonly ProductManualInputPriceFacade $productManualInputPriceFacade,
        protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductHiddenRecalculator $productHiddenRecalculator,
        protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        protected readonly ProductAccessoryRepository $productAccessoryRepository,
        protected readonly PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        protected readonly ProductFactoryInterface $productFactory,
        protected readonly ProductAccessoryFactoryInterface $productAccessoryFactory,
        protected readonly ProductCategoryDomainFactoryInterface $productCategoryDomainFactory,
        protected readonly ProductParameterValueFactoryInterface $productParameterValueFactory,
        protected readonly ProductVisibilityFactoryInterface $productVisibilityFactory,
        protected readonly ProductPriceCalculation $productPriceCalculation,
        protected readonly ProductExportScheduler $productExportScheduler,
    ) {
    }

    /**
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductQueryParams $query
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function findByProductQueryParams(ProductQueryParams $query): PaginationResult
    {
        return $this->productRepository->findByProductQueryParams($query);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function create(ProductData $productData)
    {
        $product = $this->productFactory->create($productData);

        $this->em->persist($product);
        $this->em->flush();
        $this->setAdditionalDataAfterCreate($product, $productData);

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productData->pluginData);

        $this->productExportScheduler->scheduleRowIdForImmediateExport($product->getId());

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    public function setAdditionalDataAfterCreate(Product $product, ProductData $productData)
    {
        // Persist of ProductCategoryDomain requires known primary key of Product
        // @see https://github.com/doctrine/doctrine2/issues/4869
        $productCategoryDomains = $this->productCategoryDomainFactory->createMultiple(
            $product,
            $productData->categoriesByDomainId,
        );
        $product->setProductCategoryDomains($productCategoryDomains);
        $this->em->flush();

        $this->saveParameters($product, $productData->parameters);
        $this->createProductVisibilities($product);
        $this->refreshProductManualInputPrices($product, $productData->manualInputPricesByPricingGroupId);
        $this->refreshProductAccessories($product, $productData->accessories);
        $this->productHiddenRecalculator->calculateHiddenForProduct($product);
        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);

        $this->imageFacade->manageImages($product, $productData->images);
        $this->friendlyUrlFacade->createFriendlyUrls('front_product_detail', $product->getId(), $product->getNames());

        $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
        $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
        $this->productPriceRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
    }

    /**
     * @param int $productId
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function edit($productId, ProductData $productData)
    {
        $product = $this->productRepository->getById($productId);
        $originalNames = $product->getNames();

        $productCategoryDomains = $this->productCategoryDomainFactory->createMultiple(
            $product,
            $productData->categoriesByDomainId,
        );
        $product->edit($productCategoryDomains, $productData);
        $this->productPriceRecalculationScheduler->scheduleProductForImmediateRecalculation($product);

        $this->saveParameters($product, $productData->parameters);

        if (!$product->isMainVariant()) {
            $this->refreshProductManualInputPrices($product, $productData->manualInputPricesByPricingGroupId);
        } else {
            $product->refreshVariants($productData->variants);
        }
        $this->refreshProductAccessories($product, $productData->accessories);
        $this->em->flush();
        $this->productHiddenRecalculator->calculateHiddenForProduct($product);
        $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($product);
        $this->imageFacade->manageImages($product, $productData->images);
        $this->friendlyUrlFacade->saveUrlListFormData('front_product_detail', $product->getId(), $productData->urls);
        $this->createFriendlyUrlsWhenRenamed($product, $originalNames);

        $this->pluginCrudExtensionFacade->saveAllData('product', $product->getId(), $productData->pluginData);

        $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation($product);
        $this->productVisibilityFacade->refreshProductsVisibilityForMarkedDelayed();
        $this->productPriceRecalculationScheduler->scheduleProductForImmediateRecalculation($product);

        $productToExport = $product->isVariant() ? $product->getMainVariant() : $product;
        $this->productExportScheduler->scheduleRowIdForImmediateExport($productToExport->getId());

        return $product;
    }

    /**
     * @param int $productId
     */
    public function delete($productId)
    {
        $product = $this->productRepository->getById($productId);
        $productDeleteResult = $product->getProductDeleteResult();
        $productsForRecalculations = $productDeleteResult->getProductsForRecalculations();

        foreach ($productsForRecalculations as $productForRecalculations) {
            $this->productPriceRecalculationScheduler->scheduleProductForImmediateRecalculation(
                $productForRecalculations,
            );
            $productForRecalculations->markForVisibilityRecalculation();
            $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation(
                $productForRecalculations,
            );
            $this->productExportScheduler->scheduleRowIdForImmediateExport($productForRecalculations->getId());
        }

        $this->productExportScheduler->scheduleRowIdForImmediateExport($product->getId());

        $this->em->remove($product);
        $this->em->flush();

        $this->pluginCrudExtensionFacade->removeAllData('product', $product->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] $productParameterValuesData
     */
    protected function saveParameters(Product $product, array $productParameterValuesData)
    {
        // Doctrine runs INSERTs before DELETEs in UnitOfWork. In case of UNIQUE constraint
        // in database, this leads in trying to insert duplicate entry.
        // That's why it's necessary to do remove and flush first.

        $oldProductParameterValues = $this->parameterRepository->getProductParameterValuesByProduct($product);

        foreach ($oldProductParameterValues as $oldProductParameterValue) {
            $this->em->remove($oldProductParameterValue);
        }
        $this->em->flush();

        $toFlush = [];

        foreach ($productParameterValuesData as $productParameterValueData) {
            $productParameterValue = $this->productParameterValueFactory->create(
                $product,
                $productParameterValueData->parameter,
                $this->parameterRepository->findOrCreateParameterValueByValueTextAndLocale(
                    $productParameterValueData->parameterValueData->text,
                    $productParameterValueData->parameterValueData->locale,
                ),
            );
            $this->em->persist($productParameterValue);
            $toFlush[] = $productParameterValue;
        }

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[][]
     */
    public function getAllProductSellingPricesIndexedByDomainId(Product $product)
    {
        $productSellingPrices = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productSellingPrices[$domainId] = $this->getAllProductSellingPricesByDomainId($product, $domainId);
        }

        return $productSellingPrices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[]
     */
    public function getAllProductSellingPricesByDomainId(Product $product, int $domainId): array
    {
        $productSellingPrices = [];

        foreach ($this->pricingGroupRepository->getPricingGroupsByDomainId($domainId) as $pricingGroup) {
            try {
                $sellingPrice = $this->productPriceCalculation->calculatePrice($product, $domainId, $pricingGroup);
            } catch (MainVariantPriceCalculationException $e) {
                $sellingPrice = new ProductPrice(Price::zero(), false);
            }
            $productSellingPrices[$pricingGroup->getId()] = new ProductSellingPrice($pricingGroup, $sellingPrice);
        }

        return $productSellingPrices;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Money\Money[]|null[] $manualInputPrices
     */
    protected function refreshProductManualInputPrices(Product $product, array $manualInputPrices)
    {
        foreach ($this->pricingGroupRepository->getAll() as $pricingGroup) {
            $this->productManualInputPriceFacade->refresh(
                $product,
                $pricingGroup,
                $manualInputPrices[$pricingGroup->getId()],
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    protected function createProductVisibilities(Product $product)
    {
        $toFlush = [];

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();

            foreach ($this->pricingGroupRepository->getPricingGroupsByDomainId($domainId) as $pricingGroup) {
                $productVisibility = $this->productVisibilityFactory->create($product, $pricingGroup, $domainId);
                $this->em->persist($productVisibility);
                $toFlush[] = $productVisibility;
            }
        }

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $accessories
     */
    protected function refreshProductAccessories(Product $product, array $accessories)
    {
        $oldProductAccessories = $this->productAccessoryRepository->getAllByProduct($product);

        foreach ($oldProductAccessories as $oldProductAccessory) {
            $this->em->remove($oldProductAccessory);
        }
        $this->em->flush();

        $toFlush = [];

        foreach ($accessories as $position => $accessory) {
            $newProductAccessory = $this->productAccessoryFactory->create($product, $accessory, $position);
            $this->em->persist($newProductAccessory);
            $toFlush[] = $newProductAccessory;
        }

        if (count($toFlush) > 0) {
            $this->em->flush();
        }
    }

    /**
     * @param string $productCatnum
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getOneByCatnumExcludeMainVariants($productCatnum)
    {
        return $this->productRepository->getOneByCatnumExcludeMainVariants($productCatnum);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getByUuid(string $uuid): Product
    {
        return $this->productRepository->getOneByUuid($uuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function markProductsForExport(array $products): void
    {
        $this->productRepository->markProductsForExport($products);
    }

    public function markAllProductsForExport(): void
    {
        $this->productRepository->markAllProductsForExport();
    }

    public function markAllProductsAsExported(): void
    {
        $this->productRepository->markAllProductsAsExported();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsWithAvailability(Availability $availability): array
    {
        return $this->productRepository->getProductsWithAvailability($availability);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsWithParameter(Parameter $parameter): array
    {
        return $this->productRepository->getProductsWithParameter($parameter);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsWithBrand(Brand $brand): array
    {
        return $this->productRepository->getProductsWithBrand($brand);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsWithFlag(Flag $flag): array
    {
        return $this->productRepository->getProductsWithFlag($flag);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsWithUnit(Unit $unit): array
    {
        return $this->productRepository->getProductsWithUnit($unit);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param array $originalNames
     */
    protected function createFriendlyUrlsWhenRenamed(Product $product, array $originalNames): void
    {
        $changedNames = $this->getChangedNamesByLocale($product, $originalNames);

        if (count($changedNames) === 0) {
            return;
        }

        $this->friendlyUrlFacade->createFriendlyUrls(
            'front_product_detail',
            $product->getId(),
            $changedNames,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param array $originalNames
     * @return array
     */
    protected function getChangedNamesByLocale(Product $product, array $originalNames): array
    {
        $changedProductNames = [];

        foreach ($product->getNames() as $locale => $name) {
            if ($name !== null && $name !== $originalNames[$locale]) {
                $changedProductNames[$locale] = $name;
            }
        }

        return $changedProductNames;
    }
}
