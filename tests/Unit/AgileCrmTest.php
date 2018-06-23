<?php

use Dewbud\AgileCRM\AgileCRM;
use function GuzzleHttp\json_encode;
use PHPUnit\Framework\TestCase;

class AgileCrmTest extends TestCase
{
    const DOMAIN = '';
    const USER   = '';
    const KEY    = '';

    /**
     * @var \Dewbud\AgileCRM\AgileCRM
     */
    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = new AgileCRM(self::DOMAIN, self::USER, self::KEY);
    }

    public function tearDown()
    {
        unset($this->client);
        parent::tearDown();
    }

    public function log($text)
    {
        fwrite(STDERR, $text . "\n");
    }

    /**
     * Tests
     */

    /** @test */
    public function creates_companies()
    {
        $array = [
            'name' => 'Test Company',
        ];

        $tags = ['test'];

        $response = $this->client->newCompany($array, $tags);
    }

    /** @test */
    public function creates_contacts()
    {
        $array = [
            'name'    => 'Test Contact',
            'phone'   => '9541231234',
            'email'   => 'test-contact@example.com',
            'company' => 'Test Company',
            'title'   => 'Owner',
        ];

        $tags = ['test'];

        $this->assertTrue(false, json_encode($this->client->newContact($array, $tags), JSON_PRETTY_PRINT));
    }

    /** @test */
    public function creates_tasks_for_contacts()
    {
        $array = [
            'type'          => 'FOLLOW_UP',
            'priority_type' => 'HIGH',
            'due'           => \time() + (1 * 24 * 60 * 60), // 24 hours from now
            'is_complete'   => false,
            'subject'       => 'Follow up',
            'status'        => 'YET_TO_START',
            'owner_id'      => '',
        ];

        $tags = ['test'];

        $this->assertTrue(false, json_encode($this->client->newTask('test-contact@example.com', $array), JSON_PRETTY_PRINT));
    }
}
