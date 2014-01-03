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

        $this->client = static::createClient(array(
            'environment' => (isset($_ENV['SYMFONY_ENV']))?$_ENV['SYMFONY_ENV']:'test',
        ));
        $this->router = $this->client->getContainer()->get('router');

        $this->loginClient('test@estina.lt', 'testuser');
    }

    /**
     * Login client
     *
     * @param string $useName
     * @param string $password
     */
    protected function loginClient($useName, $password, $admin = true)
    {
        $url = '/login';
        if ($admin) {
            $url = '/admin/login';
        }

        $crawler = $this->client->request('GET', $url);

        $button = $crawler->selectButton('_login');
        $this->assertGreaterThan(0, $button->count());
        $form = $button->form(array(
            '_username'  => $useName,
            '_password'  => $password,
        ));

        $this->client->submit($form);
        $this->client->followRedirect();
    }

    /**
     * Run basic crud tests for given routes
     *
     * @param string $routeIndex
     * @param string $routeData
     * @param string $routeNew
     * @param string $routeEdit
     * @param string $routeDelete
     * @param string $nameField
     * @param array $data data to test create action
     * @param array $updateData data to test update action
     * @return void
     */
    protected function runBasicCrudTest($routeIndex,
        $routeData,
        $routeNew,
        $routeEdit,
        $routeDelete,
        $nameField,
        $data,
        $updateData)
    {
        // check if listing works
        $crawler = $this->client->request('GET', $this->router->generate($routeIndex));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET index");

        // Create a new entry in the database
        $crawler = $this->client->request('GET', $this->router->generate($routeNew));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET new");

        // Fill in the form and submit it
        $form = $crawler->selectButton('Save')->form($data);

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        // Check data in the listing
        $entry = $this->getDataEntryByName($this->getJsonData($this->router->generate($routeData)), $data[$nameField]);
        $this->assertNotNull($entry, 'Entry not found in listing');

        // Edit the entity
        $crawler = $this->client->request('GET', $this->router->generate($routeEdit, array('id' => $entry['id'])));

        $form = $crawler->selectButton('Save')->form($updateData);

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $entry = $this->getDataEntryByName($this->getJsonData($this->router->generate($routeData)), $updateData[$nameField]);
        $this->assertNotNull($entry, 'Entry not found in updated listing');

        // Delete the entity
        $crawler = $this->client->request('GET', $this->router->generate($routeDelete, array('id' => $entry['id'])));
        $crawler = $this->client->followRedirect();

        // Check the entity has been delete on the list
        $entry = $this->getDataEntryByName($this->getJsonData($this->router->generate($routeData)), $updateData[$nameField]);
        $this->assertNull($entry);
    }

    /**
     * For testing /getdata/ method
     * @param $data json data, response from /getdata method
     * @param $nameGiven name to look for
     * @return mixed array if found, null otherwise
     */
    protected function getDataEntryByName($data, $nameGiven)
    {
        $id = null;
        $name = null;

        foreach ($data->aaData as $item) {
            foreach ($item as $k => $val) {
                if ($val == $nameGiven) {
                    $id = $item[0];
                    $name = $val;
                    break;
                }
            }
        }

        if ($id) {
            return array(
                'id' => $id,
                'name' => $name,
            );
        }
    }

    /**
     * Get json data from url
     * @param string $url url to send request to
     * @return object json decoded object
     */
    protected function getJsonData($url)
    {
        $crawler = $this->client->request('GET', $url);
        $response = $this->client->getResponse()->getContent();
        return json_decode($response);
    }
}
