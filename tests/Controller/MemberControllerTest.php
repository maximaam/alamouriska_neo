<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MemberControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $this->markTestSkipped('must be revisited.');
        
        $client = static::createClient();
        $client->request('GET', '/member');

        self::assertResponseIsSuccessful();
    }
}
