<?php

namespace go1\rest\controller;

use Exception;
use go1\rest\errors\RuntimeError;
use go1\rest\Request;
use go1\rest\Response;
use go1\rest\Stream;
use JsonException;

class ConsumeController
{
    protected $stream;

    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }

    public function get(Response $response)
    {
        foreach ($this->stream->listeners() as $name => $listener) {
            $listeners[$name] = $listener['description'];
        }

        return $response->withJson($listeners ?? []);
    }

    public function post(Request $request, Response $response)
    {
        try {
            $json = $request->json();
            $routingKey = $json['routingKey'] ?? '';
            $body = $json['body'] ?? null;
            $context = $json['context'] ?? [];
            if (is_scalar($body)) {
                $body = json_decode($body, true);
            }

            if (empty($body) || !is_array($body)) {
                return $response->jr('Invalid or missing payload');
            }

            if (!empty($context) && !is_array($context)) {
                return $response->jr('Invalid context');
            }

            if (empty($routingKey) || !is_string($routingKey)) {
                return $response->jr('Invalid or missing routingKey');
            }

            $this->stream->commit($routingKey, json_encode($body), $context);

            return $response->withJson(null, 204);
        } catch (JsonException $e) {
            return $response->jr('Invalid payload');
        } catch (Exception $e) {
            throw new RuntimeError('failed commit: ' . $e->getMessage());
        }
    }
}
