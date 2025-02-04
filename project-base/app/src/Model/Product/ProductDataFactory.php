<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Component\Setting\Setting;
use App\Model\ProductVideo\ProductVideoDataFactory;
use App\Model\ProductVideo\ProductVideoRepository;
use App\Model\Stock\ProductStockDataFactory;
use App\Model\Stock\ProductStockFacade;
use App\Model\Stock\StockFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory as BaseProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Unit\UnitFacade;

/**
 * @method \App\Model\Product\Product[] getAccessoriesData(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData[] getParametersData(\App\Model\Product\Product $product)
 */
class ProductDataFactory extends BaseProductDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \App\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade
     * @param \App\Model\Product\Unit\UnitFacade $unitFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginDataFormExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueDataFactory $productParameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupFacade $pricingGroupFacade
     * @param \App\Model\Product\Availability\AvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \App\Model\Stock\ProductStockFacade $stockProductFacade
     * @param \App\Model\Stock\StockFacade $stockFacade
     * @param \App\Model\Stock\ProductStockDataFactory $stockProductDataFactory
     * @param \App\Model\Product\ProductFacade $productFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \App\Model\ProductVideo\ProductVideoDataFactory $productVideoDataFactory
     * @param \App\Model\ProductVideo\ProductVideoRepository $productVideoRepository
     */
    public function __construct(
        VatFacade $vatFacade,
        ProductInputPriceFacade $productInputPriceFacade,
        UnitFacade $unitFacade,
        Domain $domain,
        ParameterRepository $parameterRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        ProductAccessoryRepository $productAccessoryRepository,
        PluginCrudExtensionFacade $pluginDataFormExtensionFacade,
        ProductParameterValueDataFactoryInterface $productParameterValueDataFactory,
        PricingGroupFacade $pricingGroupFacade,
        AvailabilityFacade $availabilityFacade,
        ImageUploadDataFactory $imageUploadDataFactory,
        private readonly ProductStockFacade $stockProductFacade,
        private readonly StockFacade $stockFacade,
        private readonly ProductStockDataFactory $stockProductDataFactory,
        private readonly ProductFacade $productFacade,
        private readonly Setting $setting,
        private readonly ProductVideoDataFactory $productVideoDataFactory,
        private readonly ProductVideoRepository $productVideoRepository,
    ) {
        parent::__construct(
            $vatFacade,
            $productInputPriceFacade,
            $unitFacade,
            $domain,
            $parameterRepository,
            $friendlyUrlFacade,
            $productAccessoryRepository,
            $pluginDataFormExtensionFacade,
            $productParameterValueDataFactory,
            $pricingGroupFacade,
            $availabilityFacade,
            $imageUploadDataFactory,
        );
    }

    /**
     * @return \App\Model\Product\ProductData
     */
    protected function createInstance(): BaseProductData
    {
        $productData = new ProductData();
        $productData->images = $this->imageUploadDataFactory->create();

        return $productData;
    }

    /**
     * @return \App\Model\Product\ProductData
     */
    public function create(): BaseProductData
    {
        $productData = $this->createInstance();
        $this->fillNew($productData);
        $this->fillStockProductByStocks($productData);

        return $productData;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\ProductData
     */
    public function createFromProduct(BaseProduct $product): BaseProductData
    {
        $productData = $this->createInstance();
        $this->fillFromProduct($productData, $product);
        $this->fillStockProductByProduct($productData, $product);
        $this->fillProductFilesAttributesFromProduct($productData, $product);
        $this->fillProductVideosByProductId($productData, $product);

        return $productData;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    protected function fillNew(BaseProductData $productData): void
    {
        parent::fillNew($productData);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->shortDescriptionUsp1[$domainId] = null;
            $productData->shortDescriptionUsp2[$domainId] = null;
            $productData->shortDescriptionUsp3[$domainId] = null;
            $productData->shortDescriptionUsp4[$domainId] = null;
            $productData->shortDescriptionUsp5[$domainId] = null;
            $productData->assemblyInstructionFileUrl[$domainId] = null;
            $productData->productTypePlanFileUrl[$domainId] = null;
            $productData->flags[$domainId] = [];
            $productData->saleExclusion[$domainId] = false;
            $productData->domainHidden[$domainId] = false;
            $productData->domainOrderingPriority[$domainId] = 0;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $productData->namePrefix[$locale] = null;
            $productData->nameSufix[$locale] = null;
        }

        $productData->preorder = false;
        $productData->availability = $this->availabilityFacade->getById($this->setting->get('defaultAvailabilityInStockId'));
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    protected function fillFromProduct(BaseProductData $productData, BaseProduct $product): void
    {
        /** @var \App\Model\Product\ProductTranslation[] $translations */
        $translations = $product->getTranslations();

        foreach ($translations as $translation) {
            $locale = $translation->getLocale();

            $productData->name[$locale] = $translation->getName();
            $productData->variantAlias[$locale] = $translation->getVariantAlias();
            $productData->namePrefix[$locale] = $translation->getNamePrefix();
            $productData->nameSufix[$locale] = $translation->getNameSufix();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->shortDescriptions[$domainId] = $product->getShortDescription($domainId);
            $productData->descriptions[$domainId] = $product->getDescription($domainId);
            $productData->seoH1s[$domainId] = $product->getSeoH1($domainId);
            $productData->seoTitles[$domainId] = $product->getSeoTitle($domainId);
            $productData->seoMetaDescriptions[$domainId] = $product->getSeoMetaDescription($domainId);
            $productData->vatsIndexedByDomainId[$domainId] = $product->getVatForDomain($domainId);

            $productData->shortDescriptionUsp1[$domainId] = $product->getShortDescriptionUsp1($domainId);
            $productData->shortDescriptionUsp2[$domainId] = $product->getShortDescriptionUsp2($domainId);
            $productData->shortDescriptionUsp3[$domainId] = $product->getShortDescriptionUsp3($domainId);
            $productData->shortDescriptionUsp4[$domainId] = $product->getShortDescriptionUsp4($domainId);
            $productData->shortDescriptionUsp5[$domainId] = $product->getShortDescriptionUsp5($domainId);
            $productData->flags[$domainId] = $product->getFlagsForDomain($domainId);
            $productData->saleExclusion[$domainId] = $product->getSaleExclusion($domainId);
            $productData->domainHidden[$domainId] = $product->isDomainHidden($domainId);
            $productData->domainOrderingPriority[$domainId] = $product->getDomainOrderingPriority($domainId);

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
                $domainId,
                'front_product_detail',
                $product->getId(),
            );
            $productData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }

        $productData->catnum = $product->getCatnum();
        $productData->partno = $product->getPartno();
        $productData->ean = $product->getEan();
        $productData->sellingFrom = $product->getSellingFrom();
        $productData->sellingTo = $product->getSellingTo();
        $productData->sellingDenied = $product->isSellingDenied();

        $productData->availability = $this->availabilityFacade->getById($this->setting->get('defaultAvailabilityInStockId'));

        $productData->unit = $product->getUnit();

        $productData->hidden = $product->isHidden();
        $productData->categoriesByDomainId = $product->getCategoriesIndexedByDomainId();
        $productData->brand = $product->getBrand();
        $productData->orderingPriority = $product->getOrderingPriority();

        $productData->parameters = $this->getParametersData($product);

        try {
            $productData->manualInputPricesByPricingGroupId = $this->productInputPriceFacade->getManualInputPricesDataIndexedByPricingGroupId($product);
        } catch (MainVariantPriceCalculationException $ex) {
            $productData->manualInputPricesByPricingGroupId = $this->getNullForAllPricingGroups();
        }

        /** @var \App\Model\Product\Product[] $productAccessories */
        $productAccessories = $this->getAccessoriesData($product);

        $productData->accessories = $productAccessories;
        $productData->images = $this->imageUploadDataFactory->createFromEntityAndType($product, null);
        $productData->variants = $product->getVariants();
        $productData->pluginData = $this->pluginDataFormExtensionFacade->getAllData('product', $product->getId());

        $productData->downloadAssemblyInstructionFiles = $product->isDownloadAssemblyInstructionFiles();
        $productData->downloadProductTypePlanFiles = $product->isDownloadAssemblyInstructionFiles();

        $productData->preorder = $product->hasPreorder();
        $productData->vendorDeliveryDate = $product->getVendorDeliveryDate();
        $productData->weight = $product->getWeight();
        $productData->relatedProducts = $product->getRelatedProducts();
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    private function fillProductFilesAttributesFromProduct(ProductData $productData, Product $product): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $productData->assemblyInstructionFileUrl[$domainId] = null;
            $productData->productTypePlanFileUrl[$domainId] = null;

            if ($product->getAssemblyInstructionCode($domainId) !== null) {
                $productData->assemblyInstructionFileUrl[$domainId] = $this->productFacade->getProductTransferredFileUrl(
                    $product->getProductFileNameByType($domainId, Product::FILE_IDENTIFICATOR_ASSEMBLY_INSTRUCTION_TYPE),
                    $domainConfig->getUrl(),
                );
            }

            if ($product->getProductTypePlanCode($domainId) !== null) {
                $productData->productTypePlanFileUrl[$domainId] = $this->productFacade->getProductTransferredFileUrl(
                    $product->getProductFileNameByType($domainId, Product::FILE_IDENTIFICATOR_PRODUCT_TYPE_PLAN_TYPE),
                    $domainConfig->getUrl(),
                );
            }
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function fillStockProductByStocks(ProductData $productData): void
    {
        foreach ($this->stockFacade->getAllStocks() as $stock) {
            $productData->stockProductData[$stock->getId()] = $this->stockProductDataFactory->createFromStock($stock);
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    private function fillStockProductByProduct(ProductData $productData, Product $product): void
    {
        $this->fillStockProductByStocks($productData);

        foreach ($this->stockProductFacade->getProductStocksByProduct($product) as $stockProduct) {
            $productData->stockProductData[$stockProduct->getStock()->getId()] = $this->stockProductDataFactory->createFromProductStock($stockProduct);
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @param \App\Model\Product\Product $product
     */
    private function fillProductVideosByProductId(ProductData $productData, Product $product): void
    {
        foreach ($this->productVideoRepository->findByProductId($product->getId()) as $video) {
            $productData->productVideosData[$video->getid()] = $this->productVideoDataFactory->createFromProductVideo($video);
        }
    }
}
