<?php

namespace go1\rest\errors;

use Exception;
use go1\rest\Request;
use go1\rest\Response;
use go1\rest\tests\RestTestCase;
use Psr\Log\LoggerInterface;
use function class_exists;
use function sprintf;

class RestErrorHandler
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Response $response, Exception $e)
    {
        $this->logger->error(
            $e->getMessage(), [
                'errorCode'  => (!$e instanceof RestError) ? $e->getCode() : $e->errorCode(),
                'httpStatus' => (!$e instanceof RestError) ? 500 : $e->httpErrorCode(),
                'request'    => sprintf('%s %s', $request->getMethod(), $request->getUri()->__toString()),
                'trace'      => $e->getTrace(),
            ]
        );

        if (!$request->hasHeader('Accept')) {
            if ($request->hasHeader('Content-Type')) {
                $request = $request->withHeader('Accept', $request->getHeader('Content-Type'));
            }
        }

        if ($e instanceof RestError) {
            return $this->handleRestError($request, $response, $e);
        }

        return $this->handle($request, $response, $e);
    }

    private function handleRestError(Request $request, Response $response, RestError $e)
    {
        # ref: https://jsonapi.org/examples/#error-objects-basics

        return $response->withJson(
            [
                'errors' => [
                    [
                        'status' => $e->httpErrorCode(),
                        'code'   => $e->errorCode(),
                        'title'  => $e->getMessage(),
                        'detail' => sprintf(
                            '%s %s',
                            $request->getMethod(),
                            $request->getUri()->getPath()
                        ),
                    ],
                ],
            ],
            $e->httpErrorCode()
        );
    }

    private function handle(Request $request, Response $response, Exception $e)
    {
        if (class_exists(RestTestCase::class, false)) {
            return $response->withJson(
                [
                    'method'  => $request->getMethod(),
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ],
                500
            );
        }

        throw $e;
    }
}
