<?php

return ['errors' => [
    'configuration' => 'Identification is not configured yet. Please contact an administrator.',
    'image' => 'The photo could not be read. Please upload the fish photo again.',
    'connection' => 'The identification service could not be reached. Please try again.',
    'timeout' => 'Identification timed out before the AI finished processing the photo. Please try again.',
    'rate_limit' => 'The identification service is busy. Please try again later.',
    'provider' => 'The identification service is unavailable. Please try again later.',
    'response' => 'The identification result could not be read. Please try again.',
], 'reference' => [
    'ambiguous_title' => 'Choose an iNaturalist reference',
    'ambiguous_body' => 'Several active fish taxa have this scientific name. Select a reference explicitly; no taxon has been selected automatically.',
    'choose' => 'Use this reference',
    'photo_source' => 'Photo source',
    'timeout' => 'Fetching the iNaturalist reference timed out. Please try again.',
    'connection' => 'Could not connect to iNaturalist. Please try again.',
    'not_found' => 'No active fish species with the exact scientific name was found on iNaturalist.',
    'rate_limit' => 'The iNaturalist request limit was reached. Please wait and try again.',
    'provider' => 'iNaturalist is unavailable or returned invalid data. Please try again.',
    'failure' => 'The iNaturalist reference could not be fetched. Please try again.',
    'network' => 'Could not connect to the server. Check your connection and try again.',
    'request_limit' => 'Too many requests. Please wait and try again.',
    'session' => 'Your session has expired. Please sign in again.',
    'retry' => 'Retry fetching the reference',
    'loading' => 'Fetching the iNaturalist reference for the selected candidate...',
]];
