import '../styles/user-text.css';
import 'react-loading-skeleton/dist/skeleton.css';
import 'react-toastify/dist/ReactToastify.css';
import 'nprogress/nprogress.css';
import '../styles/globals.css';
import appWithI18n from 'next-translate/appWithI18n';
import i18nConfig from 'i18n';
import { AppProps as NextAppProps } from 'next/app';
import { ReactElement, useMemo } from 'react';
import { AppPageContent } from 'components/Pages/App/AppPageContent';
import useTranslation from 'next-translate/useTranslation';
import { createClient } from 'urql/createClient';
import { Provider, ssrExchange } from 'urql';
import { initDayjsLocale } from 'helpers/formaters/formatDate';
import { logException } from 'helpers/errors/logException';
import { ServerSidePropsType } from 'helpers/serverSide/initServerSideProps';

type ErrorProps = {
    err?: any;
};

type AppProps = {
    pageProps: ServerSidePropsType;
} & Omit<NextAppProps<ErrorProps>, 'pageProps'> &
    ErrorProps;

process.on('unhandledRejection', logException);
process.on('uncaughtException', logException);

function MyApp({ Component, pageProps, err }: AppProps): ReactElement | null {
    const { defaultLocale, publicGraphqlEndpoint } = pageProps.domainConfig;
    initDayjsLocale(defaultLocale);
    const { t } = useTranslation();

    const urqlClient = useMemo(
        () =>
            createClient({ t, ssrExchange: ssrExchange({ initialState: pageProps.urqlState }), publicGraphqlEndpoint }),
        [publicGraphqlEndpoint, pageProps.urqlState, t],
    );

    return (
        <Provider value={urqlClient}>
            <AppPageContent Component={Component} pageProps={pageProps} err={err} />
        </Provider>
    );
}

// eslint-disable-next-line
// @ts-ignore
export default appWithI18n(MyApp, { ...i18nConfig });
