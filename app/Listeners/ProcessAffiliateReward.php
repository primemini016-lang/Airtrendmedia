<?php

namespace App\Listeners;

use App\Events\UserActivationPaid;
use App\Services\AffiliateService;

class ProcessAffiliateReward
{
    public function __construct(private AffiliateService $affiliate) {}

    public function handle(UserActivationPaid $event): void
    {
        $this->affiliate->processActivationReward($event->deposit);
    }
}
