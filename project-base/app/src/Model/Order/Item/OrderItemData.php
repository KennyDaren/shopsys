<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;

/**
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 */
class OrderItemData extends BaseOrderItemData
{
    /**
     * @var \App\Model\Store\Store|null
     */
    public $personalPickupStore;

    /**
     * @var string|null
     */
    public $promoCodeIdentifier;

    /**
     * @var \App\Model\Order\Item\OrderItem|null
     */
    public $relatedOrderItem;
}
