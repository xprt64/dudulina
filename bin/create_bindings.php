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

system('php -f ' . __DIR__ . '/validate_aggregate_event_appliers.php -- '  . $src, $return);

if (0 !== $return) {
    exit($return);
}

echo "<?php \n";

system('php -f ' . __DIR__ . '/create_cqrs_command_handlers_map.php  --  --output="-" ' . $src . "", $return);
system('php -f ' . __DIR__ . '/create_cqrs_command_validators_map.php  --  --output="-" ' . $src . "", $return);
system('php -f ' . __DIR__ . '/create_cqrs_event_listeners_map.php  --  --output="-" ' . $src . "", $return);
system('php -f ' . __DIR__ . '/create_cqrs_query_askers_map.php  --  --output="-" ' . $src . "", $return);
system('php -f ' . __DIR__ . '/create_cqrs_query_handlers_map.php  --  --output="-" ' . $src . "", $return);
system('php -f ' . __DIR__ . '/create_cqrs_readmodels_map.php  --  --output="-" ' . $src . "", $return);
system('php -f ' . __DIR__ . '/create_cqrs_saga_event_processors_map.php  --  --output="-" ' . $src . "", $return);
