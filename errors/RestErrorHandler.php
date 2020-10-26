<?php

namespace go1\rest\errors;

use Exception;
use go1\rest\Request;
use go1\rest\Response;
use go1\rest\tests\RestTestCase;
use Psr\Log\LoggerInterface;
use Throwable;
use function class_exists;
use function getenv;
use function sprintf;

class RestErrorHandler
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request             $request
     * @param Response            $response
     * @param Exception|Throwable $e
     * @return Response
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, $e)
    {
        $debugging = getenv('REST_DEBUGGING') ?? false;
        if ($debugging) {
            throw $e;
        }

        $this->logger->error(
            $e->getMessage(), [
                'errorCode'  => (!$e instanceof RestError) ? $e->getCode() : $e->errorCode(),
                'httpStatus' => (!$e instanceof RestError) ? 500 : $e->httpErrorCode(),
                'request'    => sprintf('%s %s', $request->getMethod(), $request->getUri()->__toString()),
                'trace'      => $e->getTrace(),
                'exception'  => $e,
            ]
        );

        if (!$request->hasHeader('Accept')) {
            if ($request->hasHeader('Content-Type')) {
                $request = $request->withHeader('Accept', $request->getHeader('Content-Type'));
            }
        }

        if ($e instanceof Exception) {
            if ($e instanceof RestError) {
                return $this->handleRestError($request, $response, $e);
            }

            return $this->handleException($request, $response, $e);
        }

        return $this->handleError($request, $response, $e);
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

    private function handleException(Request $request, Response $response, Exception $e)
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

    private function handleError(Request $request, Response $response, Throwable $e)
    {
        if (class_exists(RestTestCase::class, false)) {
            return $response->withJson(
                [
                    'method'  => $request->getMethod(),
                    'code'    => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ],
                400
            );
        }

        throw $e;
    }
}
