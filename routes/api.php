<?php

declare(strict_types=1);

use Core\Application;

$router = Application::getInstance()->getRouter();

foreach ([
    'auth', 'home', 'products', 'categories',
    'brands', 'deals', 'cart', 'checkout',
    'orders', 'profile', 'addresses', 'sliders',
    'product-options', 'delivery-methods', 'payment-types',
] as $group) {
    require __DIR__ . "/V1/{$group}.php";
}
