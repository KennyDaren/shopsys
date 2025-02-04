import { ExtendedNextLink } from 'components/Basic/ExtendedNextLink/ExtendedNextLink';
import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { Image } from 'components/Basic/Image/Image';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductFlags } from 'components/Blocks/Product/ProductFlags';
import { ProductPrice } from 'components/Blocks/Product/ProductPrice';
import { ListedProductFragmentApi } from 'graphql/generated';
import { onGtmProductClickEventHandler } from 'gtm/helpers/eventHandlers';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import useTranslation from 'next-translate/useTranslation';
import { twMergeCustom } from 'helpers/twMerge';
import { forwardRef } from 'react';
import { FunctionComponentProps } from 'types/globals';
import { RemoveBoldIcon } from 'components/Basic/Icon/IconsSvg';

type ProductItemProps = {
    product: ListedProductFragmentApi;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    isProductInComparison: boolean;
    toggleProductInComparison: () => void;
    isProductInWishlist: boolean;
    toggleProductInWishlist: () => void;
} & FunctionComponentProps;

const getDataTestId = (catalogNumber: string) => 'blocks-product-list-listeditem-' + catalogNumber;

export const ProductListItem = forwardRef<HTMLLIElement, ProductItemProps>(
    (
        {
            product,
            listIndex,
            gtmProductListName,
            gtmMessageOrigin,
            isProductInComparison,
            toggleProductInComparison,
            isProductInWishlist,
            toggleProductInWishlist,
            className,
        },
        ref,
    ) => {
        const { url } = useDomainConfig();
        const { t } = useTranslation();

        return (
            <li
                ref={ref}
                className={twMergeCustom(
                    'relative flex flex-col justify-between gap-3 border-b border-greyLighter p-3 text-left lg:hover:z-above lg:hover:bg-white lg:hover:shadow-xl',
                    className,
                )}
                data-testid={getDataTestId(product.catalogNumber)}
            >
                {gtmProductListName === GtmProductListNameType.wishlist && (
                    <button
                        className="absolute right-3 z-above flex h-5 w-5 cursor-pointer items-center justify-center rounded-full border-none bg-whitesmoke p-0 outline-none transition hover:bg-blueLight"
                        onClick={toggleProductInWishlist}
                        data-testid={getDataTestId(product.catalogNumber) + '-wishlist-remove'}
                        title={t('Remove from wishlist')}
                    >
                        <RemoveBoldIcon className="mx-auto w-2 basis-2" />
                    </button>
                )}

                <ExtendedNextLink
                    type="product"
                    href={product.slug}
                    className="flex h-full flex-col gap-3 no-underline hover:no-underline"
                    onClick={() => onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url)}
                >
                    <div className="relative">
                        <Image
                            image={product.mainImage}
                            type="list"
                            alt={product.mainImage?.name || product.fullName}
                            className="h-40 justify-center lg:hover:mix-blend-multiply"
                        />
                        {!!product.flags.length && (
                            <div className="absolute top-3 left-4 flex flex-col">
                                <ProductFlags flags={product.flags} />
                            </div>
                        )}
                    </div>

                    <div
                        className="h-10 overflow-hidden text-lg font-bold leading-5 text-dark"
                        data-testid={getDataTestId(product.catalogNumber) + '-name'}
                    >
                        {product.fullName}
                    </div>

                    <ProductPrice productPrice={product.price} />

                    <div className="flex flex-col gap-1 text-sm text-black">
                        <div>{product.availability.name}</div>
                        <ProductAvailableStoresCount
                            isMainVariant={product.isMainVariant}
                            availableStoresCount={product.availableStoresCount}
                        />
                    </div>
                </ExtendedNextLink>

                <div className="flex justify-end gap-2">
                    <ProductCompareButton
                        isProductInComparison={isProductInComparison}
                        toggleProductInComparison={toggleProductInComparison}
                    />
                    <ProductWishlistButton
                        toggleProductInWishlist={toggleProductInWishlist}
                        isProductInWishlist={isProductInWishlist}
                    />
                </div>

                <ProductAction
                    product={product}
                    gtmProductListName={gtmProductListName}
                    gtmMessageOrigin={gtmMessageOrigin}
                    listIndex={listIndex}
                />
            </li>
        );
    },
);

ProductListItem.displayName = 'ProductItem';
