<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    | Penova resolves page names through a custom convention
    | ("Core/Users/Index" → resources/js/Core/Pages/Users/Index.vue,
    | "Modules/X/…" → resources/js/Modules/X/Pages/…), inserting a
    | "Pages" segment the default existence check cannot know about — so
    | that check is disabled. assertInertia() component/prop assertions
    | still work as usual.
    */

    'testing' => [
        'ensure_pages_exist' => false,
        'page_paths' => [resource_path('js')],
        'page_extensions' => ['js', 'vue'],
    ],

];
