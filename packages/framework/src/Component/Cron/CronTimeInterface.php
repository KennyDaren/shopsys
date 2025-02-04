<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

interface CronTimeInterface
{
    /**
     * @return string
     */
    public function getTimeMinutes();

    /**
     * @return string
     */
    public function getTimeHours();
}
