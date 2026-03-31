<?php
function getDbConfig() {
    $defaultConfig = [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'airline_system',
        'port' => 3306
    ];

    $configFile = __DIR__ . DIRECTORY_SEPARATOR . 'db_config.php';

    if (file_exists($configFile)) {
        $fileConfig = require $configFile;
        if (is_array($fileConfig)) {
            $defaultConfig = array_merge($defaultConfig, $fileConfig);
        }
    }

    return $defaultConfig;
}

function getDbConnection() {
    $config = getDbConfig();

    $conn = @mysqli_connect(
        $config['host'],
        $config['user'],
        $config['password'],
        $config['database'],
        (int) $config['port']
    );

    if (!$conn) {
        $safeUser = $config['user'];
        $safeHost = $config['host'];
        die(
            "Database connection failed for '{$safeUser}' on '{$safeHost}'. " .
            "Please check db_config.php and update your MySQL password. " .
            "MySQL says: " . mysqli_connect_error()
        );
    }

    return $conn;
}
?>
