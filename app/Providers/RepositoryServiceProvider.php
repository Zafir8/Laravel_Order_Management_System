<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Services
use App\Services\OrderImportService;
use App\Services\OrderWorkflowService;
use App\Services\InventoryService;
use App\Services\PaymentService;
use App\Services\KpiService;
use App\Services\LeaderboardService;

// Service Implementations
use App\Services\Impl\OrderImportServiceImpl;
use App\Services\Impl\OrderWorkflowServiceImpl;
use App\Services\Impl\InventoryServiceImpl;
use App\Services\Impl\PaymentServiceImpl;
use App\Services\Impl\KpiServiceImpl;
use App\Services\Impl\LeaderboardServiceImpl;

// Repositories
use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\ProductRepository;
use App\Repositories\CustomerRepository;

// Repository Implementations
use App\Repositories\Impl\OrderRepositoryImpl;
use App\Repositories\Impl\OrderItemRepositoryImpl;
use App\Repositories\Impl\ProductRepositoryImpl;
use App\Repositories\Impl\CustomerRepositoryImpl;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services
        $this->app->bind(OrderImportService::class, OrderImportServiceImpl::class);
        $this->app->bind(OrderWorkflowService::class, OrderWorkflowServiceImpl::class);
        $this->app->bind(InventoryService::class, InventoryServiceImpl::class);
        $this->app->bind(PaymentService::class, PaymentServiceImpl::class);
        $this->app->bind(KpiService::class, KpiServiceImpl::class);
        $this->app->bind(LeaderboardService::class, LeaderboardServiceImpl::class);

        // Bind repositories
        $this->app->bind(OrderRepository::class, OrderRepositoryImpl::class);
        $this->app->bind(OrderItemRepository::class, OrderItemRepositoryImpl::class);
        $this->app->bind(ProductRepository::class, ProductRepositoryImpl::class);
        $this->app->bind(CustomerRepository::class, CustomerRepositoryImpl::class);
    }

    public function boot(): void
    {
        //
    }
}
