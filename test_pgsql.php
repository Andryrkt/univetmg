<?php
echo "Testing PostgreSQL extensions:\n";
echo "PDO_PGSQL: " . (extension_loaded('pdo_pgsql') ? 'LOADED' : 'MISSING') . "\n";
echo "PGSQL: " . (extension_loaded('pgsql') ? 'LOADED' : 'MISSING') . "\n";

try {
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=postgres', 'postgres', 'postgres!');
    echo "Database connection: SUCCESS\n";
} catch (Exception $e) {
    echo "Database connection: FAILED - " . $e->getMessage() . "\n";
}
