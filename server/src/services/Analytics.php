<?php

namespace App\Services;

use App\Models\PageView;
use App\Repositories\PageViewRepository;
use App\Exceptions\AnalyticsException;

class Analytics
{
    public function __construct(
        private PageViewRepository $repository
    ) {}
    
    public function logPageView(int $businessId, string $ip, string $userAgent, ?string $referrer): void
    {
        try {
            $view = new PageView();
            $view->business_id = $businessId;
            $view->ip_address = $ip;
            $view->user_agent = $userAgent;
            $view->referrer = $referrer;
            
            $this->repository->create($view);
        } catch (\Exception $e) {
            throw new AnalyticsException('Failed to log page view', $e);
        }
    }
    
    public function getStats(int $businessId): array
    {
        return $this->repository->getStats($businessId);
    }
}
