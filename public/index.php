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
    $container = function_exists('app') ? app() : new Illuminate\Container\Container();
    $adapter = new LaravelAdapter($container);
} elseif ($adapterType === 'symfony') {
    use Inspector\Adapters\SymfonyAdapter;
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
$filterLower = strtolower($filter);
$services = $inspector->browseServices();

// Enhanced search: by name, class, or interface
if ($filter !== '') {
    $services = array_filter($services, function($s) use ($filterLower, $inspector) {
        if (stripos($s, $filterLower) !== false) {
            return true;
        }
        $details = $inspector->inspectService($s);
        if (isset($details['class']) && stripos((string)$details['class'], $filterLower) !== false) {
            return true;
        }
        if (isset($details['interfaces']) && is_array($details['interfaces'])) {
            foreach ($details['interfaces'] as $iface) {
                if (stripos((string)$iface, $filterLower) !== false) {
                    return true;
                }
            }
        }
        return false;
    });
}
$selectedService = $_GET['service'] ?? null;
$detail = $selectedService ? $inspector->inspectService($selectedService) : null;

// Helper for highlighting matches
function highlight($text, $filter) {
    if (!$filter) return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return preg_replace('/(' . preg_quote($filter, '/') . ')/i', '<mark>$1</mark>', htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Service Container Inspector</title>
    <style>
        mark { background: #ffe066; }
        .deps { margin-left: 2em; }
    </style>
</head>
<body>
    <h1>Registered Services (<?= htmlspecialchars($adapterType, ENT_QUOTES, 'UTF-8') ?>)</h1>
    <form method="get" style="margin-bottom: 1em;">
        <input type="text" name="filter" value="<?= htmlspecialchars($filter, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search by name, class, or interface..." />
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
            <?php $details = $inspector->inspectService($service); ?>
            <li>
                <a href="?<?= http_build_query(['filter' => $filter, 'adapter' => $adapterType, 'service' => $service]) ?>">
                    <?= highlight((string)$service, $filter) ?>
                </a>
                <?php if (isset($details['shared'])): ?>
                    <small>
                        <?= $details['shared'] === true ? 'singleton' : 'non-shared' ?>
                    </small>
                <?php endif; ?>
                <?php if ($class): ?>
                    <small>Class: <?= highlight($class, $filter) ?></small>
                <?php endif; ?>
                <?php if (!empty($interfaces)): ?>
                    <small>Interfaces:
                        <?php foreach ($interfaces as $iface): ?>
                            <?= highlight($iface, $filter) ?>
                        <?php endforeach; ?>
                    </small>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($selectedService && $detail): ?>
        <h2>Service Details: <?= htmlspecialchars((string)$selectedService, ENT_QUOTES, 'UTF-8') ?></h2>
        <?php if (!empty($detail['class'])): ?>
            <div><strong>Class:</strong> <?= htmlspecialchars($detail['class'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($detail['interfaces'])): ?>
            <div><strong>Interfaces:</strong>
                <ul>
                    <?php foreach ($detail['interfaces'] as $iface): ?>
                        <li><?= htmlspecialchars($iface, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (!empty($detail['constructor_dependencies'])): ?>
            <div><strong>Constructor dependencies:</strong>
                <ul class="deps">
                    <?php foreach ($detail['constructor_dependencies'] as $dep): ?>
                        <li>
                            <?= htmlspecialchars($dep['name'], ENT_QUOTES, 'UTF-8') ?>:
                            <?= htmlspecialchars($dep['type'] ?? 'mixed', ENT_QUOTES, 'UTF-8') ?>
                            <?= $dep['isOptional'] ? '(optional)' : '' ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <pre><?= htmlspecialchars(print_r($detail, true), ENT_QUOTES, 'UTF-8') ?></pre>
    <?php endif; ?>

    <?php
    $broken = $inspector->findUnresolvableServices();
    if (!empty($broken)) {
        echo '<h2>Unresolvable Services</h2><ul>';
        foreach ($broken as $service) {
            echo '<li>' . htmlspecialchars($service, ENT_QUOTES, 'UTF-8') . '</li>';
        }
        echo '</ul>';
    }
    ?>

    <?php
    $brokenDetails = $inspector->findUnresolvableServicesWithDetails();
    if (!empty($brokenDetails)) {
        echo '<h2>Unresolvable Services (Detailed)</h2><ul>';
        foreach ($brokenDetails as $service => $info) {
            // Adapter filter (if you want to filter by current adapter)
            if ($adapterFilter && $adapterFilter !== $adapterType) {
                continue;
            }
            // Service name filter
            if ($serviceFilter && stripos($service, $serviceFilter) === false) {
                continue;
            }
            // Error type/message filter
            $errorText = $info['type'] . ': ' . $info['message'];
            if ($errorFilter && stripos($errorText, $errorFilter) === false) {
                continue;
            }
            echo '<li><strong>' . htmlspecialchars($service, ENT_QUOTES, 'UTF-8') . '</strong>: ';
            echo htmlspecialchars($errorText, ENT_QUOTES, 'UTF-8');
            echo ' <small>(' . htmlspecialchars($info['file'], ENT_QUOTES, 'UTF-8') . ':' . $info['line'] . ')</small>';
            if (isset($info['exception']) && $info['exception'] instanceof \Throwable) {
                echo '<details><summary>Stack trace</summary><pre style="max-height:300px;overflow:auto;">' .
                    htmlspecialchars($info['exception']->getTraceAsString(), ENT_QUOTES, 'UTF-8') .
                    '</pre></details>';
            }
            echo '</li>';
        }
        echo '</ul>';
    }
    ?>

    <?php
    $errorFilter = $_GET['error_filter'] ?? '';
    $serviceFilter = $_GET['service_filter'] ?? '';
    $adapterFilter = $_GET['adapter_filter'] ?? $adapterType;
    ?>
    <form method="get" style="margin-bottom: 1em;">
        <input type="text" name="error_filter" value="<?= htmlspecialchars($errorFilter, ENT_QUOTES, 'UTF-8') ?>" placeholder="Filter errors by type or message..." />
        <input type="text" name="service_filter" value="<?= htmlspecialchars($serviceFilter, ENT_QUOTES, 'UTF-8') ?>" placeholder="Filter by service name..." />
        <select name="adapter_filter">
            <option value="laravel" <?= $adapterFilter === 'laravel' ? 'selected' : '' ?>>Laravel</option>
            <option value="symfony" <?= $adapterFilter === 'symfony' ? 'selected' : '' ?>>Symfony</option>
            <option value="psr" <?= $adapterFilter === 'psr' ? 'selected' : '' ?>>PSR-11</option>
        </select>
        <?php
        // Preserve other query params
        foreach ($_GET as $k => $v) {
            if (!in_array($k, ['error_filter', 'service_filter', 'adapter_filter'])) {
                echo '<input type="hidden" name="' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '">';
            }
        }
        ?>
        <button type="submit">Filter</button>
    </form>
</body>
</html>