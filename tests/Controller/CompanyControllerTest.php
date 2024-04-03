<?php

namespace App\Tests\Controller;

use App\Controller\CompanyController;
use App\Entity\Company;
use App\Repository\CompanyRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompanyControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/companies');
        $responseContent = $client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();
    }
}
