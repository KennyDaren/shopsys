import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { Heading } from 'components/Basic/Heading/Heading';
import { CheckmarkIcon } from 'components/Basic/Icon/IconsSvg';
import { Image } from 'components/Basic/Image/Image';
import { Link } from 'components/Basic/Link/Link';
import { Button } from 'components/Forms/Button/Button';
import { CartItemFragmentApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { mapPriceForCalculations } from 'helpers/mappers/price';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import dynamic from 'next/dynamic';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

type AddToCartPopupProps = {
    onCloseCallback: () => void;
    addedCartItem: CartItemFragmentApi;
};

const TEST_IDENTIFIER = 'blocks-product-addtocartpopup-product';

export const AddToCartPopup: FC<AddToCartPopupProps> = ({ onCloseCallback, addedCartItem: { product, quantity } }) => {
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);

    return (
        <Popup onCloseCallback={onCloseCallback} className="w-11/12 max-w-2xl" hideCloseButton>
            <div className="mb-4 flex w-full items-center md:mb-6">
                <CheckmarkIcon className="mr-4 w-7 text-greenDark" />
                <Heading type="h2" className="mb-0 text-xl normal-case text-primary">
                    {t('Great choice! We have added your item to the cart')}
                </Heading>
            </div>

            <div
                className="mb-4 flex flex-col items-center rounded border border-greyLighter p-3 md:flex-row md:p-4"
                data-testid={TEST_IDENTIFIER}
            >
                {!!product.mainImage && (
                    <div className="mb-4 flex w-24 items-center justify-center md:mb-0">
                        <Image
                            image={product.mainImage}
                            type="thumbnailMedium"
                            alt={product.mainImage.name || product.fullName}
                        />
                    </div>
                )}
                <div className="w-full md:pl-4 lg:flex lg:items-center lg:justify-between">
                    <div className="block break-words text-primary" data-testid={TEST_IDENTIFIER + '-name'}>
                        <ExtendedNextLink href={product.slug} type="product">
                            {product.fullName}
                        </ExtendedNextLink>
                    </div>

                    <div className="mt-2 lg:mt-0 lg:w-5/12 lg:pl-4 lg:text-right">
                        <div className="block text-primary" data-testid={TEST_IDENTIFIER + '-price'}>
                            {`${quantity} ${product.unit.name}, ${formatPrice(
                                quantity * mapPriceForCalculations(product.price.priceWithVat),
                            )}`}
                        </div>
                    </div>
                </div>
            </div>

            <div className="flex flex-col text-center md:flex-row md:items-center md:justify-between md:p-0">
                <Button
                    onClick={onCloseCallback}
                    dataTestId={TEST_IDENTIFIER + '-button-back'}
                    className="mt-2 lg:w-auto lg:justify-start"
                >
                    {t('Back to shop')}
                </Button>

                <Link className="mt-2 w-full lg:w-auto lg:justify-start" href={cartUrl} isButton>
                    {t('To cart')}
                </Link>
            </div>
        </Popup>
    );
};
