<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        self::markTestSkipped('must be revisited.');

        $client = static::createClient();
        $client->request('GET', '/member');

        self::assertResponseIsSuccessful();
    }
}
