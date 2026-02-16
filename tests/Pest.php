<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

use App\Models\Casilla;
use App\Models\AceptacionTerminos;
use App\Models\VersionTerminos;

/**
 * Create a casilla account and authenticate it with Sanctum.
 *
 * @return array{account: Casilla, token: string}
 */
function createAuthenticatedAccount(array $attributes = []): array
{
    $account = Casilla::factory()->create($attributes);
    $token = $account->createToken('test-token')->plainTextToken;

    return ['account' => $account, 'token' => $token];
}

/**
 * Create active terms and accept them for the given account.
 */
function acceptTermsForAccount(Casilla $account): VersionTerminos
{
    $terms = VersionTerminos::factory()->activa()->create();

    AceptacionTerminos::factory()->create([
        'version_terminos_id' => $terms->id,
        'usuario_auth_id' => $account->usuario_auth_id,
    ]);

    return $terms;
}
