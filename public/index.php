<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Inspector\Inspector;
use Inspector\Adapters\LaravelAdapter;
use Illuminate\Container\Container;

// Create Laravel container and adapter
$container = new Container();
$adapter = new LaravelAdapter($container);
$inspector = new Inspector($adapter);

$services = $inspector->browseServices();
$selectedService = $_GET['service'] ?? null;
$detail = $selectedService ? $inspector->inspectService($selectedService) : null;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Service Container Inspector</title>
</head>
<body>
    <h1>Registered Services</h1>
    <ul>
        <?php foreach ($services as $service): ?>
            <li>
                <a href="?service=<?= urlencode((string)$service) ?>">
                    <?= htmlspecialchars((string)$service, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($selectedService && $detail): ?>
        <h2>Service Details: <?= htmlspecialchars((string)$selectedService, ENT_QUOTES, 'UTF-8') ?></h2>
        <pre><?= htmlspecialchars(print_r($detail, true), ENT_QUOTES, 'UTF-8') ?></pre>
    <?php endif; ?>
</body>
</html>