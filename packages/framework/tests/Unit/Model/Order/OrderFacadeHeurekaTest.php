<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\FrontOrderDataMapper;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

class OrderFacadeHeurekaTest extends TestCase
{
    public function testNotSendHeurekaOrderInfoWhenShopCertificationIsNotActivated()
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->method('isHeurekaShopCertificationActivated')->willReturn(false);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade, false);
    }

    public function testNotSendHeurekaOrderInfoWhenDomainLocaleNotSupported()
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->method('isDomainLocaleSupported')->willReturn(false);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade, false);
    }

    public function testNotSendHeurekaOrderInfoWhenSendingIsDisallowed()
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->method('isDomainLocaleSupported')->willReturn(true);

        $heurekaFacade->expects($this->never())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade, true);
    }

    public function testSendHeurekaOrderInfo()
    {
        $heurekaFacade = $this->createMock(HeurekaFacade::class);
        $heurekaFacade->method('isHeurekaShopCertificationActivated')->willReturn(true);
        $heurekaFacade->method('isDomainLocaleSupported')->willReturn(true);

        $heurekaFacade->expects($this->once())->method('sendOrderInfo');

        $this->runHeurekaTest($heurekaFacade, false);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderFacade
     */
    private function createOrderFacade(HeurekaFacade $heurekaFacade): OrderFacade
    {
        return new OrderFacade(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(OrderNumberSequenceRepository::class),
            $this->createMock(OrderRepository::class),
            $this->createMock(OrderUrlGenerator::class),
            $this->createMock(OrderStatusRepository::class),
            $this->createMock(OrderMailFacade::class),
            $this->createMock(OrderHashGeneratorRepository::class),
            $this->createMock(Setting::class),
            $this->createMock(Localization::class),
            $this->createMock(AdministratorFrontSecurityFacade::class),
            $this->createMock(CurrentPromoCodeFacade::class),
            $this->createMock(CartFacade::class),
            $this->createMock(CustomerUserFacade::class),
            $this->createMock(CurrentCustomerUser::class),
            $this->createMock(OrderPreviewFactory::class),
            $this->createMock(OrderProductFacade::class),
            $heurekaFacade,
            $this->createDomain(),
            $this->createMock(OrderFactoryInterface::class),
            $this->createMock(OrderPriceCalculation::class),
            $this->createMock(OrderItemPriceCalculation::class),
            $this->createMock(FrontOrderDataMapper::class),
            $this->createMock(NumberFormatterExtension::class),
            $this->createMock(PaymentPriceCalculation::class),
            $this->createMock(TransportPriceCalculation::class),
            $this->createMock(OrderItemFactoryInterface::class),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param bool $disallowHeurekaVerifiedByCustomers
     */
    private function runHeurekaTest(HeurekaFacade $heurekaFacade, $disallowHeurekaVerifiedByCustomers): void
    {
        $orderFacade = $this->createOrderFacade($heurekaFacade);
        $order = $this->createOrderMock();
        $orderFacade->sendHeurekaOrderInfo($order, $disallowHeurekaVerifiedByCustomers);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private function createDomain(): Domain
    {
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfig = new DomainConfig(Domain::FIRST_DOMAIN_ID, '', '', 'cs', $defaultTimeZone);

        return new Domain([$domainConfig], $this->createMock(Setting::class));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Shopsys\FrameworkBundle\Model\Order\Order
     */
    private function createOrderMock(): MockObject
    {
        $order = $this->createMock(Order::class);
        $order->method('getDomainId')->willReturn(Domain::FIRST_DOMAIN_ID);

        return $order;
    }
}
