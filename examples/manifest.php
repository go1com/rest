<?php

namespace go1\rest\examples;

use DI\Container;
use go1\core\customer\user_explore\Controller;
use go1\rest\middleware\JsonSchemaValidatorMiddleWare;
use go1\rest\RestService;
use go1\rest\tests\fixtures\AcmeDatabaseConnection;
use go1\rest\tests\fixtures\AcmeDatabaseSchema;
use go1\rest\tests\fixtures\FoodCreatedEvent;
use go1\rest\tests\fixtures\User;
use go1\rest\tests\fixtures\UserCreateController;
use go1\rest\util\ObjectMapper;
use go1\rest\wrapper\Manifest;
use JsonSchema\Validator;

/**
 * Motivation
 * ----
 *
 * 1. Builder interface for composing CI files without Googling
 *
 *   a. Out good developer spent a lot of time (about 8 hours) for building and deploy a single service to production.
 *   b There a lot of customisation in too many places (CI config, Testing, Docker build, configuration, …) — all of them
 *      does not have builder interface, easy making mistakes.
 *
 * 2. We no longer have huge services, but only micro services, the service that do very limited things but doing well.
 *
 *   a. But it's not easy to know, which services, which database, message queue it depends on.
 *   b. And it's also hard to know which endpoints its providing.
 *      - Each endpoint, we need time to track down input and output format.
 *   c. Manifest builder also provide simple way to define event routing.
 *   b. … as well as  database schema the service is providing.
 */

// @formatter:off

$manifest = Manifest::create();

# =====================
# define composer.json
# ---------------------
$manifest->composer()
    ->withName('go1-core/customer/user-explore')
    ->withPreferStable(true)
    ->withMinimumStability('stable')
    ->withPsr4Autoload('go1\\core\\customer\\user_explore\\', './')
    ->require('beberlei/assert', '^3.2.0')
    ->require('elasticsearch/elasticsearch', '^5.4.0')
    ->require('ongr/elasticsearch-dsl', '^5.0')
    ->endComposer();

# =====================
# define phpunit.xml
# =====================
$manifest->phpunit()
    ->withBootstrapFile('./vendor/autoload.php')
    ->withTestSuite('go1', ['./tests'])
    ->withWhitelistDirectory('./')
    ->withoutWhitelistDirectory('./vendor')
    ->end();

# =====================
# define docker-compose.yml
# =====================
$manifest->dockerCompose()
    ->withEnv('_DOCKER_ES_URL')
    ->withEnv('_DOCKER_ES_INDEX')
    ->end();

# =====================
# Define event-listening
# =====================
$manifest->stream()
    ->on(FoodCreatedEvent::NAME, 'Notification', function (FoodCreatedEvent $event) {})
    ->endStream();

# =====================
# Define REST service configuration
# =====================
$manifest->rest()
    ->set('env', 'qa')
    ->withServiceName('user-explore')
    ->withVersion('v1.0')
    ->withEsOption('default', getenv('ES_URL') ?: 'http://localhost:9200')
    ->withConfigFile(__DIR__ . '/resources/config.default.php')

    # --- caching config ---
    # Caching for performance, should only enable on production
    ->set('di.enable-compile', true) # must have APCU extension enabled
    ->set('marshaller.cache', true)  # must have APCU extension enabled
    # ! --- caching config ---
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
    # ---------------------
    # Database example
    ->set('dbOptions', ['acme' => AcmeDatabaseConnection::connectionOptions()])
    ->withDatabaseSchema(AcmeDatabaseConnection::class, AcmeDatabaseSchema::class)
    # ! Database example
    # ---------------------
    ->endRest();

# =====================
# Define Swagger/OpenAPI
# =====================
$manifest->openAPI()
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
                ->endResponseContent()
            ->endResponse()
        ->end()
    ->end();
// @formatter:on

return $manifest;
