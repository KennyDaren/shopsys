import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { FooterBoxInfo } from './FooterBoxInfo';
import { FooterCopyright } from './FooterCopyright';
import { FooterMenu } from './FooterMenu';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';

type FooterProps = {
    simpleFooter?: boolean;
};

const FOOTER_TEST_IDENTIFIER = 'layout-footer';

export const Footer: FC<FooterProps> = ({ simpleFooter }) => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const [cookieConsentUrl] = getInternationalizedStaticUrls(['/cookie-consent'], url);

    return (
        <div className="relative mt-auto" data-testid={FOOTER_TEST_IDENTIFIER}>
            <div className="flex flex-col pt-5 pb-11 lg:py-11">
                {!simpleFooter && (
                    <>
                        <FooterBoxInfo />
                        <div className="mb-12 vl:mb-24 vl:flex">
                            <FooterMenu />
                        </div>
                    </>
                )}
                <FooterCopyright />
                <ExtendedNextLink
                    href={cookieConsentUrl}
                    type="static"
                    className="self-center text-greyLight no-underline transition hover:text-whitesmoke hover:no-underline"
                >
                    {t('Cookie consent update')}
                </ExtendedNextLink>
            </div>
        </div>
    );
};
