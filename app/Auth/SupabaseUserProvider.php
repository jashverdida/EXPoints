<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class SupabaseUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return User::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = User::find($identifier);

        if (!$user) {
            return null;
        }

        $rememberToken = $user->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token) ? $user : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        // Remove password from credentials for lookup
        $lookupCredentials = array_filter($credentials, function ($key) {
            return $key !== 'password';
        }, ARRAY_FILTER_USE_KEY);

        if (empty($lookupCredentials)) {
            return null;
        }

        // Find user by email
        if (isset($lookupCredentials['email'])) {
            return User::findByEmail($lookupCredentials['email']);
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (!isset($credentials['password'])) {
            return false;
        }

        return Hash::check($credentials['password'], $user->getAuthPassword());
    }

    /**
     * Rehash the user's password if required and supported.
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        if (!Hash::needsRehash($user->getAuthPassword()) && !$force) {
            return;
        }

        $user->setAttribute('password', Hash::make($credentials['password']));
        $user->save();
    }
}
