import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ListItem } from './CartListItem';
import { Loader } from 'components/Basic/Loader/Loader';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Button } from 'components/Forms/Button/Button';
import { useCurrentCart } from 'connectors/cart/Cart';
import { getInternationalizedStaticUrls } from 'helpers/getInternationalizedStaticUrls';
import { useRemoveFromCart } from 'hooks/cart/useRemoveFromCart';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { twJoin } from 'tailwind-merge';
import { GtmProductListNameType } from 'gtm/types/enums';
import { twMergeCustom } from 'helpers/twMerge';
import { EmptyCartIcon, CartIcon as CartIcon } from 'components/Basic/Icon/IconsSvg';

const TEST_IDENTIFIER = 'layout-header-cart-';

export const Cart: FC = ({ className }) => {
    const router = useRouter();
    const { t } = useTranslation();
    const formatPrice = useFormatPrice();
    const { cart, isCartEmpty, isFetching } = useCurrentCart();
    const { url } = useDomainConfig();
    const [cartUrl] = getInternationalizedStaticUrls(['/cart'], url);
    const loginLoading = usePersistStore((store) => store.loginLoading);
    const [removeItemFromCart, isRemovingItem] = useRemoveFromCart(GtmProductListNameType.cart);
    const [isLoading, setIsLoading] = useState(false);

    /** We have to match server HTML and client HTML. Since we store cardUuid in browser,
     * server doesn't know that user do have cartUuid and it won't start fetching, but browser
     * does. For this we need both server and client start with the same values and then
     * set it on client. */
    useEffect(() => {
        setIsLoading(isFetching || !!loginLoading);
    }, [isFetching, loginLoading]);

    return (
        <div className={twMergeCustom('group relative lg:flex', className)}>
            {isLoading && (
                <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded bg-greyLighter py-2 opacity-50" />
            )}

            <ExtendedNextLink
                href={cartUrl}
                type="static"
                className={twJoin(
                    'hidden items-center gap-x-4 rounded bg-orangeLight py-4 pr-2 pl-4 text-black no-underline transition-all hover:text-black hover:no-underline group-hover:rounded-b-none group-hover:bg-white group-hover:shadow-lg lg:flex',
                )}
                data-testid={TEST_IDENTIFIER + 'block'}
            >
                <>
                    <span className="relative flex text-lg">
                        <CartIcon className="w-6 lg:w-5" />
                        <CartCount>{cart?.items.length ?? 0}</CartCount>
                    </span>
                    <span className="hidden text-sm font-bold lg:block" data-testid={TEST_IDENTIFIER + 'totalprice'}>
                        {formatPrice(cart?.totalItemsPrice.priceWithVat ?? 0, {
                            explicitZero: true,
                        })}
                    </span>
                </>
            </ExtendedNextLink>

            <div
                className={twJoin(
                    'pointer-events-none absolute top-full right-0 z-cart hidden origin-top-right scale-75 p-5 transition-all group-hover:pointer-events-auto group-hover:scale-100 group-hover:opacity-100 lg:block lg:rounded lg:rounded-tr-none lg:bg-white lg:opacity-0 lg:shadow-md',
                    isCartEmpty ? 'lg:flex lg:w-96 lg:flex-nowrap lg:items-center lg:justify-between' : 'lg:w-[510px]',
                )}
                data-testid={TEST_IDENTIFIER + 'detail'}
            >
                {!isCartEmpty ? (
                    <>
                        <ul className="relative m-0 flex max-h-96 w-full list-none flex-col overflow-y-auto p-0">
                            {isRemovingItem && <LoaderWithOverlay className="w-16" />}
                            {cart?.items.map((cartItem, listIndex) => (
                                <ListItem
                                    key={cartItem.uuid}
                                    cartItem={cartItem}
                                    onItemRemove={() => removeItemFromCart(cartItem, listIndex)}
                                />
                            ))}
                        </ul>
                        <div className="flex w-full justify-end pt-5">
                            <Button
                                size="small"
                                onClick={() => router.push(cartUrl)}
                                dataTestId={TEST_IDENTIFIER + 'button'}
                            >
                                {t('Go to cart')}
                            </Button>
                        </div>
                    </>
                ) : (
                    <>
                        <span className="text-dark">{t('Your cart is currently empty.')}</span>
                        <EmptyCartIcon className={twJoin('w-20 text-orange')} />
                    </>
                )}
            </div>

            <div className="flex cursor-pointer items-center justify-center text-lg outline-none lg:hidden">
                <ExtendedNextLink
                    href={cartUrl}
                    type="static"
                    className="relative flex h-full w-full items-center justify-center p-3 text-white no-underline transition-colors hover:text-white hover:no-underline"
                >
                    <>
                        <CartIcon className="w-6 text-white" />
                        <CartCount>{cart?.items.length ?? 0}</CartCount>
                    </>
                </ExtendedNextLink>
            </div>
        </div>
    );
};

const CartCount: FC = ({ children, dataTestId }) => (
    <span
        className="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-xs font-bold leading-normal text-white lg:-top-2 lg:-right-2"
        data-testid={dataTestId}
    >
        {children}
    </span>
);
