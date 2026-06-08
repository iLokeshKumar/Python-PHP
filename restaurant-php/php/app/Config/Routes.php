<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'CartController::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function ($routes) {
    $routes->get('orders',        'OrderController::index');
    $routes->get('orders/(:num)', 'OrderController::show/$1');
});

$routes->group('cart', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('',        'CartController::index');
    $routes->get('data',    'CartController::data');
    $routes->post('add',    'CartController::add');
    $routes->post('update', 'CartController::update');
    $routes->post('remove', 'CartController::remove');
    $routes->post('clear',  'CartController::clear');
});
