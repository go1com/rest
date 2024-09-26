<?php

namespace go1\rest;

use Firebase\JWT\JWT;
use go1\rest\wrapper\request\RequestBag;
use JsonException;
use RuntimeException;
use Slim\Psr7\Request as SlimRequest;
use stdClass;
use function in_array;
use function is_numeric;

/**
 * @property RequestBag $request
 */
class Request extends SlimRequest
{
    const ROLE_SYSTEM        = 'Admin on #Accounts';
    const ROLE_ADMIN         = 'administrator';
    const ROLE_ADMIN_CONTENT = 'content administrator';
    const ROLE_MANAGER       = 'manager';
    const ROLE_STUDENT       = 'Student';
    const ROLE_ASSESSOR      = 'tutor';
    const ROLE_ALL           = [
        self::ROLE_SYSTEM,
        self::ROLE_ADMIN,
        self::ROLE_ADMIN_CONTENT,
        self::ROLE_MANAGER,
        self::ROLE_STUDENT,
        self::ROLE_ASSESSOR,
    ];

    private $contextUser;
    private $requestBag;

    public function __get($name)
    {
        if ('request' === $name) {
            if (!$this->requestBag) {
                $this->requestBag = new RequestBag($this->json(true));
            }

            return $this->requestBag;
        }

        throw new RuntimeException('Unknown property: ' . $name);
    }

    public function param($name, $defaultValue = null)
    {
        $routeInfo = $this->getAttribute('routeInfo', []);
        $routeInfo = $routeInfo[2] ?? [];

        return $routeInfo[$name] ?? ($defaultValue ?? null);
    }

    public function bodyString(): string
    {
        $body = $this->getBody();
        $body->rewind();

        return $body->getContents();
    }

    public function json(bool $assoc = true, int $depth = 512)
    {
        $body = $this->bodyString();
        if (empty($body)) {
            return [];
        }

        $data = json_decode($this->bodyString(), $assoc, $depth, JSON_THROW_ON_ERROR);

        // support php <= 7.2
        if (0 !== json_last_error()) {
            throw new JsonException(json_last_error_msg());
        }

        return $data;
    }

    public function jwt()
    {
		$auth = $this->getHeaderLine('Authorization');
		if ($auth && strpos($auth, 'Bearer ') === 0) {
			return substr($auth, 7);
		}

		return $this->getQueryParams()['jwt'] ?? $this->getCookieParams()['jwt'] ?? null;
    }

    public function jwtPayload(): ?stdClass
    {
        $jwt = $this->jwt();
        $jwt = is_null($jwt) ? null : ((2 !== substr_count($jwt, '.')) ? null : explode('.', $jwt)[1]);
        $jwt = is_null($jwt) ? null : JWT::jsonDecode(JWT::urlsafeB64Decode($jwt));

        return $jwt ?? null;
    }

    public function contextUser()
    {
        if (is_null($this->contextUser)) {
            if (!$payload = $this->jwtPayload()) {
                return null;
            }

            if (!empty($payload->object->type)) {
                if ('user' === $payload->object->type) {
                    $this->contextUser = !empty($payload->object->content->mail) ? $payload->object->content : null;
                }
            }
        }

        return $this->contextUser ?? null;
    }

    public function contextPortalId(): int
    {
        if ($account = $this->contextAccount()) {
            return $account->portal_id;
        }

        return 0;
    }

    public function contextAccount($portalIdOrName = null)
    {
        if (!$user = $this->contextUser()) {
            return null;
        }

        $accounts = isset($user->accounts) ? $user->accounts : [];

        if ($portalIdOrName) {
            foreach ($accounts as $account) {
                $actual = is_numeric($portalIdOrName) ? $account->portal_id : $account->instance;
                if ($portalIdOrName == $actual) {
                    return $account;
                }
            }

            return null;
        }

        return $accounts[0] ?? null;
    }

    public function isSystemUser(): bool
    {
        if (!$contextUser = $this->contextUser()) {
            return false;
        }

        return in_array(self::ROLE_SYSTEM, isset($contextUser->roles) ? $contextUser->roles : []);
    }

    public function isPortalAdmin($portalIdOrName = null, bool $inherit = true): bool
    {
        return $this->roleCheck($portalIdOrName, self::ROLE_ADMIN, $inherit);
    }

    public function isPortalContentAdministrator($portalIdOrName = null, bool $inherit = true)
    {
        if ($this->roleCheck($portalIdOrName, self::ROLE_ADMIN_CONTENT, $inherit)) {
            return true;
        }

        return $inherit ? $this->isPortalAdmin($portalIdOrName) : false;
    }

    public function isPortalManager($portalIdOrName, bool $inherit = true)
    {
        return $this->roleCheck($portalIdOrName, self::ROLE_MANAGER, $inherit);
    }

    private function roleCheck($portalIdOrName = null, $role = self::ROLE_ADMIN, bool $inherit = true)
    {
        if (!$contextUser = $this->contextUser()) {
            return false;
        }

        if ($inherit) {
            if ($this->isSystemUser()) {
                return true;
            }
        }

        $account = $this->contextAccount($portalIdOrName);
        if ($account) {
            if (!empty($account->roles) && in_array($role, $account->roles)) {
                return true;
            }
        }

        return false;
    }
}
