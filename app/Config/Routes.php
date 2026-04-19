<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/notes', 'Home::list');

// Note routes
$routes->get('/note/new', 'Note::new');
$routes->get('/note/(:num)/edit', 'Note::edit/$1');
$routes->get('/note/(:num)', 'Note::find/$1');
$routes->get('/note/(:num)/revisions', 'Note::listRevisions/$1');
$routes->get('/note/(:num)/revision/(:num)', 'Note::findRevision/$1/$2');
$routes->post('/note', 'Note::create');
$routes->post('/note/preview', 'Note::preview');
$routes->patch('/note/(:num)', 'Note::update/$1');
$routes->delete('/note/(:num)', 'Note::delete/$1');

// Admin routes
$routes->get('/admin', 'Admin\Home::index');
$routes->get('/admin/notes/key', 'Admin\Notes::key');
$routes->get('/admin/import', 'Admin\Import::index');
$routes->post('/admin/import', 'Admin\Import::process');

// API routes
$routes->match(['get', 'options'], '/api/test/ping', 'Api\Test::ping');

// Command line routes
$routes->cli('cli/test/index/(:segment)', 'CLI\Test::index/$1');
$routes->cli('cli/test/count', 'CLI\Test::count');

// Metrics route
$routes->post('/metrics/receive', 'Metrics::receive');

// Logout route
$routes->get('/logout', 'Auth::logout');

// Unauthorised route
$routes->get('/unauthorised', 'Unauthorised::index');

// Custom 404 route
$routes->set404Override('App\Controllers\Errors::show404');

// Debug routes
$routes->get('/debug', 'Debug\Home::index');
$routes->get('/debug/(:segment)', 'Debug\Rerouter::reroute/$1');
$routes->get('/debug/(:segment)/(:segment)', 'Debug\Rerouter::reroute/$1/$2');
