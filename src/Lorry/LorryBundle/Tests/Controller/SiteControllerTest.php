<?php

namespace Lorry\LorryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SiteControllerTest extends WebTestCase
{
    public function testFront()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/front');
    }

}
