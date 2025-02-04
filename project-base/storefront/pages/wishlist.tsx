import { BreadcrumbFragmentApi } from 'graphql/generated';
import useTranslation from 'next-translate/useTranslation';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { GtmPageType } from 'gtm/types/enums';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useRouter } from 'next/router';
import SharedWishlist from 'components/Pages/Wishlist/SharedWishlist';
import { Wishlist } from 'components/Pages/Wishlist/Wishlist';
import { Webline } from 'components/Layout/Webline/Webline';

const WishlistPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    useGtmPageViewEvent(useGtmStaticPageViewEvent(GtmPageType.other));
    const currentDomainConfig = useDomainConfig();

    const [wishlistUrl] = getInternationalizedStaticUrls(['/wishlist'], currentDomainConfig.url);
    const breadcrumbs: BreadcrumbFragmentApi[] = [{ __typename: 'Link', name: t('Wishlist'), slug: wishlistUrl }];
    const router = useRouter();
    const urlQueryParamId = router.query.id as string | undefined;

    return (
        <CommonLayout title={t('Wishlist')} breadcrumbs={breadcrumbs}>
            <Webline>{urlQueryParamId ? <SharedWishlist urlQueryParamId={urlQueryParamId} /> : <Wishlist />}</Webline>
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default WishlistPage;
