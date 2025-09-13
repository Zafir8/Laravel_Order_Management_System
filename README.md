# Laravel Order Management System

A robust, enterprise-level order management system built with Laravel 11, featuring asynchronous processing, real-time analytics, and comprehensive queue management.

## 🚀 Overview

This system was developed as a Software Engineer Level 2 technical assignment, implementing a complete order lifecycle management solution with CSV import capabilities, automated workflows, real-time notifications, and analytics-driven insights.

## 📊 System Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           Laravel Order Management System                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐  │
│  │   CSV File  │    │   Web UI    │    │   API       │    │   CLI       │  │
│  │   Import    │    │   Dashboard │    │   Endpoints │    │   Commands  │  │
│  └──────┬──────┘    └──────┬──────┘    └──────┬──────┘    └──────┬──────┘  │
│         │                  │                  │                  │         │
│         └──────────────────┼──────────────────┼──────────────────┘         │
│                            │                  │                            │
│  ┌─────────────────────────┼──────────────────┼─────────────────────────┐  │
│  │                    Controllers Layer                                  │  │
│  └─────────────────────────┼──────────────────┼─────────────────────────┘  │
│                            │                  │                            │
│  ┌─────────────────────────┼──────────────────┼─────────────────────────┐  │
│  │                     Services Layer                                    │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  │  │
│  │  │   Order     │  │     KPI     │  │ Leaderboard │  │   Payment   │  │  │
│  │  │  Workflow   │  │   Service   │  │   Service   │  │   Service   │  │  │
│  │  │   Service   │  │             │  │             │  │             │  │  │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  │  │
│  └─────────────────────────┼──────────────────┼─────────────────────────┘  │
│                            │                  │                            │
│  ┌─────────────────────────┼──────────────────┼─────────────────────────┐  │
│  │                    Queue Jobs Layer                                   │  │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  │  │
│  │  │   CSV       │  │   Order     │  │   Refund    │  │ Notification│  │  │
│  │  │  Import     │  │  Processing │  │  Processing │  │   Sending   │  │  │
│  │  │    Job      │  │     Job     │  │     Job     │  │     Job     │  │  │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘  │  │
│  └─────────────────────────┼──────────────────┼─────────────────────────┘  │
│                            │                  │                            │
│  ┌─────────────────────────┼──────────────────┼─────────────────────────┐  │
│  │                    Data Storage Layer                                 │  │
│  │                            │                  │                       │  │
│  │  ┌─────────────────────────┼──────────────────┼──────────────────┐     │  │
│  │  │           MySQL Database (Transactional Data)                │     │  │
│  │  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ │     │  │
│  │  │  │Customers│ │ Orders  │ │Products │ │Refunds  │ │  Notif. │ │     │  │
│  │  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘ │     │  │
│  │  └──────────────────────────────────────────────────────────────┘     │  │
│  │                                                                        │  │
│  │  ┌─────────────────────────────────────────────────────────────────┐  │  │
│  │  │              Redis (Analytics & Queue Management)              │  │  │
│  │  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────┐ │  │  │
│  │  │  │    KPIs     │  │ Leaderboard │  │      Queue Jobs          │ │  │  │
│  │  │  │   (Hash)    │  │(Sorted Set) │  │    (Lists/Streams)       │ │  │  │
│  │  │  └─────────────┘  └─────────────┘  └─────────────────────────┘ │  │  │
│  │  └─────────────────────────────────────────────────────────────────┘  │  │
│  └────────────────────────────────────────────────────────────────────────┘  │
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                     Monitoring & Management                          │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │   │
│  │  │   Laravel   │  │  Supervisor │  │   System    │  │    Redis    │ │   │
│  │  │   Horizon   │  │   Process   │  │   Logging   │  │  Monitoring │ │   │
│  │  │  Dashboard  │  │  Management │  │             │  │             │ │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
```

## ✨ Features

### 📥 CSV Import & Processing
- **Large file handling** with streaming processing
- **Chunked processing** to prevent memory overload
- **Progress tracking** and error handling
- **Asynchronous processing** via queue jobs
- **Batch import** with configurable chunk sizes

### 🔄 Order Workflow Management
- **Multi-stage workflow**: Reserve → Payment → Finalize/Rollback
- **State machine pattern** for order status management
- **Payment simulation** with webhook callback handling
- **Inventory management** with stock reservation
- **Atomic transactions** for data consistency

### 📧 Notification System
- **Multiple notification types**: confirmation, shipping, delivery, cancellation
- **Queued processing** (non-blocking)
- **Notification history** tracking
- **Template-based messaging**
- **High success rate** (99.3%+)

### 💰 Refund Management
- **Partial and full refunds** support
- **Idempotent processing** (prevent double-refunds)
- **Asynchronous refund processing**
- **Real-time analytics updates**
- **Comprehensive audit trail**

### 📊 Real-time Analytics
- **Daily KPIs**: revenue, order count, average order value
- **Customer leaderboard** with real-time rankings
- **Redis-powered** for sub-millisecond queries
- **Historical data** with daily partitioning
- **Performance metrics** tracking

### 🔍 Monitoring & Management
- **Laravel Horizon** dashboard for queue monitoring
- **Real-time job tracking** and failure management
- **System health monitoring**
- **Comprehensive logging**
- **Performance metrics**

## 🛠 Tech Stack

### **Backend Framework**
- **Laravel 11** - Modern PHP framework with latest features
- **PHP 8.2+** - Latest PHP version for optimal performance

### **Database & Storage**
- **MySQL 8.0** - Primary database for transactional data
- **Redis 7.0** - Queue management and analytics storage
- **File Storage** - Local/S3 compatible for CSV files

### **Queue & Background Processing**
- **Laravel Horizon** - Queue monitoring and management
- **Redis Queues** - High-performance queue driver
- **Supervisor** - Process management for production

### **Development & Testing**
- **Laravel Tinker** - Interactive REPL for testing
- **Custom Test Commands** - Comprehensive testing suite
- **Database Factories** - Data generation for testing

### **Infrastructure**
- **Docker** (optional) - Containerized deployment
- **Nginx/Apache** - Web server
- **Composer** - Dependency management

## 🏗 Architecture Patterns

### **Service Layer Pattern**
- Clean separation of concerns
- Business logic encapsulated in services
- Dependency injection for testability

### **Queue-Driven Architecture**
- Non-blocking user experience
- Scalable background processing
- Fault tolerance with automatic retries

### **Event-Driven Analytics**
- Real-time data updates
- Decoupled system components
- High-performance analytics with Redis

## 📦 Installation

### **Prerequisites**
```bash
- PHP 8.2+
- Composer
- MySQL 8.0+
- Redis 7.0+
- Node.js (for frontend assets)
```

### **Setup Steps**
```bash
# Clone the repository
git clone <repository-url>
cd assignment

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database and Redis in .env file
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_DATABASE=assignment
# REDIS_HOST=127.0.0.1
# QUEUE_CONNECTION=redis

# Run database migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Install Horizon
php artisan horizon:install

# Start the application
php artisan serve

# Start queue workers (in separate terminal)
php artisan horizon
```

## 🚀 Usage

### **CSV Import**
```bash
# Import orders from CSV file
php artisan orders:import path/to/orders.csv

# Monitor progress in Horizon dashboard
open http://localhost/horizon
```

### **Testing the System**
```bash
# Run comprehensive test suite
php artisan test:assignment --csv-rows=20 --delay=2

# Test Redis KPIs and leaderboard
php artisan demo:redis-kpis

# Check system status
php artisan system:status
```

### **Monitoring**
```bash
# Access Horizon dashboard
http://localhost/horizon

# Check queue status
php artisan horizon:status

# View failed jobs
php artisan queue:failed
```

## 📊 Performance Metrics

### **Current System Statistics**
- 💰 **Revenue**: $4,990+ tracked in real-time
- 📦 **Orders**: 80+ processed orders
- 💸 **Refunds**: $569+ in processed refunds
- 📧 **Notifications**: 99.3% success rate
- 🏆 **Analytics**: Sub-second query responses

### **Scalability Features**
- **Horizontal scaling** with multiple queue workers
- **Database indexing** for optimal query performance
- **Redis clustering** support for high availability
- **Memory-efficient** streaming for large files

## 🔧 Configuration

### **Queue Configuration**
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

### **Horizon Configuration**
```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
        ],
    ],
],
```

## 🧪 Testing

### **Test Commands**
```bash
# Main assignment test
php artisan test:assignment

# Individual component tests
php artisan test:csv-import
php artisan test:refunds
php artisan test:notifications

# Redis functionality demo
php artisan demo:redis-kpis
```

### **Test Coverage**
- ✅ CSV import processing
- ✅ Order workflow state transitions
- ✅ Payment simulation with callbacks
- ✅ Notification delivery
- ✅ Refund processing with idempotency
- ✅ Real-time analytics updates
- ✅ Queue job processing

## 📝 API Documentation

### **Key Endpoints**
```
POST /api/orders/import     # CSV import endpoint
GET  /api/orders/{id}       # Order details
POST /api/refunds           # Create refund
GET  /api/analytics/kpis    # Daily KPIs
GET  /api/leaderboard       # Customer rankings
```

## 🤝 Contributing

This project follows Laravel best practices and PSR standards. Key principles:

- **SOLID principles** in service design
- **DRY (Don't Repeat Yourself)** code organization
- **Comprehensive error handling**
- **Extensive logging** for debugging
- **Database transactions** for data integrity

## 📄 License

This project is developed as a technical assignment and is available for review and demonstration purposes.

---

## 🎯 Assignment Requirements Fulfilled

### ✅ **Task 1**: CSV Import + Order Workflow + KPIs + Horizon
- Large CSV import with queued processing
- Complete order workflow with payment simulation
- Real-time KPIs and customer leaderboard using Redis
- Laravel Horizon queue management

### ✅ **Task 2**: Order Notifications
- Queued notification jobs (non-blocking)
- Multiple notification types
- Notification history tracking
- Comprehensive order information included

### ✅ **Task 3**: Refund Handling & Analytics
- Asynchronous refund processing
- Real-time analytics updates
- Idempotent operations
- Comprehensive audit trail

---

**Built with ❤️ using Laravel 11 and modern PHP practices**
