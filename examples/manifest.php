<?php

namespace go1\rest\examples;

use DI\Container;
use go1\core\customer\user_explore\Controller;
use go1\rest\middleware\JsonSchemaValidatorMiddleWare;
use go1\rest\middleware\ObjectMapper;
use go1\rest\RestService;
use go1\rest\tests\fixtures\FoodCreatedEvent;
use go1\rest\tests\fixtures\User;
use go1\rest\tests\fixtures\UserCreateController;
use go1\rest\wrapper\Manifest;
use JsonSchema\Validator;

# ---------------------------------------------------------------
# Builder interface for composing CI files without googling
# ---------------------------------------------------------------
// @formatter:off
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
    ->rest()
        ->withServiceName('user-explore')
        ->withVersion('v1.0')
        ->withEsOption('default', getenv('ES_URL') ?: 'http://localhost:9200')
        ->withConfigFile(__DIR__ . '/resources/config.default.php')
        ->set('userCreateJsonValidatorMiddleware', function (Container $c) {
            return new JsonSchemaValidatorMiddleWare(
                $c->get(Validator::class),
                $c->get(ObjectMapper::class),
                User::class,
                'file://'.realpath(__DIR__.'/../tests/fixtures/json_schema/user.json')
            );
        })
        ->withBootCallback(
            function (RestService $rest, Manifest $builder) {
                if (!defined('ES_INDEX')) {
                    define('ES_INDEX', getenv('ES_INDEX') ?: 'go1_dev');
                }
            }
        )
        ->end()
    ->stream()
        ->on(
            FoodCreatedEvent::NAME,
            'Notification',
            function (FoodCreatedEvent $event) {}
        )
        ->endStream()
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
        ->withPath('/user', 'POST')
            ->withController([UserCreateController::class, 'post'])
            ->withMiddleware('userCreateJsonValidatorMiddleware')
            ->responses('200')
                ->withDescription('Create new user')
                ->withContent('application/json')
                    ->withSchema([
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'int'],
                        ]
                    ])
                    ->end()
                ->end()
            ->end()
        ->end()
    ->phpunit()
        ->withBootstrapFile('./vendor/autoload.php')
        ->withTestSuite('go1', ['./tests'])
        ->withWhitelistDirectory('./')
        ->withoutWhitelistDirectory('./vendor')
        ->end()
    ->dockerCompose()
        ->withEnv('_DOCKER_ES_URL')
        ->withEnv('_DOCKER_ES_INDEX')
        ->end()
    ;
// @formatter:on
