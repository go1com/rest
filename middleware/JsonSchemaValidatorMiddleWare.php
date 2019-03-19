<?php

namespace go1\rest\middleware;

use go1\rest\Request;
use go1\rest\Response;
use JsonSchema\Validator;

class JsonSchemaValidatorMiddleWare
{
    private $validator;
    private $mapper;
    private $className;
    private $jsonSchemaPath;

    public function __construct(Validator $validator, JsonMapper $mapper, string $className, string $jsonSchemaPath)
    {
        $this->validator = $validator;
        $this->mapper = $mapper;
        $this->className = $className;
        $this->jsonSchemaPath = $jsonSchemaPath;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $json = $request->json(false);
        $this->validator->reset();
        $this
            ->validator
            ->validate($json, ['$ref' => $this->jsonSchemaPath]);

        if (!$this->validator->isValid()) {
            return $response->jr(sprintf('Invalid payload %s',  json_encode($this->validator->getErrors(), JSON_PRETTY_PRINT)));
        }

        $object = new $this->className;
        $object = $this->mapper->map($json, $object);
        $request = $request->withAttribute($this->className, $object);

        return $next($request, $response);
    }
}
