<?php
/**
 * Copyright (c) 2018 Constantin Galbenu <xprt64@gmail.com>
 */

namespace cqrs;


$options = getopt('', ['src:', 'output:', 'append']);


$srcFolders = \is_array($options['src']) ? $options['src'] : [$options['src']];

$src = implode(' ', array_map(function ($src) {
    return '--src=' . escapeshellarg($src) . '';
}, $srcFolders));

system('php -f ' . __DIR__ . '/validate_aggregate_event_appliers.php -- ' . $src, $return);

if (0 !== $return) {
    exit($return);
}

$scripts = [
    'create_cqrs_command_handlers_map.php', 'create_cqrs_command_validators_map.php',
    'create_cqrs_event_listeners_map.php', 'create_cqrs_query_askers_map.php',
    'create_cqrs_query_handlers_map.php', 'create_cqrs_readmodels_map.php',
    'create_cqrs_saga_event_processors_map.php',
];

echo "<?php \n";

foreach ($scripts as $script) {
    system('php -f ' . __DIR__ . '/' . $script . '  --  --output="-" ' . $src . "", $return);
    if ($return) {
        exit($return);
    }
}
