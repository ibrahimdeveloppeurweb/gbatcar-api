<?php
require 'vendor/autoload.php';

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv('.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$conn = $container->get('doctrine')->getConnection();

$months = 41;
$startDate = (new \DateTime())->modify("-{$months} months")->format('Y-m-01');
$groupByYear = true;

$datePart = '%Y';
$format = 'Y';
$step = '+1 year';

// Monthly/Yearly Expected
$sqlExpected = "
    SELECT 
        DATE_FORMAT(ps.expected_date, '{$datePart}') as period,
        SUM(ps.amount) as expected
    FROM payment_schedule ps
    WHERE ps.deleted_at IS NULL AND ps.expected_date >= :start AND ps.expected_date <= LAST_DAY(CURRENT_DATE())
    GROUP BY DATE_FORMAT(ps.expected_date, '{$datePart}')
    ORDER BY period ASC
";
$expectedRaw = $conn->fetchAllAssociative($sqlExpected, ['start' => $startDate]);

$expectedByPeriod = [];
foreach ($expectedRaw as $row)
    $expectedByPeriod[$row['period']] = (float)$row['expected'];

$trendData = [];
$currentDate = new \DateTime($startDate);
$endDate = new \DateTime('last day of this month');
$endKey = $endDate->format($format);
$runningDebt = 0;

echo "END KEY: $endKey\n";

$periodKey = "";
$maxIter = 100;
$i = 0;
while ($i++ < $maxIter) {
    $periodKey = $currentDate->format($format);
    echo "ITERATION periodKey: $periodKey (currentDate: " . $currentDate->format('Y-m-d') . ")\n";

    $periodExpected = $expectedByPeriod[$periodKey] ?? 0;
    $targetForPeriod = $periodExpected + $runningDebt;

    $trendData[] = [
        'month' => $periodKey,
        'expected' => $targetForPeriod,
        'paid' => 0
    ];

    $runningDebt = $targetForPeriod;

    if ($periodKey === $endKey)
        break;

    $currentDate->modify($step);
}

echo json_encode($trendData, JSON_PRETTY_PRINT);
echo "\n";