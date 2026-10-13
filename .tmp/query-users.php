<?php
$db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
foreach ($db->query('select id,name,email from users') as $row) echo json_encode($row), PHP_EOL;
