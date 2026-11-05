<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Blog Domain
    |--------------------------------------------------------------------------
    |
    | The host the public blog is served from. It shares this application's
    | document root, so every blog route is constrained to this domain to
    | keep it from colliding with the portfolio routes on the apex host.
    |
    | This must never resolve to null: an unconstrained blog "/" route would
    | shadow the portfolio welcome page.
    |
    */

    'domain' => env('BLOG_DOMAIN', 'blog.'.parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),

    /*
    |--------------------------------------------------------------------------
    | Posts Per Page
    |--------------------------------------------------------------------------
    */

    'per_page' => 9,

    /*
    |--------------------------------------------------------------------------
    | Permanent Redirects
    |--------------------------------------------------------------------------
    |
    | Retired blog paths mapped to their replacements, answered with a 301 so
    | links and search engine entries for the old URLs keep working. The
    | interview series was first published under Croatian slugs and tags.
    |
    */

    'redirects' => [
        'senior-backend-intervju-oop-i-php' => 'senior-backend-interview-oop-and-php',
        'senior-backend-intervju-baze-podataka' => 'senior-backend-interview-databases',
        'senior-backend-intervju-rest-i-api' => 'senior-backend-interview-rest-and-api',
        'senior-backend-intervju-arhitektura' => 'senior-backend-interview-architecture',
        'senior-backend-intervju-sigurnost' => 'senior-backend-interview-security',
        'senior-backend-intervju-laravel' => 'senior-backend-interview-laravel',
        'senior-backend-intervju-kada-ne-znas-odgovor' => 'senior-backend-interview-when-you-dont-know-the-answer',
        'senior-backend-intervju-trik-pitanja' => 'senior-backend-interview-trick-questions',
        'tags/intervju' => 'tags/interview',
        'tags/baze-podataka' => 'tags/database',
        'tags/arhitektura' => 'tags/architecture',
        'tags/sigurnost' => 'tags/security',
        'tags/karijera' => 'tags/career',
    ],

];
