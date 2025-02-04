<?php

declare(strict_types=1);

namespace App\Model\Category;

use App\Model\Category\LinkedCategory\LinkedCategoryFacade;
use App\Model\Product\Filter\ProductFilterData;
use App\Model\Product\ProductFacade;
use App\Model\Product\ProductOnCurrentDomainElasticFacade;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Category as BaseCategory;
use Shopsys\FrameworkBundle\Model\Category\CategoryData;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade as BaseCategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory;
use Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildrenFactory;

/**
 * @property \App\Model\Category\CategoryRepository $categoryRepository
 * @method \App\Model\Category\Category getById(int $categoryId)
 * @method \App\Model\Category\Category getByUuid(string $categoryUuid)
 * @method \App\Model\Category\Category[] getAllCategoriesOfCollapsedTree(\App\Model\Category\Category[] $selectedCategories)
 * @method \App\Model\Category\Category[] getFullPathsIndexedByIdsForDomain(int $domainId, string $locale)
 * @method \App\Model\Category\Category[] getVisibleCategoriesInPathFromRootOnDomain(\App\Model\Category\Category $category, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildren[] getCategoriesWithLazyLoadedVisibleChildrenForParent(\App\Model\Category\Category $parentCategory, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category[] getVisibleByDomainAndSearchText(int $domainId, string $locale, string $searchText)
 * @method \App\Model\Category\Category[] getAllVisibleChildrenByCategoryAndDomainId(\App\Model\Category\Category $category, int $domainId)
 * @method \App\Model\Category\Category[]|null[] getProductMainCategoriesIndexedByDomainId(\App\Model\Product\Product $product)
 * @method \App\Model\Category\Category getProductMainCategoryByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category|null findProductMainCategoryByDomainId(\App\Model\Product\Product $product, int $domainId)
 * @method string[] getCategoryNamesInPathFromRootToProductMainCategoryOnDomain(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Category\Category getRootCategory()
 * @method \App\Model\Category\Category getVisibleOnDomainById(int $domainId, int $categoryId)
 * @method int[] getListableProductCountsIndexedByCategoryId(\App\Model\Category\Category[] $categories, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $domainId)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method \App\Model\Category\Category[] getAllTranslated(string $locale)
 * @method \App\Model\Category\Category[] getAllTranslatedWithoutBranch(\App\Model\Category\Category $category, string $locale)
 * @method createFriendlyUrlsWhenRenamed(\App\Model\Category\Category $category, array $originalNames)
 * @method array getChangedNamesByLocale(\App\Model\Category\Category $category, array $originalNames)
 * @method \App\Model\Category\Category[] getByIds(int[] $categoryIds)
 * @method \App\Model\Category\Category getVisibleOnDomainByUuid(int $domainId, string $categoryUuid)
 * @method \App\Model\Category\Category getProductMainCategoryOnCurrentDomain(\App\Model\Product\Product $product)
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
 */
class CategoryFacade extends BaseCategoryFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginCrudExtensionFacade $pluginCrudExtensionFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFactory $categoryFactory
     * @param \App\Model\Category\CategoryParameterFacade $categoryParameterFacade
     * @param \App\Model\Category\LinkedCategory\LinkedCategoryFacade $linkedCategoryFacade
     * @param \App\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
     * @param \App\Model\Product\ProductFacade $productFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CategoryRepository $categoryRepository,
        Domain $domain,
        CategoryVisibilityRecalculationScheduler $categoryVisibilityRecalculationScheduler,
        FriendlyUrlFacade $friendlyUrlFacade,
        ImageFacade $imageFacade,
        PluginCrudExtensionFacade $pluginCrudExtensionFacade,
        CategoryWithPreloadedChildrenFactory $categoryWithPreloadedChildrenFactory,
        CategoryWithLazyLoadedVisibleChildrenFactory $categoryWithLazyLoadedVisibleChildrenFactory,
        CategoryFactoryInterface $categoryFactory,
        private CategoryParameterFacade $categoryParameterFacade,
        private LinkedCategoryFacade $linkedCategoryFacade,
        private ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade,
        private ProductFacade $productFacade,
    ) {
        parent::__construct(
            $em,
            $categoryRepository,
            $domain,
            $categoryVisibilityRecalculationScheduler,
            $friendlyUrlFacade,
            $imageFacade,
            $pluginCrudExtensionFacade,
            $categoryWithPreloadedChildrenFactory,
            $categoryWithLazyLoadedVisibleChildrenFactory,
            $categoryFactory,
        );
    }

    /**
     * @param \App\Model\Category\CategoryData $categoryData
     * @return \App\Model\Category\Category
     */
    public function create(CategoryData $categoryData): BaseCategory
    {
        /** @var \App\Model\Category\Category $category */
        $category = parent::create($categoryData);
        $this->categoryParameterFacade->saveRelation($category, $categoryData->parametersPosition, $categoryData->parametersCollapsed);
        $this->linkedCategoryFacade->updateLinkedCategories($category, $categoryData->linkedCategories);

        return $category;
    }

    /**
     * @param int $categoryId
     * @param \App\Model\Category\CategoryData $categoryData
     * @return \App\Model\Category\Category
     */
    public function edit($categoryId, CategoryData $categoryData): BaseCategory
    {
        /** @var \App\Model\Category\Category $category */
        $category = parent::edit($categoryId, $categoryData);
        $this->categoryParameterFacade->saveRelation($category, $categoryData->parametersPosition, $categoryData->parametersCollapsed);
        $this->linkedCategoryFacade->updateLinkedCategories($category, $categoryData->linkedCategories);

        $this->scheduleProductsToExportByCategory($category);

        return $category;
    }

    /**
     * @param array<int, array{id: string|int, parent_id: string|int|null, depth: int, left: int, right: int}> $categoriesOrderingData
     */
    public function reorderByNestedSetValues(array $categoriesOrderingData): void
    {
        parent::reorderByNestedSetValues($categoriesOrderingData);

        $this->productFacade->markAllProductsForExport();
    }

    /**
     * @param \App\Model\Category\Category $category
     */
    private function scheduleProductsToExportByCategory(Category $category): void
    {
        $products = $this->productFacade->getProductsByCategory($category);

        foreach ($products as $product) {
            $product->markForExport();
        }

        $this->em->flush();
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Category\Category|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?Category
    {
        return $this->categoryRepository->findByAkeneoCode($akeneoCode);
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Category\Category
     */
    public function getByAkeneoCode(string $akeneoCode): Category
    {
        return $this->categoryRepository->getByAkeneoCode($akeneoCode);
    }

    /**
     * @return int[]
     */
    public function getAllAkeneoCategoryIds(): array
    {
        return $this->categoryRepository->getAllAkeneoCategoryIds();
    }

    /**
     * @param \App\Model\Category\Category $destinationCategory
     * @return array
     */
    public function getCategoriesInPath(Category $destinationCategory): array
    {
        return array_slice($this->categoryRepository->getPath($destinationCategory), 1);
    }

    /**
     * @param \App\Model\Category\Category $destinationCategory
     * @param string $locale
     * @param string $delimiter
     * @return string
     */
    public function getCategoriesNamesInPathAsString(
        Category $destinationCategory,
        string $locale,
        string $delimiter = '/',
    ): string {
        $categoriesInPath = $this->getCategoriesInPath($destinationCategory);

        $categoriesNamesInPath = [];

        foreach ($categoriesInPath as $category) {
            $categoriesNamesInPath[] = $category->getName($locale);
        }

        return implode($delimiter, $categoriesNamesInPath);
    }

    /**
     * @param int $categoryId
     */
    public function deleteById($categoryId)
    {
        $category = $this->categoryRepository->getById($categoryId);
        $this->scheduleProductsToExportByCategory($category);

        parent::deleteById($categoryId);
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function getFullPathsIndexedByIds(string $locale): array
    {
        return $this->categoryRepository->getFullPathsIndexedByIds($locale);
    }

    /**
     * @param \App\Model\Category\Category $parentCategory
     * @param int $domainId
     * @return \App\Model\Category\Category[]
     */
    public function getVisibleCategoriesLookingLikeChildren(Category $parentCategory, int $domainId): array
    {
        $children = $this->getAllVisibleChildrenByCategoryAndDomainId($parentCategory, $domainId);
        $categoriesFromLinkedCategories = $this->categoryRepository->getVisibleCategoriesByLinkedCategories($parentCategory, $domainId, $children);

        return array_merge($children, $categoriesFromLinkedCategories);
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return array
     */
    public function getCategoriesOfProductByFilterData(ProductFilterData $productFilterData): array
    {
        $categoryIds = $this->productOnCurrentDomainElasticFacade->getCategoryIdsForFilterData($productFilterData);
        $categories = $this->categoryRepository->getCategoriesByIds($categoryIds);

        $categoriesIndexedByIds = [];

        foreach ($categories as $category) {
            $categoriesIndexedByIds[$category->getId()] = $category;
        }

        $sortedCategories = [];

        foreach ($categoryIds as $categoryId) {
            if (!array_key_exists($categoryId, $categoriesIndexedByIds)) {
                continue;
            }
            $sortedCategories[] = $categoriesIndexedByIds[$categoryId];
        }

        return $sortedCategories;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Category\Category[]
     */
    public function getAllVisibleChildrenByCategoryAndDomainConfig(
        Category $category,
        DomainConfig $domainConfig,
    ): array {
        return $this->categoryRepository->getAllVisibleChildrenByCategoryAndDomainConfig($category, $domainConfig);
    }
}
