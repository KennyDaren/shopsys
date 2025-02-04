import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useAuth } from 'hooks/auth/useAuth';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';

export const CustomerContent: FC = () => {
    const { t } = useTranslation();
    const { logout } = useAuth();
    const { url } = useDomainConfig();
    const [customerOrdersUrl, customerEditProfileUrl] = getInternationalizedStaticUrls(
        ['/customer/orders', '/customer/edit-profile'],
        url,
    );

    return (
        <>
            <Webline>
                <div className="text-center">
                    <Heading type="h1">{t('Customer')}</Heading>
                </div>
            </Webline>

            <Webline>
                <ul className="mb-8 flex flex-col flex-wrap gap-4 md:flex-row">
                    <CustomerListItem>
                        <ExtendedNextLink href={customerOrdersUrl} type="static">
                            {t('My orders')}
                        </ExtendedNextLink>
                    </CustomerListItem>

                    <CustomerListItem>
                        <ExtendedNextLink href={customerEditProfileUrl} type="static">
                            {t('Edit profile')}
                        </ExtendedNextLink>
                    </CustomerListItem>

                    <CustomerListItem>
                        <a onClick={logout}>{t('Logout')}</a>
                    </CustomerListItem>
                </ul>
            </Webline>
        </>
    );
};

const CustomerListItem: FC = ({ children }) => (
    <li className="block flex-1 cursor-pointer rounded bg-greyVeryLight text-lg text-dark transition hover:bg-greyLighter [&_a]:block [&_a]:h-full [&_a]:w-full [&_a]:p-5 [&_a]:no-underline hover:[&_a]:text-dark hover:[&_a]:no-underline">
        {children}
    </li>
);
