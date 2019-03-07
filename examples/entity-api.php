<?php

namespace go1\rest\examples;

use go1\rest\wrapper\Manifest;

call_user_func(
    function () {
        // @formatter:off
        Manifest::create()
            ->entityApi()
                ->entityType('user')
                    ->withBaseTable('gc_user')
                    ->withProperty('mail')
                        ->withLabel('Email')
                        ->withDescription("Primary email address of user.")
                        ->end()
                    ->withProperty('status')
                        ->withLabel('Status')
                        ->withDescription('Status of user')
                        ->end()
                    ->end()
        ;
        // @formatter:on
    }
);
