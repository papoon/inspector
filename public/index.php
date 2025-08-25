<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Inspector\Inspector;

// Try to load config from project root first, then fallback to package config
$configPaths = [
    dirname(__DIR__, 2) . '/config/container.php', // User's project root
    __DIR__ . '/../config/container.php',          // Package default
];

$config = [];
foreach ($configPaths as $path) {
    if (file_exists($path)) {
        $config = require $path;
        break;
    }
}

$adapterType = $_GET['adapter'] ?? getenv('INSPECTOR_ADAPTER') ?? 'laravel';

if ($adapterType === 'laravel') {
    use Inspector\Adapters\LaravelAdapter;
    // Use the real Laravel container if available
    $container = function_exists('app') ? app() : new Illuminate\Container\Container();
    $adapter = new LaravelAdapter($container);
} elseif ($adapterType === 'symfony') {
    use Inspector\Adapters\SymfonyAdapter;
    // Use the real Symfony container if available
    $container = isset($GLOBALS['kernel']) ? $GLOBALS['kernel']->getContainer() : new Symfony\Component\DependencyInjection\ContainerBuilder();
    $adapter = new SymfonyAdapter($container);
} elseif ($adapterType === 'psr') {
    use Inspector\Adapters\PsrAdapter;
    $psrConfig = $config['psr'] ?? [];
    $class = $psrConfig['class'] ?? null;
    $args = $psrConfig['args'] ?? [];
    if ($class && class_exists($class)) {
        $container = new $class(...$args);
        $adapter = new PsrAdapter($container);
    } else {
        die('PSR-11 container class not configured or not found.');
    }
}

$inspector = new Inspector($adapter);

$filter = $_GET['filter'] ?? '';
$services = $inspector->browseServices();
if ($filter !== '') {
    $services = array_filter($services, fn($s) => stripos($s, $filter) !== false);
}
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
    <h1>Registered Services (<?= htmlspecialchars($adapterType, ENT_QUOTES, 'UTF-8') ?>)</h1>
    <form method="get" style="margin-bottom: 1em;">
        <input type="text" name="filter" value="<?= htmlspecialchars($filter, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search services..." />
        <select name="adapter">
            <option value="laravel" <?= $adapterType === 'laravel' ? 'selected' : '' ?>>Laravel</option>
            <option value="symfony" <?= $adapterType === 'symfony' ? 'selected' : '' ?>>Symfony</option>
            <option value="psr" <?= $adapterType === 'psr' ? 'selected' : '' ?>>PSR-11</option>
        </select>
        <button type="submit">Search</button>
        <?php if ($selectedService): ?>
            <input type="hidden" name="service" value="<?= htmlspecialchars((string)$selectedService, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>
    </form>
    <ul>
        <?php foreach ($services as $service): ?>
            <li>
                <a href="?<?= http_build_query(['filter' => $filter, 'adapter' => $adapterType, 'service' => $service]) ?>">
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