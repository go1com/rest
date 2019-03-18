<?php

namespace go1\rest;

use Firebase\JWT\JWT;
use JsonException;

class Request extends \Slim\Http\Request
{
    const ROLE_SYSTEM        = 'Admin on #Accounts';
    const ROLE_ADMIN         = 'administrator';
    const ROLE_ADMIN_CONTENT = 'content administrator';
    const ROLE_MANAGER       = 'manager';

    private $contextUser;

    public function json(bool $assoc = true, int $depth = 512)
    {
        $body = $this->getBody();
        $body->rewind();
        $data = json_decode($body->getContents(), $assoc, $depth, JSON_THROW_ON_ERROR);

        // support php <= 7.2
        if (0 !== json_last_error()) {
            throw new JsonException(json_last_error_msg());
        }

        return $data;
    }

    private function jwtPayload()
    {
        $auth = $this->getHeaderLine('Authorization');
        if ($auth && (0 === strpos($auth, 'Bearer '))) {
            $jwt = substr($auth, 7);
        }

        $jwt = $jwt ?? $this->getQueryParam('jwt') ?? $this->getCookieParam('jwt');
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

    public function contextAccount(string $portalName)
    {
        if (!$user = $this->contextUser()) {
            return null;
        }

        $accounts = isset($user->accounts) ? $user->accounts : [];
        foreach ($accounts as $account) {
            if ($portalName == $account->instance) {
                return $account;
            }
        }

        return null;
    }

    public function isSystemUser(): bool
    {
        if (!$contextUser = $this->contextUser()) {
            return false;
        }

        return in_array(self::ROLE_SYSTEM, isset($contextUser->roles) ? $contextUser->roles : []);
    }

    public function isPortalAdmin($portalIdOrName, bool $inherit = true): bool
    {
        return $this->roleCheck($portalIdOrName, self::ROLE_ADMIN, $inherit);
    }

    public function isPortalContentAdministrator($portalIdOrName, bool $inherit = true)
    {
        return $this->roleCheck($portalIdOrName, self::ROLE_ADMIN_CONTENT, $inherit);
    }

    public function isPortalManager($portalIdOrName, bool $inherit = true)
    {
        return $this->roleCheck($portalIdOrName, self::ROLE_MANAGER, $inherit);
    }

    private function roleCheck($portalIdOrName, $role = self::ROLE_ADMIN, bool $inherit = true)
    {
        if (!$contextUser = $this->contextUser()) {
            return false;
        }

        if ($inherit) {
            if ($this->isSystemUser()) {
                return true;
            }
        }

        $accounts = isset($contextUser->accounts) ? $contextUser->accounts : [];
        foreach ($accounts as &$account) {
            $actual = is_numeric($portalIdOrName) ? $account->portal_id : $account->instance;
            if ($portalIdOrName === $actual) {
                if (!empty($account->roles) && in_array($role, $account->roles)) {
                    return true;
                }
            }
        }

        return false;
    }
}
