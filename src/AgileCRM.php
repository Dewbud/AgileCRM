<?php

namespace Dewbud\AgileCRM;

use Dewbud\AgileCRM\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

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
    public function __construct(string $domain, string $user, string $key)
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
        return new Response($this->send('GET', 'contacts', []));
    }

    /**
     * Create a contact
     * @param array $contact
     * @param array $tags
     * @return \Dewbud\AgileCRM\Response
     */
    public function newContact(array $contact, array $tags = [])
    {
        $data = [
            'tags'       => $tags,
            'properties' => agilecrm_map_types($contact),
        ];

        return new Response($this->send('POST', 'contacts', $data));
    }

    /**
     * Create a deal
     * @param array $deal
     * @return \Dewbud\AgileCRM\Response
     */
    public function newDeal(array $deal)
    {
        return new Response($this->send('POST', 'opportunity', $deal));
    }

    /**
     * Update a deal
     * @param string $id
     * @param array $deal
     * @return \Dewbud\AgileCRM\Response
     */
    public function editDeal(string $id, array $deal)
    {
        $data = array_merge($deal, ['id' => $id]);
        return new Response($this->send('PUT', 'opportunity/partial-update', $data));
    }

    /**
     * Search for a contact by email
     * @param string $email
     * @return \Dewbud\AgileCRM\Response|null
     */
    public function searchContact(string $email)
    {
        $res = $this->send('GET', "contacts/search/email/{$email}");

        if ($res->getStatusCode() == 204) {
            return null;
        }

        return new Response($res);
    }

    /**
     * Create a company
     * @param array $company
     * @param array $tags
     * @return \Dewbud\AgileCRM\Response
     */
    public function newCompany(array $company, array $tags = [])
    {
        $data = [
            'type'       => 'COMPANY',
            'tags'       => $tags,
            'properties' => agilecrm_map_types($company),
        ];

        return new Response($this->send('POST', 'contacts', $data));
    }

    /**
     * Edit an existing contact/company
     *
     * @param string $id
     * @param array $properties
     * @return \Dewbud\AgileCRM\Response
     */
    public function editContact(string $id, array $properties)
    {

        $data = [
            'id'         => $id,
            'properties' => agilecrm_map_types($properties),
        ];

        return new Response($this->send('PUT', 'contacts/edit-properties', $data));
    }

    /**
     * Create a new task for a contact
     * @param string $contact_email
     * @param array $task
     * @return \Dewbud\AgileCRM\Response
     */
    public function newTask(string $contact_email, array $task)
    {
        return new Response($this->send('POST', "tasks/email/{$contact_email}", $task));
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
    protected function send(string $verb, string $resource, array $request = [], array $options = [])
    {
        $default_options = [
            'allow_redirects' => true,
            'auth'            => [$this->user_email, $this->api_key],
            'headers'         => [
                'User-Agent'   => self::CLIENT_NAME . ' v' . self::CLIENT_VERSION,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
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
