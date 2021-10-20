<?php


return [
    'api_key'=>env('DEEPL_API_KEY'),
    'api_address'=>env('DEEPL_API_ADDRESS'),
    'api_version'=>env('DEEPL_API_VERSION', 2),
    'api_chunk_size'=>env('DEEPL_API_CHUNK_SIZE', 50),
    'from_language'=>env('DEEPL_FROM_LANGUAGE'),
    'to_languages'=>env('DEEPL_TO_LANGUAGES') ? explode(',', env('DEEPL_TO_LANGUAGES')) : [],
];