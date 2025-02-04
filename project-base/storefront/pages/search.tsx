import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { SearchContent } from 'components/Pages/Search/SearchContent';
import {
    BreadcrumbFragmentApi,
    SearchProductsQueryDocumentApi,
    SearchQueryDocumentApi,
    useSearchQueryApi,
} from 'graphql/generated';
import { getMappedProductFilter } from 'helpers/filterOptions/getMappedProductFilter';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useQueryParams } from 'hooks/useQueryParams';
import { GtmPageType } from 'gtm/types/enums';
import {
    getNumberFromUrlQuery,
    getProductListSortFromUrlQuery,
    getSlugFromServerSideUrl,
    getStringFromUrlQuery,
} from 'helpers/parsing/urlParsing';
import { getRedirectWithOffsetPage } from 'helpers/loadMore';

const SearchPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { sort, filter, searchString, currentLoadMore } = useQueryParams();

    const [{ data: searchData, fetching }] = useSearchQueryApi({
        variables: {
            search: searchString ?? '',
            orderingMode: sort,
            filter: mapParametersFilter(filter),
            pageSize: DEFAULT_PAGE_SIZE * (currentLoadMore + 1),
        },
    });

    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Search'), slug: searchUrl }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.search_results, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const title = useSeoTitleWithPagination(searchData?.productsSearch.totalCount, t('Search'));

    return (
        <>
            <MetaRobots content="noindex, nofollow" />
            <CommonLayout title={title} breadcrumbs={breadcrumbs}>
                <SearchContent searchResults={searchData} fetching={fetching} />
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);
    const loadMore = getNumberFromUrlQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME], 0);
    const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '');
    const redirect = getRedirectWithOffsetPage(page, loadMore, urlSlug, context.query);

    if (redirect) {
        return redirect;
    }

    const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
    const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);
    const search = getStringFromUrlQuery(context.query[SEARCH_QUERY_PARAMETER_NAME]);

    return initServerSideProps({
        context,
        prefetchedQueries: [
            {
                query: SearchQueryDocumentApi,
                variables: {
                    search,
                    orderingMode,
                    filter,
                    pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                },
            },
            {
                query: SearchProductsQueryDocumentApi,
                variables: {
                    search,
                    orderingMode,
                    filter,
                    endCursor: getEndCursor(page),
                    pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default SearchPage;
