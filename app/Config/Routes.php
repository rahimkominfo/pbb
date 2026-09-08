<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('api/realisasi', 'Home::getChartData');

// Auth routes
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// Admin group
$routes->group('admin', function(RouteCollection $routes) {
    $routes->get('/', 'Admin::dashboard');
    $routes->get('dashboard', 'Admin::dashboard');
    
    // Master Kolektor
    $routes->get('kolektor', 'Admin::kolektor');
    $routes->post('kolektor/save', 'Admin::saveKolektor');
    $routes->post('kolektor/delete', 'Admin::deleteKolektor');
    $routes->post('kolektor/copy', 'Admin::copyKolektor');
    $routes->get('kolektor/get-desas', 'Admin::getDesasByKecamatan');
    $routes->get('kolektor/check-duplicate', 'Admin::checkDuplicateKolektor');
    
    // Master Wilayah (Kecamatan & Desa)
    $routes->get('wilayah', 'Admin::wilayah');
    $routes->post('wilayah/kecamatan/save', 'Admin::saveKecamatan');
    $routes->post('wilayah/desa/save', 'Admin::saveDesa');
    
    // Transaksi Set Target
    $routes->get('target', 'Admin::target');
    $routes->post('target/save', 'Admin::saveTarget');
    $routes->post('target/edit-fetch', 'Admin::fetchTarget');
    
    // Transaksi Input Setor
    $routes->get('setor', 'Admin::setor');
    $routes->post('setor/save', 'Admin::saveSetor');
    $routes->post('setor/delete', 'Admin::deleteSetor');
    
    // Pengguna
    $routes->get('pengguna', 'Admin::pengguna');
    $routes->post('pengguna/save', 'Admin::savePengguna');
    $routes->post('pengguna/reset', 'Admin::resetPassword');
    $routes->post('pengguna/delete', 'Admin::deletePengguna');

    // Laporan Insentif
    $routes->get('insentif', 'Admin::insentif');
    $routes->get('insentif/export', 'Admin::exportInsentif');
});


