<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @auth Octavio Cornejo
     * @date 2021-04-01
     * @var string
     */
    public $accessToken;

    /**
     * @auth Octavio Cornejo
     * @date 2021-04-01
     * @const array
     */
    const DATA = [
        'name' => 'usuario 1',
        'email' => 'test@mail.com',
        'password' => 'secret',
        'password_confirmation' => 'secret',
    ];

    /**
     * run tests
     * @auth Octavio Cornejo
     * @date 2021-04-01
     *
     * @return void
     */
    public function test_authentication()
    {
        $this->register();
        $this->login();
        $this->usersList();
        $this->getUserProfile();
        $this->refresh();
        $this->logout();

    }

    /**
     * test user registration.
     * @auth Octavio Cornejo
     * @date 2021-04-01
     *
     * @return void
     */
    public function register()
    {
        dump('register...');
        $response = $this->postJson('/api/auth/register', self::DATA);

        $this->assertDatabaseHas('users', [
            'email' => 'test@mail.com'
        ]);

        $response
            ->assertJsonPath('user.email', self::DATA['email'])
            ->assertCreated();
    }

    /**
     * test if a user registered can get logged.
     * @auth Octavio Cornejo
     * @date 2021-04-01
     *
     * @return void
     */
    public function login()
    {
        dump('login...');
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@mail.com',
            'password' => 'secret',
        ]);

        $response
            ->assertJsonPath('user.email', self::DATA['email'])
            ->assertOk();
        $this->accessToken = $response->decodeResponseJson()['access_token'];
    }

    /**
     * test user attempt to logout.
     * @auth Octavio Cornejo
     * @date 2021-04-01
     *
     * @return void
     */
    public function logout()
    {
        dump('logout...');
        $response = $this->withHeader('Authorization', $this->accessToken)
            ->postJson('/api/auth/logout');

        $response->assertJsonPath('message', 'User successfully signed out')
            ->assertOk();
    }

    /**
     * test user profile information.
     * @auth Octavio Cornejo
     * @date 2021-04-01
     *
     * @return void
     */
    public function getUserProfile()
    {
        dump('getUserProfile...');
        $response = $this->withHeader('Authorization', $this->accessToken)
            ->getJson('/api/auth/user-profile');

        $response
            ->assertJsonPath('email', self::DATA['email'])
            ->assertOk();
    }

    /**
     * test if token can be refreshed.
     * @auth Octavio Cornejo
     * @date 2021-04-01
     *
     * @return void
     */
    public function refresh()
    {
        dump('refresh...');
        $response = $this->withHeader('Authorization', $this->accessToken)
            ->postJson('/api/auth/refresh');

        $response
            ->assertDontSee($this->accessToken)
            ->assertJsonPath('user.email', self::DATA['email'])
            ->assertOk();
        $this->accessToken = $response->decodeResponseJson()['access_token'];
    }

    /**
     * test users list.
     * @auth Octavio Cornejo
     * @date 2021-04-01
     *
     * @return void
     */
    public function usersList()
    {
        dump('usersList...');
        $response = $this->withHeader('Authorization', $this->accessToken)
            ->getJson('/api/users');

        $response
            ->assertJsonCount(1)
            ->assertOk();
    }
}
