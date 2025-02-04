import { ProductVariantsTableRow } from './ProductVariantsTableRow';
import { Image } from 'components/Basic/Image/Image';
import { AddToCart } from 'components/Blocks/Product/AddToCart';
import { ProductAvailableStoresCount } from 'components/Blocks/Product/ProductAvailableStoresCount';
import { ProductDetailAvailabilityList } from 'components/Pages/ProductDetail/ProductDetailAvailabilityList';
import { MainVariantDetailFragmentApi } from 'graphql/generated';
import { useFormatPrice } from 'hooks/formatting/useFormatPrice';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useState } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { twMergeCustom } from 'helpers/twMerge';

const Popup = dynamic(() => import('components/Layout/Popup/Popup').then((component) => component.Popup));

type VariantProps = {
    variant: MainVariantDetailFragmentApi['variants'][number];
    isSellingDenied: boolean;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    listIndex: number;
};

const TEST_IDENTIFIER = 'pages-productdetail-variant-';

export const Variant: FC<VariantProps> = ({
    gtmProductListName,
    gtmMessageOrigin,
    isSellingDenied,
    listIndex,
    variant,
}) => {
    const formatPrice = useFormatPrice();
    const [isAvailabilityPopupVisible, setAvailabilityPopupVisibility] = useState(false);
    const { t } = useTranslation();

    return (
        <>
            <ProductVariantsTableRow key={variant.uuid} dataTestId={TEST_IDENTIFIER + variant.catalogNumber}>
                <Cell className="float-left w-10 pl-0 lg:float-none">
                    <div className="w-20 pr-2">
                        <Image
                            image={variant.mainImage}
                            alt={variant.mainImage?.name || variant.fullName}
                            type="default"
                        />
                    </div>
                </Cell>
                <Cell dataTestId={TEST_IDENTIFIER + 'name'}>{variant.fullName}</Cell>
                <Cell
                    className="cursor-pointer"
                    onClick={() => setAvailabilityPopupVisibility(true)}
                    dataTestId={TEST_IDENTIFIER + 'availability'}
                >
                    {variant.availability.name}
                    <ProductAvailableStoresCount
                        isMainVariant={false}
                        availableStoresCount={variant.availableStoresCount}
                    />
                </Cell>
                <Cell className="lg:text-right" dataTestId={TEST_IDENTIFIER + 'price'}>
                    {formatPrice(variant.price.priceWithVat)}
                </Cell>
                <Cell className="text-right max-lg:clear-both max-lg:pl-0 lg:w-60">
                    {isSellingDenied ? (
                        <>{t('This item can no longer be purchased')}</>
                    ) : (
                        <AddToCart
                            productUuid={variant.uuid}
                            minQuantity={1}
                            maxQuantity={variant.stockQuantity}
                            gtmMessageOrigin={gtmMessageOrigin}
                            gtmProductListName={gtmProductListName}
                            listIndex={listIndex}
                        />
                    )}
                </Cell>
            </ProductVariantsTableRow>
            {isAvailabilityPopupVisible && (
                <Popup onCloseCallback={() => setAvailabilityPopupVisibility(false)} className="w-11/12 max-w-2xl">
                    <ProductDetailAvailabilityList storeAvailabilities={variant.storeAvailabilities} />
                </Popup>
            )}
        </>
    );
};

type CellProps = { onClick?: () => void };

const Cell: FC<CellProps> = ({ className, children, dataTestId, onClick }) => (
    <td
        className={twMergeCustom(
            'block pl-20 text-left align-middle text-xs lg:table-cell lg:border-b lg:border-greyLighter lg:px-1 lg:py-2',
            className,
        )}
        data-testid={dataTestId}
        onClick={onClick}
    >
        {children}
    </td>
);
