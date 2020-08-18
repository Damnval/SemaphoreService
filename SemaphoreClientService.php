<?php

namespace App\Services;

use GuzzleHttp\Client;

class SemaphoreClientService
{
	public $apiBase = 'https://api.semaphore.co/api/v4/';

    public $apikey;
    public $senderName = null;
    protected $client;

	/**
	 * Initialize SemaPhore Client class
	 *
	 * @param string $sender_name. 
	 */
    public function __construct($sender_name = null)
    {
		$this->apikey = env("SEMAPHORE_API_KEY", "");

		$this->sender_name =  env("SENDER_NAME", "Gemango");

		// will create a validation if $sender_name exists
		if($sender_name){
			$this->sender_name = $this->senderName;
		}

        $this->client = new Client( ['base_uri' => $this->apiBase, 'query' => [ 'apikey' => $this->apikey ] ] );
    }

    /**
     * Check the balance of your account
     *
	 * @return Object $response
     */
    public function balance()
    {
        $response = $this->client->get('account');
        return $response->getBody();
	}
	
 	/**
     * Sends a message using Semaphore
     *
	 * @param mixed $recipient. array if multiple recepient. otherwise, string
	 * @param String $message
	 * @return Object $response
     */
    public function send($recipient, $message)
    {
        // use comma if multiple numbers $recipient='x,x,x';
		$recipients = explode( ',', $recipient );

    	if( count( $recipients ) > 1000 )
	    {
	    	throw new \Exception( 'API is limited to sending to 1000 recipients at a time' );
		}
		
		$recipient = $this->cleanContactNumber($recipients);
		
		$recipient = implode(',',$recipient);
		
        $params = [
			'form_params' => [
				'apikey' =>  $this->apikey,
				'message' => $message,
				'number' => $recipient,
				'sendername' => $this->sender_name
				]
			];

        $response = $this->client->post('messages', $params );

        return $response->getBody();
	}
	
	/**
	 * appending 0 in all contact numbers for ph numbers. e.g. 09187232457 
	 * instead of 6397232457 or 927232457
	 * 
	 * @param Array $recipients
	 * @return Array $recipients
	 */
	public function cleanContactNumber($recipients)
	{
		return array_map(function($number){
			$cleansed = substr($number, -10);
			return '0'.$cleansed;
		}, $recipients);
	}

	/**
	 * Get account details
	 *
	 * @return $response
	 */
	public function account()
	{
		$response = $this->client->get( 'account' );
		return $response->getBody();
	}

	/**
	 * Get users associated with the account
	 *
	 * @return $response
	 */
	public function users()
	{
		$response = $this->client->get( 'account/users' );
		return $response->getBody();

	}

	/**
	 * Get sender names associated with the account
	 *
	 * @return $response
	 */
	public function sendernames()
	{
		$response = $this->client->get( 'account/sendernames' );
		return $response->getBody();
	}

	/**
	 * Get transactions associated with the account
	 *
	 * @return $response
	 */
	public function transactions()
	{
		$response = $this->client->get( 'account/transactions' );
		return $response->getBody();
	}

}