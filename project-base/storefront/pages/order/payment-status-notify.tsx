import { useCheckPaymentStatusMutationApi } from 'graphql/generated';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps, ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';
import { useRouter } from 'next/router';
import { useEffect } from 'react';

const PaymentStatusNotifyPage: FC<ServerSidePropsType> = () => {
    const [, checkPaymentStatus] = useCheckPaymentStatusMutationApi();
    const router = useRouter();
    const { orderIdentifier } = router.query;

    let orderUuidParam = '';
    if (orderIdentifier !== undefined) {
        if (Array.isArray(orderIdentifier)) {
            orderUuidParam = orderIdentifier[0];
        } else if (orderIdentifier.trim() !== '') {
            orderUuidParam = orderIdentifier.trim();
        }
    }

    const checkPaymentOnApi = async (orderUuid: string) => {
        await checkPaymentStatus({ orderUuid });
    };

    useEffect(() => {
        checkPaymentOnApi(orderUuidParam);
    }, []);

    return <></>;
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, t }) =>
        async (context) =>
            initServerSideProps({ context, redisClient, domainConfig, t }),
);

export default PaymentStatusNotifyPage;
