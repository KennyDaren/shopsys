import { CommonLayout } from 'components/Layout/CommonLayout';
import { StoresContent } from 'components/Pages/Stores/StoresContent';
import { BreadcrumbFragmentApi, StoresQueryDocumentApi, useStoresQueryApi } from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { GtmPageType } from 'gtm/types/enums';

const StoresPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const [{ data: storesData }] = useStoresQueryApi();
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Department stores'), slug: '' }];

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.stores, breadcrumbs);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    return (
        <>
            <CommonLayout title={t('Stores')} breadcrumbs={breadcrumbs}>
                {storesData?.stores && <StoresContent stores={storesData.stores} />}
            </CommonLayout>
        </>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({
                context,
                prefetchedQueries: [{ query: StoresQueryDocumentApi }],
                redisClient,
                domainConfig,
                t,
            }),
);

export default StoresPage;
