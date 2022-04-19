<?php

namespace App\Tests\api;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Util\HttpCode;
use Faker\Factory;
use Faker\Generator;

class RegistrationTest {

    private Generator $faker;

    public function _before(ApiTester $I) {
        $this->faker = Factory::create();
    }

    public function registerSuccessfully(ApiTester $I) {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $emailAddress = $this->faker->email();
        $I->sendPost(
            '/register',
            [
                'firstName'    => $firstName,
                'lastName'     => $lastName,
                'emailAddress' => $emailAddress,
                'password'     => $this->faker->password()
            ]
        );

        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseIsJson();
        $I->seeInRepository(
            User::class,
            [
                'firstName' => $firstName,
                'lastName'  => $lastName,
                'email'     => $emailAddress
            ]
        );
    }

    public function registerWithoutEmailAddressAndFail(ApiTester $I) {
        $I->sendPost(
            '/register',
            [
                'firstName' => $this->faker->firstName(),
                'lastName'  => $this->faker->lastName(),
                'password'  => $this->faker->password()
            ]
        );
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseIsJson();
    }
}
