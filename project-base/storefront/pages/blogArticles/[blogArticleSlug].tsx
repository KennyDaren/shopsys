import { CommonLayout } from 'components/Layout/CommonLayout';
import { BlogArticleDetailContent } from 'components/Pages/BlogArticle/BlogArticleDetailContent';
import { BlogArticlePageSkeleton } from 'components/Pages/BlogArticle/BlogArticlePageSkeleton';
import {
    BlogArticleDetailQueryApi,
    BlogArticleDetailQueryDocumentApi,
    BlogArticleDetailQueryVariablesApi,
    ProductsByCatnumsDocumentApi,
    useBlogArticleDetailQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/isServer';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/urlParsing';
import { createClient } from 'urql/createClient';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { OperationResult } from 'urql';
import { parseCatnums } from 'helpers/parsing/grapesJsParser';

const BlogArticleDetailPage: NextPage = () => {
    const router = useRouter();
    const [{ data: blogArticleData, fetching }] = useBlogArticleDetailQueryApi({
        variables: { urlSlug: getSlugFromUrl(router.asPath) },
    });

    const pageViewEvent = useGtmFriendlyPageViewEvent(blogArticleData?.blogArticle);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout
            title={blogArticleData?.blogArticle?.seoTitle}
            description={blogArticleData?.blogArticle?.seoMetaDescription}
            breadcrumbs={blogArticleData?.blogArticle?.breadcrumb}
            breadcrumbsType="blogCategory"
            canonicalQueryParams={[]}
        >
            {!!blogArticleData?.blogArticle && !fetching ? (
                <BlogArticleDetailContent blogArticle={blogArticleData.blogArticle} />
            ) : (
                <BlogArticlePageSkeleton />
            )}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const client = createClient({
                t,
                ssrExchange,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            if (isRedirectedFromSsr(context.req.headers)) {
                const blogArticleResponse: OperationResult<
                    BlogArticleDetailQueryApi,
                    BlogArticleDetailQueryVariablesApi
                > = await client!
                    .query(BlogArticleDetailQueryDocumentApi, {
                        urlSlug: getSlugFromServerSideUrl(context.req.url ?? ''),
                    })
                    .toPromise();

                const parsedCatnums = parseCatnums(blogArticleResponse.data?.blogArticle?.text ?? '');

                await client!
                    .query(ProductsByCatnumsDocumentApi, {
                        catnums: parsedCatnums,
                    })
                    .toPromise();

                if (
                    (!blogArticleResponse.data || !blogArticleResponse.data.blogArticle) &&
                    !(context.res.statusCode === 503)
                ) {
                    return {
                        notFound: true,
                    };
                }
            }

            const initServerSideData = await initServerSideProps({
                context,
                client,
                domainConfig,
                ssrExchange,
            });

            return initServerSideData;
        },
);

export default BlogArticleDetailPage;
