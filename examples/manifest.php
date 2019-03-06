<?php

namespace go1\rest\examples;

use go1\core\customer\user_explore\Controller;
use go1\rest\RestService;
use go1\rest\wrapper\Manifest;

# ---------------------------------------------------------------
# Builder interface for composing CI files without googling
# ---------------------------------------------------------------
return Manifest::create()
    ->composer()
        ->withName('go1-core/customer/user-explore')
        ->withPreferStable(true)
        ->withMinimumStability('stable')
        ->withPsr4Autoload('go1\\core\\customer\\user_explore\\', './')
        ->require('beberlei/assert', '^3.2.0')
        ->require('elasticsearch/elasticsearch', '^5.4.0')
        ->require('ongr/elasticsearch-dsl', '^5.0')
        ->end()
    ->service()
        ->withServiceName('user-explore')
        ->withVersion('v1.0')
        ->withEsOption('default', getenv('ES_URL') ?: 'http://localhost:9200')
        ->withConfigFile(__DIR__ . '/resources/config.default.php')
        ->withBootCallback(
            function (RestService $app, Manifest $builder) {
                if (!defined('ES_INDEX')) {
                    define('ES_INDEX', getenv('ES_INDEX') ?: 'go1_dev');
                }
            }
        )
        ->end()
    ->swagger()
        ->withOpenAPI('3.0.0')
        ->withServer('%user-explore%', 'Service for user exploring')
        ->withPath('/lo/{portalId}/{loId}/learners/{keyword}', 'GET')
            # ---------------------------------------------------------------
            # config builder auto register this route with slim
            # ---------------------------------------------------------------
            ->withController([Controller::class, 'get'])
            ->withSummary('Find users who has enrolment with the learning object in certain portal.')
            ->withParam('portalId')->inPath()->required(true)->withTypeInteger()->end()
            ->withParam('loId')->inPath()->required(true)->withTypeInteger()->end()
            ->withParam('keyword')->inPath()->required(false)->withTypeString()->withDefaultValue('')->end()
            ->end()
        ->withPath('/lo/{portalId}/{loId}/non-learners/{keyword}', 'GET')
            ->withController([Controller::class, 'get'])
            ->withSummary('Find users who does not have enrolment with the learning object in certain portal.')
            ->withParam('portalId')->inPath()->required(true)->withTypeInteger()->end()
            ->withParam('loId')->inPath()->required(true)->withTypeInteger()->end()
            ->withParam('keyword')->inPath()->required(false)->withTypeString()->withDefaultValue('')->end()
            ->end()
        ->end()
    ->phpunit()
        ->withBootstrapFile('./vendor/autoload.php')
        ->withTestSuite('go1', ['./tests'])
        ->withWhitelistDirectory('./')
        ->withoutWhitelistDirectory('./tests')
        ->withoutWhitelistDirectory('./vendor')
        ->end()
    ->dockerCompose()
        ->withEnv('_DOCKER_ES_URL')
        ->withEnv('_DOCKER_ES_INDEX')
        ->end()
    ;
