import { SearchProductsWrapper } from './SearchProductsWrapper';
import { FilterIcon } from 'components/Basic/Icon/IconsSvg';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { ListedProductConnectionPreviewFragmentApi, ProductOrderingModeEnumApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import dynamic from 'next/dynamic';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

type ProductsSearchProps = {
    productsSearch: ListedProductConnectionPreviewFragmentApi;
};

export const ProductsSearch: FC<ProductsSearchProps> = ({ productsSearch }) => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const [isPanelOpen, setIsPanelOpen] = useState(false);

    const handlePanelOpenerClick = () => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            const newValue = !prev;
            body.style.overflow = newValue ? 'hidden' : 'visible';

            return newValue;
        });
    };

    return (
        <>
            <div className="relative mb-8 flex flex-col vl:mb-10 vl:flex-row vl:flex-wrap vl:gap-12">
                <div
                    className={twJoin(
                        'fixed top-0 left-0 bottom-0 right-10 max-w-md -translate-x-full vl:static vl:w-80 vl:translate-x-0 vl:transition-none',
                        isPanelOpen && 'z-aboveOverlay translate-x-0 transition',
                    )}
                >
                    <FilterPanel
                        productFilterOptions={productsSearch.productFilterOptions}
                        defaultOrderingMode={productsSearch.defaultOrderingMode}
                        orderingMode={productsSearch.orderingMode}
                        originalSlug={null}
                        panelCloseHandler={handlePanelOpenerClick}
                        slug={searchUrl}
                        totalCount={productsSearch.totalCount}
                    />
                </div>

                <Overlay isActive={isPanelOpen} onClick={handlePanelOpenerClick} />

                <div className="flex flex-1 flex-col" ref={paginationScrollTargetRef}>
                    <div
                        className="relative mb-3 flex h-12 w-full cursor-pointer flex-row justify-center rounded bg-primary py-3 px-8 font-bold uppercase leading-7 text-white vl:hidden"
                        onClick={handlePanelOpenerClick}
                    >
                        <FilterIcon className="mr-3 w-6 font-bold text-white" />
                        {t('Filter')}
                    </div>

                    <SortingBar
                        sorting={productsSearch.orderingMode}
                        totalCount={productsSearch.totalCount}
                        customSortOptions={[
                            ProductOrderingModeEnumApi.RelevanceApi,
                            ProductOrderingModeEnumApi.PriceAscApi,
                            ProductOrderingModeEnumApi.PriceDescApi,
                        ]}
                    />

                    <SearchProductsWrapper
                        paginationScrollTargetRef={paginationScrollTargetRef}
                        productsSearch={productsSearch}
                    />
                </div>
            </div>
        </>
    );
};
