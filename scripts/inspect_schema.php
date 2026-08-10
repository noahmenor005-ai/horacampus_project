<?php
$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
foreach (['users', 'enseignants'] as $t) {
    $cols = [];
    $stmt = $db->query('PRAGMA table_info(' . $t . ')');
    foreach ($stmt as $row) {
        $cols[] = $row['name'];
    }
    echo $t . ': ' . implode(',', $cols) . PHP_EOL;
}
