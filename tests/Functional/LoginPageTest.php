<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginPageTest extends WebTestCase
{
    public function testLoginPageIsReachable(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorTextContains('h1', 'SGEC');
    }

    public function testDashboardRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        $this->assertResponseRedirects('/login');
    }

    public function testProfileRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profil');

        $this->assertResponseRedirects('/login');
    }
}
