<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ProductAvailabilityCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculator $productAvailabilityRecalculator
     */
    public function __construct(protected readonly ProductAvailabilityRecalculator $productAvailabilityRecalculator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function sleep()
    {
    }

    public function wakeUp()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterate()
    {
        if ($this->productAvailabilityRecalculator->runBatchOfScheduledDelayedRecalculations()) {
            $this->logger->debug('Batch is recalculated.');

            return true;
        }
        $this->logger->debug('All availabilities are recalculated.');

        return false;
    }
}
