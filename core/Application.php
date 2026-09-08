<?php

declare(strict_types=1);

namespace Core;

class Application
{
    private static ?self $instance = null;
    private Container $container;
    private Router $router;
    private bool $booted = false;

    private function __construct() {}

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->container = new Container();
        $this->router    = new Router();

        $this->registerBindings();
        $this->registerGlobalMiddleware();

        $this->booted = true;
    }

    private function registerBindings(): void
    {
        // Repository bindings — interface → concrete
        $bindings = [
            \App\Repositories\Contracts\UserRepositoryInterface::class       => \App\Repositories\UserRepository::class,
            \App\Repositories\Contracts\AuthRepositoryInterface::class       => \App\Repositories\AuthRepository::class,
            \App\Repositories\Contracts\ProductRepositoryInterface::class    => \App\Repositories\ProductRepository::class,
            \App\Repositories\Contracts\CategoryRepositoryInterface::class   => \App\Repositories\CategoryRepository::class,
            \App\Repositories\Contracts\BrandRepositoryInterface::class      => \App\Repositories\BrandRepository::class,
            \App\Repositories\Contracts\CartRepositoryInterface::class       => \App\Repositories\CartRepository::class,
            \App\Repositories\Contracts\OrderRepositoryInterface::class      => \App\Repositories\OrderRepository::class,
            \App\Repositories\Contracts\AddressRepositoryInterface::class    => \App\Repositories\AddressRepository::class,
            \App\Repositories\Contracts\ReviewRepositoryInterface::class     => \App\Repositories\ReviewRepository::class,
            \App\Repositories\Contracts\SliderRepositoryInterface::class    => \App\Repositories\SliderRepository::class,
            \App\Repositories\Contracts\ProductOptionRepositoryInterface::class      => \App\Repositories\ProductOptionRepository::class,
            \App\Repositories\Contracts\ProductOptionValueRepositoryInterface::class => \App\Repositories\ProductOptionValueRepository::class,
            \App\Repositories\Contracts\DeliveryMethodRepositoryInterface::class    => \App\Repositories\DeliveryMethodRepository::class,
            \App\Repositories\Contracts\PaymentTypeRepositoryInterface::class       => \App\Repositories\PaymentTypeRepository::class,
        ];

        foreach ($bindings as $abstract => $concrete) {
            $this->container->bind($abstract, $concrete);
        }
    }

    private function registerGlobalMiddleware(): void
    {
        $this->router->setGlobalMiddleware([
            \App\Middleware\MaintenanceMiddleware::class,
            \App\Middleware\CorsMiddleware::class,
            \App\Middleware\JsonMiddleware::class,
        ]);
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function run(): Response
    {
        $request = Request::capture();
        $this->container->instance(Request::class, $request);
        return $this->router->dispatch($request);
    }
}
