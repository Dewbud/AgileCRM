<?php

const SYSTEM_TYPES = [
    'name',
    'first_name',
    'last_name',
    'phone',
    'email',
    'company',
    'website',
    'title',
    'owner_id',
];

const HAS_SUBTYPE = [
    'phone',
    'email',
    'address',
    'website',
];

const TYPES = [
    'SYSTEM' => SYSTEM_TYPES,
    'CUSTOM',
];

/**
 * Map key/values to their types
 *
 * @param array $array
 * @return array
 */
function agilecrm_map_types($array)
{
    $mapped = [];
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $mapped[] = $value;
            continue;
        }

        $type = in_array($key, TYPES['SYSTEM']) ? 'SYSTEM' : 'CUSTOM';

        $mapped[] = [
            'type'  => $type,
            'name'  => $key,
            'value' => $value,
        ];
    }

    return $mapped;
}
