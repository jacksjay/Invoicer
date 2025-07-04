<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
// Invoice routes
$routes->get('/invoice', 'Invoice::index');
$routes->get('/invoices', 'Invoice::index'); // Alternative route
$routes->post('/invoice/getInvoicesAjax', 'Invoice::getInvoicesAjax');
$routes->get('/invoice/getInvoicesAjax', 'Invoice::getInvoicesAjax'); // Allow GET as well

// API-style routes (optional)
$routes->group('api', function($routes) {
    $routes->get('invoices', 'Invoice::getInvoicesAjax');
    $routes->post('invoices', 'Invoice::getInvoicesAjax');
});
