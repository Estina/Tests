<?php
namespace Estina\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    protected $client;
    protected $router;

    /**
     * Prepare each test
     */
    public function setUp()
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');

    }

    /**
     * Login client
     *
     * @param string $useName
     * @param string $password
     */
    protected function loginClient($useName, $password)
    {
        $crawler = $this->client->request('GET', '/login');

        $button = $crawler->selectButton('_login');
        $this->assertGreaterThan(0, $button->count());
        $form = $button->form(array(
            '_username'  => $useName,
            '_password'  => $password,
        ));

        $this->client->submit($form);
        $this->client->followRedirect();
    }
}
