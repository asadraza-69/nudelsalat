<?php

declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'Nudelsalat\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $baseDir = __DIR__ . '/src/';

    // Standard PSR-4 resolution first
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Special handling for field inheritance chains
    if (str_starts_with($class, 'Nudelsalat\\Migrations\\Fields\\')) {
        $fieldBase = $baseDir . 'Migrations/Fields/Field.php';
        if (file_exists($fieldBase)) {
            require_once $fieldBase;
        }

        $fieldFile = $baseDir . 'Migrations/Fields/' . basename(str_replace('\\', '/', $class)) . '.php';
        if (file_exists($fieldFile)) {
            require_once $fieldFile;
        }
        return;
    }

    // ORM support files that define multiple related classes/functions
    if (str_starts_with($class, 'Nudelsalat\\ORM\\')) {
        $ormDir = $baseDir . 'ORM/';

        $queryFile = $ormDir . 'Query.php';
        $querySetFile = $ormDir . 'QuerySet.php';
        $signalsFile = $ormDir . 'Signals.php';
        $modelFile = $ormDir . 'Model.php';

        if (
            str_contains($class, 'Query')
            || str_contains($class, 'Aggregation')
            || str_contains($class, 'Paginator')
            || str_contains($class, 'Q\\')
            || str_contains($class, 'F\\')
        ) {
            if (file_exists($queryFile)) {
                require_once $queryFile;
            }
        }

        if (str_contains($class, 'QuerySet') && file_exists($querySetFile)) {
            require_once $querySetFile;
        }

        if (str_contains($class, 'Signal') && file_exists($signalsFile)) {
            require_once $signalsFile;
        }

        if (file_exists($modelFile)) {
            require_once $modelFile;
        }

        return;
    }
});