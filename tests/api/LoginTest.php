<?php

namespace App\Tests\api;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Util\HttpCode;
use Exception;
use Faker\Factory;

class LoginTest {

    private string $validEmail;
    private string $validPassword;

    public function _before(ApiTester $I) {
        $faker = Factory::create();
        $this->validEmail = $faker->email();
        $this->validPassword = $faker->password();
        $hasher = $I->grabService('security.user_password_hasher');

        $I->haveInRepository(
            User::class,
            [
                'firstName' => $faker->firstName(),
                'lastName'  => $faker->lastName(),
                'email'     => $this->validEmail,
                'password'  => ''
            ]
        );

        $user = $I->grabEntityFromRepository(
            User::class,
            [
                'email' => $this->validEmail
            ]
        );

        $user->setPassword($hasher->hashPassword($user, $this->validPassword));
    }

    public function loginSuccessfully(ApiTester $I) {
        $I->sendPost(
            '/login',
            [
                'emailAddress' => $this->validEmail,
                'password' => $this->validPassword
            ]
        );

        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseMatchesJsonType(
            [
                'token' => 'string:!empty'
            ]
        );
    }

    public function verifyReturnedApiToken(ApiTester $I) {
        $I->sendPost(
            '/login',
            [
                'emailAddress' => $this->validEmail,
                'password'     => $this->validPassword
            ]
        );

        $token = $I->grabDataFromResponseByJsonPath('token')[0];

        $I->seeInRepository(
            User::class,
            [
                'email'    => $this->validEmail,
                'apiToken' => $token
            ]
        );
    }

    public function loginWithInvalidPasswordAndFail(ApiTester $I) {
        $I->sendPost(
            '/login',
            [
                'emailAddress' => $this->validEmail,
                'password'     => 'ThisPasswordIsInvalid...'
            ]
        );

        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }
}
