<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Exception;
use Heureka\ShopCertification\Exception as HeurekaShopCertificationException;
use Shopsys\FrameworkBundle\Model\Heureka\Exception\LocaleNotSupportedException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Bridge\Monolog\Logger;

class HeurekaFacade
{
    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationFactory $heurekaShopCertificationFactory
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting $heurekaSetting
     */
    public function __construct(
        protected readonly Logger $logger,
        protected readonly HeurekaShopCertificationFactory $heurekaShopCertificationFactory,
        protected readonly HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper,
        protected readonly HeurekaSetting $heurekaSetting,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function sendOrderInfo(Order $order)
    {
        try {
            $heurekaShopCertification = $this->heurekaShopCertificationFactory->create($order);
            $heurekaShopCertification->logOrder();
        } catch (LocaleNotSupportedException $ex) {
            $this->logError($ex, $order);
        } catch (HeurekaShopCertificationException $ex) {
            $this->logError($ex, $order);
        }
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isHeurekaShopCertificationActivated($domainId)
    {
        return $this->heurekaSetting->isHeurekaShopCertificationActivated($domainId);
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isHeurekaWidgetActivated($domainId)
    {
        return $this->heurekaSetting->isHeurekaWidgetActivated($domainId);
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function isDomainLocaleSupported($locale)
    {
        return $this->heurekaShopCertificationLocaleHelper->isDomainLocaleSupported($locale);
    }

    /**
     * @param string $locale
     * @return string|null
     */
    public function getServerNameByLocale($locale)
    {
        return $this->heurekaShopCertificationLocaleHelper->getServerNameByLocale($locale);
    }

    /**
     * @param \Exception $ex
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function logError(Exception $ex, Order $order)
    {
        $message = 'Sending order (ID = "' . $order->getId() . '") to Heureka failed - ' . get_class(
            $ex,
        ) . ': ' . $ex->getMessage();
        $this->logger->error($message, ['exceptionFullInfo' => (string)$ex]);
    }
}
