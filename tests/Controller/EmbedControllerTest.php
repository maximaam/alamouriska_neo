<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class EmbedControllerTest extends WebTestCase
{
    public function testSidebarFragmentRendersSuccessfully(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;

        $request = Request::create('', 'GET');
        $request->attributes->set('_controller', \App\Controller\EmbedController::class.'::sidebar');
        $response = $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('Commentaires récents', (string) $response->getContent());
    }
}
