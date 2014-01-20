<?php
/**
 * Our MUC caches
 */
$definitions = array(
    'aspirecache' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'ttl' => 14400
    ),
    'aspirecache_json' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'ttl' => 14400
    )
);