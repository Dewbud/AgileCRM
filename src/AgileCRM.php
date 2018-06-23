<?php

namespace Dewbud\AgileCRM;

use Dewbud\AgileCRM\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class AgileCRM
{

    /**
     * AgileCRM domain
     * @var string
     */
    public $domain = '';

    /**
     * User email
     * @var string
     */
    public $user_email = '';

    /**
     * REST API key
     * @var string
     */
    public $api_key = '';

    const CLIENT_NAME    = 'PHP AGILECRM';
    const CLIENT_VERSION = '0.1.0';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * AgileCRM Client
     * @param string $domain  AgileCRM domain
     * @param string $user    User email
     * @param string $key     REST API key
     */
    public function __construct($domain, $user, $key)
    {
        $this->domain     = $domain;
        $this->user_email = $user;
        $this->api_key    = $key;

        $this->http = new Client(['base_uri' => "https://{$this->domain}.agilecrm.com/dev/api/"]);
    }

    /**
     * Get all contacts
     *
     * @return \Dewbud\AgileCRM\Response
     */
    public function contacts()
    {
        $res = $this->send('GET', 'contacts', []);

        return new Response($this->parseResponse($res));
    }

    /**
     * Create a contact
     * @param array $contact
     * @param array $tags
     * @return \Dewbud\AgileCRM\Response
     */
    public function newContact($contact, $tags = [])
    {
        $data = [
            'tags'       => $tags,
            'properties' => agilecrm_map_types($contact),
        ];

        $res = $this->send('POST', 'contacts', $data);

        return new Response($this->parseResponse($res));
    }

    /**
     * Create a company
     * @param array $company
     * @param array $tags
     * @return \Dewbud\AgileCRM\Response
     */
    public function newCompany($company, $tags = [])
    {
        $data = [
            'type'       => 'COMPANY',
            'tags'       => $tags,
            'properties' => agilecrm_map_types($company),
        ];

        $res = $this->send('POST', 'contacts', $data);

        return new Response($this->parseResponse($res));
    }

    /**
     * Edit an existing contact/company
     *
     * @param string $id
     * @param array $properties
     * @return \Dewbud\AgileCRM\Response
     */
    public function editContact($id, $properties)
    {

        $data = [
            'id'         => $id,
            'properties' => agilecrm_map_types($properties),
        ];

        $res = $this->send('PUT', 'contacts/edit-properties', $data);

        return new Response($this->parseResponse($res));
    }

    /**
     * Create a new task for a contact
     * @param string $contact_email
     * @param array $task
     * @return \Dewbud\AgileCRM\Response
     */
    public function newTask($contact_email, $task)
    {
        $res = $this->send('POST', "tasks/email/{$contact_email}", $task);

        $res = $this->parseResponse($res);

        return new Response($res);
    }

    /**
     * @param \GuzzleHttp\Psr7\Response $res
     * @return array
     */
    protected function parseResponse(GuzzleResponse $res)
    {
        return \json_decode($res->getBody(), true);
    }

    /**
     * Send request via Guzzle
     *
     * @param string $verb      HTTP verb
     * @param string $resource  API resource
     * @param array $request    Request Data
     * @param array $options    Guzzle Options
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    protected function send($verb, $resource, $request = [], $options = [])
    {
        $default_options = [
            'allow_redirects' => true,
            'auth'            => [$this->user_email, $this->api_key],
            'headers'         => [
                'User-Agent' => self::CLIENT_NAME . ' v' . self::CLIENT_VERSION,
                'Accept'     => 'application/json',
            ],
            'verify'          => false, // self signed certs
        ];

        $options = array_merge($default_options, $options);

        if (in_array($verb, ['POST', 'PUT', 'PATCH'])) {
            $options['json'] = $request;
        }

        try {
            // Send request
            $res = $this->http->request($verb, $resource, $options);
        } catch (ClientException $e) {
            throw $e;
        }

        return $res;
    }
}
