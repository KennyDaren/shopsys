import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { PageGuard } from 'components/Basic/PageGuard/PageGuard';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { GoPayGateway } from 'components/Pages/Order/PaymentConfirmation/Gateways/GoPayGateway';
import { RegistrationAfterOrder } from 'components/Pages/OrderConfirmation/RegistrationAfterOrder';
import {
    OrderSentPageContentDocumentApi,
    useIsCustomerUserRegisteredQueryApi,
    useOrderSentPageContentApi,
} from 'graphql/generated';
import { useGtmStaticPageViewEvent } from 'gtm/helpers/eventFactories';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { GtmPageType } from 'gtm/types/enums';
import { PaymentTypeEnum } from 'types/payment';
import { useRef } from 'react';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { useIsUserLoggedIn } from 'hooks/auth/useIsUserLoggedIn';

export type OrderConfirmationQuery = {
    orderUuid: string | undefined;
    orderEmail: string | undefined;
    orderPaymentType: string | undefined;
    registrationData?: string;
};

const TEST_IDENTIFIER = 'pages-orderconfirmation';

const OrderConfirmationPage: FC<ServerSidePropsType> = () => {
    const { t } = useTranslation();
    const { query } = useRouter();
    const { orderUuid, orderEmail, orderPaymentType, registrationData } = query as OrderConfirmationQuery;
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const isUserLoggedIn = useIsUserLoggedIn();
    const parsedRegistrationData = useRef<ContactInformation | undefined>(
        registrationData ? (JSON.parse(registrationData) as ContactInformation) : undefined,
    );

    const gtmStaticPageViewEvent = useGtmStaticPageViewEvent(GtmPageType.order_confirmation);
    useGtmPageViewEvent(gtmStaticPageViewEvent);

    const [{ data: orderSentPageContentData }] = useOrderSentPageContentApi({ variables: { orderUuid: orderUuid! } });
    const [{ data: isCustomerUserRegisteredData, fetching: isInformationAboutUserRegistrationFetching }] =
        useIsCustomerUserRegisteredQueryApi({
            variables: {
                email: orderEmail!,
            },
            pause: orderEmail === undefined,
        });

    return (
        <PageGuard isWithAccess={!!orderUuid} errorRedirectUrl={cartUrl}>
            <MetaRobots content="noindex" />
            <CommonLayout title={t('Thank you for your order')}>
                <Webline>
                    <div
                        className="mt-16 mb-10 flex flex-col items-center justify-center lg:mb-20 lg:flex-row"
                        data-testid={TEST_IDENTIFIER}
                    >
                        <div className="w-40 lg:mr-32">
                            <img alt="Objednávka odeslána" src="/public/frontend/images/sent-cart.svg" />
                        </div>
                        <div className="text-center lg:text-left">
                            {!!orderSentPageContentData && (
                                <div
                                    className="mb-8"
                                    dangerouslySetInnerHTML={{ __html: orderSentPageContentData.orderSentPageContent }}
                                />
                            )}
                            {orderPaymentType === PaymentTypeEnum.GoPay && <GoPayGateway orderUuid={orderUuid!} />}
                        </div>
                    </div>
                </Webline>
                {!!parsedRegistrationData.current &&
                    !isUserLoggedIn &&
                    orderUuid &&
                    !isInformationAboutUserRegistrationFetching &&
                    isCustomerUserRegisteredData?.isCustomerUserRegistered === false && (
                        <RegistrationAfterOrder
                            lastOrderUuid={orderUuid}
                            registrationData={parsedRegistrationData.current}
                        />
                    )}
            </CommonLayout>
        </PageGuard>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(({ redisClient, domainConfig, t }) => async (context) => {
    const { orderUuid, orderEmail } = context.query as OrderConfirmationQuery;

    if (!orderUuid || !orderEmail) {
        return {
            redirect: {
                destination: getInternationalizedStaticUrls(['/cart'], domainConfig.url)[0] ?? '/',
                statusCode: 301,
            },
        };
    }

    return initServerSideProps({
        context,
        prefetchedQueries: [
            {
                query: OrderSentPageContentDocumentApi,
                variables: { orderUuid },
            },
        ],
        redisClient,
        domainConfig,
        t,
    });
});

export default OrderConfirmationPage;
