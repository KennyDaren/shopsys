import { Content } from './Content';
import { Heading } from 'components/Basic/Heading/Heading';
import { Icon } from 'components/Basic/Icon/Icon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { BreadcrumbFragmentApi } from 'graphql/generated';
import { useGtmSliderProductListViewEvent } from 'hooks/gtm/productList/useGtmSliderProductListViewEvent';
import { useComparison } from 'hooks/comparison/useComparison';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { GtmProductListNameType } from 'types/gtm/enums';

type ProductsComparisonProps = {
    breadcrumb: BreadcrumbFragmentApi[];
};

export const ProductsComparison: FC<ProductsComparisonProps> = ({ breadcrumb }) => {
    const t = useTypedTranslationFunction();

    const { comparison, fetching } = useComparison();
    const comparedProducts = comparison?.products ?? [];

    const content =
        comparedProducts.length > 0 ? (
            <Content productsCompare={comparedProducts} />
        ) : (
            <div className="my-[75px] flex items-center">
                <Icon iconType="icon" icon="Info" className="mr-4 w-8" />

                <Heading type="h3" className="!mb-0">
                    {t('Comparison does not contain any products yet.')}
                </Heading>
            </div>
        );

    useGtmSliderProductListViewEvent(comparison?.products, GtmProductListNameType.product_comparison_page);

    return (
        <Webline>
            <Breadcrumbs breadcrumb={breadcrumb} />
            {fetching ? (
                <div className="flex w-full justify-center py-10">
                    <Loader className="w-10" />
                </div>
            ) : (
                content
            )}
        </Webline>
    );
};
