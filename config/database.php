<?php
declare(strict_types=1);
return [
    'host' => getenv('ITALIENISCH_DB_HOST') ?: 'localhost',
    'username' => getenv('ITALIENISCH_DB_USERNAME') ?: 'italienisch_usr',
    'password' => getenv('ITALIENISCH_DB_PASSWORD') ?: '_FwsT0j9iMpr!uy5',
    'database' => getenv('ITALIENISCH_DB_NAME') ?: 'italienisch_db',
];
