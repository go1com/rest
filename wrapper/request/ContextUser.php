<?php

namespace go1\rest\wrapper\request;

use go1\rest\util\Model;
use function in_array;
use function is_numeric;

/**
 * @property int                                        $id
 * @property string                                     $accountsName
 * @property int                                        $profileId
 * @property string                                     $mail
 * @property string                                     $name
 * @property string[]                                   $roles
 * @property \go1\rest\wrapper\request\ContextAccount[] $accounts
 */
class ContextUser extends Model
{
    const ROLE_SYSTEM = 'Admin on #Accounts';

    protected $id;

    /**
     * @json instance
     */
    protected $accountsName;

    /**
     * @json profile_id
     */
    protected $profileId;
    protected $mail;
    protected $name;
    protected $roles = [];

    /**
     * @var \go1\rest\wrapper\request\ContextAccount[]
     */
    protected $accounts = [];

    /**
     * @param int|string $portalIdOrName
     * @return ContextAccount
     */
    public function account($portalIdOrName): ContextAccount
    {
        foreach ($this->accounts as $account) {
            $actual = is_numeric($portalIdOrName) ? $account->portalId : $account->portalName;
            if ($portalIdOrName === $actual) {
                return $account;
            }
        }

        return new ContextAccount;
    }

    public function isSystemUser(): bool
    {
        return in_array(static::ROLE_SYSTEM, isset($this->roles) ? $this->roles : []);
    }
}
