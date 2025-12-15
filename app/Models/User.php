<?php

namespace App\Models;

use App\Services\SupabaseService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

class User extends SupabaseModel implements Authenticatable
{
    protected static string $table = 'users';

    protected array $hidden = ['password'];

    /**
     * Find user by email.
     */
    public static function findByEmail(string $email): ?static
    {
        $data = static::getService()->findBy(static::$table, 'email', $email);

        if ($data) {
            $model = new static($data);
            $model->exists = true;
            return $model;
        }

        return null;
    }

    /**
     * Verify password.
     */
    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Get the user info record.
     */
    public function getUserInfo(): ?UserInfo
    {
        return UserInfo::where('user_id', $this->id)->first()
            ? UserInfo::hydrate([UserInfo::where('user_id', $this->id)->first()])[0]
            : null;
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is moderator.
     */
    public function isModerator(): bool
    {
        return $this->role === 'mod' || $this->role === 'admin';
    }

    /**
     * Check if user account is disabled.
     */
    public function isDisabled(): bool
    {
        return (bool) $this->is_disabled;
    }

    // Authenticatable interface implementation

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword(): string
    {
        return $this->password ?? '';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return $this->remember_token;
    }

    public function setRememberToken($value): void
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    /**
     * Override toArray to hide sensitive fields.
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        foreach ($this->hidden as $key) {
            unset($array[$key]);
        }

        return $array;
    }
}
