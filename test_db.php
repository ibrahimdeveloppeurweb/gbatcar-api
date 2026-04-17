<?php
require __DIR__ . '/vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$dm = $container->get('App\Manager\Admin\DashboardManager');
$data = $dm->getMainDashboardData(6);
print_r($data['revenueChartData']);
