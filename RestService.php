<?php

namespace go1\rest;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Headers;

class RestService extends \DI\Bridge\Slim\App
{
    const VERSION = 'v1.0';

    private $cnf;

    /**
     * @var Stream
     */
    private $stream;

    public function __construct(array $cnf = [])
    {
        $this->cnf = $cnf;
        $this->cnf += [
            'request'  => function (ContainerInterface $c) {
                return Request::createFromEnvironment($c->get('environment'));
            },
            'response' => function (ContainerInterface $c) {
                $response = new Response(200, new Headers(['Content-Type' => 'text/html; charset=UTF-8']));

                return $response->withProtocolVersion($c->get('settings')['httpVersion']);
            },
        ];

        parent::__construct();

        if (!empty($cnf['boot'])) {
            call_user_func($cnf['boot'], $this);
        }

        $this->get('/', function (Response $response) {
            return $response->withJson([
                'service' => defined('SERVICE_NAME') ? SERVICE_NAME : 'rest',
                'version' => defined('SERVICE_VERSION') ? SERVICE_VERSION : self::VERSION,
                'time'    => time(),
            ]);
        });

        $stream = $this->stream();
        $this->get('/consume', function (Response $response) use ($stream) {
            $listeners = [];
            foreach ($stream->listeners() as $name => $listener) {
                $listeners[$name] = $listener['description'];
            }

            return $response->withJson($listeners);
        });

        $this->post('/consume', function(Request $request, Response $response) use ($stream) {
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

                $stream->commit($routingKey, json_encode($body), $context);

                return $response->withJson(null, 204);
            } catch (\JsonException $e) {
                return $response->jr('Invalid payload');
            } catch (\Exception $e) {
                return $response->jr500('Failed to commit stream: ' . $e->getMessage());
            }
        });
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @return Response
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response)
    {
        return parent::process($request, $response);
    }

    protected function configureContainer(ContainerBuilder $builder)
    {
        if (empty($this->cnf)) {
            return null;
        }

        $builder->addDefinitions($this->cnf);
        $this->cnf = [];
    }

    public function stream(): Stream
    {
        if (!$this->stream) {
            $this->stream = new Stream;
        }

        return $this->stream;
    }
}
