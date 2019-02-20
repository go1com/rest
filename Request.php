<?php

namespace go1\rest;

class Request extends \Slim\Http\Request
{
    const ROLE_SYSTEM        = 'Admin on #Accounts';
    const ROLE_ADMIN         = 'administrator';
    const ROLE_ADMIN_CONTENT = 'content administrator';
    const ROLE_MANAGER       = 'manager';

    private $contextUser;

    public function contextUser()
    {
        if (!$this->attributes->has('jwt.payload')) {
            return null;
        }

        if (is_null($this->contextUser)) {
            $payload = $this->attributes->get('jwt.payload');
            if (!empty($payload->object->type)) {
                if ('user' === $payload->object->type) {
                    $this->contextUser = !empty($payload->object->content->mail) ? $payload->object->content : null;
                }
            }
        }

        return $this->contextUser;
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

    public function isContentAdministrator($portalIdOrName, bool $inherit = true)
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
