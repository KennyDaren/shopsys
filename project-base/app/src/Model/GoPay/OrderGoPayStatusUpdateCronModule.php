<?php

declare(strict_types=1);

namespace App\Model\GoPay;

use App\Model\GoPay\Exception\GoPayPaymentDownloadException;
use App\Model\Order\Mail\OrderMailFacade;
use App\Model\Order\OrderFacade;
use App\Model\Payment\Service\PaymentServiceFacade;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class OrderGoPayStatusUpdateCronModule implements SimpleCronModuleInterface
{
    private Logger $logger;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \App\Model\Payment\Service\PaymentServiceFacade $paymentServiceFacade
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly OrderFacade $orderFacade,
        private readonly OrderMailFacade $orderMailFacade,
        private readonly PaymentServiceFacade $paymentServiceFacade,
    ) {
    }

    public function run(): void
    {
        $now = new DateTime();
        $twentyOneDaysAgo = $now->sub(DateInterval::createFromDateString('21 days'));
        $orders = $this->orderFacade->getAllUnpaidGoPayOrders($twentyOneDaysAgo);

        $this->logger->info('Downloading status updates for `' . count($orders) . '` orders.');

        foreach ($orders as $order) {
            $this->logger->info('Downloading GoPay status for order with ID `' . $order->getId() . '`.');

            if ($order->isDeleted()) {
                $this->logger->info(sprintf(
                    'Order status of order with ID `%s` has not been changed because is deleted',
                    $order->getId(),
                ));

                continue;
            }

            $oldOrderGoPayStatusesIndexedByGoPaiId = $order->getGoPayTransactionStatusesIndexedByGoPayId();
            $oldIsOrderPaid = $order->isPaid();

            try {
                $this->paymentServiceFacade->updatePaymentTransactionsByOrder($order);
            } catch (GoPayPaymentDownloadException $e) {
                $this->logger->error($e->getMessage());

                continue;
            }

            foreach ($order->getGoPayTransactions() as $goPayTransaction) {
                $oldStatus = $oldOrderGoPayStatusesIndexedByGoPaiId[$goPayTransaction->getExternalPaymentIdentifier()];
                $newStatus = $goPayTransaction->getExternalPaymentStatus();

                if ($oldStatus !== $newStatus) {
                    $this->logger->info(
                        sprintf(
                            'Order with id `%d` changed GoPay status from `%s` to `%s`.',
                            $order->getId(),
                            $oldStatus,
                            $newStatus,
                        ),
                    );
                }
            }

            if ($oldIsOrderPaid === $order->isPaid()) {
                continue;
            }

            $this->logger->info('Sending order e-mail.');
            $this->orderMailFacade->sendEmail($order);
        }

        $this->em->flush();
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }
}
