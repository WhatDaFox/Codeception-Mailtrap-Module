<?php namespace Codeception\Module;

use Codeception\Module;
use Guzzle\Http\Client;

/**
 * This module allows you to test emails using Mailtrap <https://mailtrap.io>.
 * Please try it and leave your feedback.
 *
 * ## Project repository
 *
 * <https://github.com/WhatDaFox/codeception-mailtrap-module>
 *
 * ## Status
 *
 * * Maintainer: **Valentin Prugnaud**
 * * Stability: **dev**
 * * Contact: valentin@whatdafox.com
 *
 * ## Config
 *
 * * client_id: `string`, default `` - Your mailtrap API key.
 * * inbox_id: `string`, default `` - The inbox ID to use for the tests
 * * version: `string`, default `v1` - Version of the API to use, default to v1. (For future use)
 *
 * ## API
 *
 * * client - `\GuzzleHttp\Client` Guzzle client for API requests
 *
 */
class Mailtrap extends Module
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $baseUrl = 'https://mailtrap.io/api/{version}/';

    /**
     * @var array
     */
    protected $config = [ 'client_id', 'inbox_id', 'version' ];

    /**
     * @var array
     */
    protected $requiredFields = [ 'client_id', 'inbox_id' ];

    /**
     * Constructor
     * @param null $config
     */
    public function __construct( $config = NULL )
    {
        $this->config = array_merge(
            [
                'client_id' => '',
                'inbox_id'  => '',
                'version'   => 'v1'
            ],
            (array) $config
        );
        parent::__construct();
    }

    /**
     * Initialize
     * @return void
     */
    public function _initialize()
    {
        $this->client = new Client( [
            'base_url' => [ $this->baseUrl, [ 'version' => $this->config['version'] ] ],
            'defaults' => [
                'headers' => [ 'Api-Token' => $this->config['client_id'] ]
            ]
        ] );
    }

    /**
     * Clean the inbox after each scenario
     * @param TestCase $test
     */
    public function _after( TestCase $test )
    {
        $this->cleanInbox();
    }

    /**
     * Check if the latest email received contains $params
     * @param $params
     * @return mixed
     */
    public function receiveAnEmail( $params )
    {
        $message = $this->fetchLastMessage();
        foreach( $params as $param => $value )
        {
            $this->assertEquals( $value, $message[ $param ] );
        }
    }

    /**
     * Check if the latest email received is from $senderEmail
     * @param $senderEmail
     * @return mixed
     */
    public function receiveAnEmailFromEmail( $senderEmail )
    {
        $message = $this->fetchLastMessage();
        $this->assertEquals( $senderEmail, $message['from_email'] );
    }

    /**
     * Check if the latest email received is from $senderName
     * @param $senderName
     * @return mixed
     */
    public function receiveAnEmailFromName( $senderName )
    {
        $message = $this->fetchLastMessage();
        $this->assertEquals( $senderName, $message['from_name'] );
    }

    /**
     * Check if the latest email was received by $recipientEmail
     * @param $recipientEmail
     * @return mixed
     */
    public function receiveAnEmailToEmail( $recipientEmail )
    {
        $message = $this->fetchLastMessage();
        $this->assertEquals( $recipientEmail, $message['to_email'] );
    }

    /**
     * Check if the latest email was received by $recipientName
     * @param $recipientName
     * @return mixed
     */
    public function receiveAnEmailToName( $recipientName )
    {
        $message = $this->fetchLastMessage();
        $this->assertEquals( $recipientName, $message['to_name'] );
    }

    /**
     * Check if the latest email received has the $subject
     * @param $subject
     * @return mixed
     */
    public function receiveAnEmailWithSubject( $subject )
    {
        $message = $this->fetchLastMessage();
        $this->assertEquals( $subject, $message['subject'] );
    }

    /**
     * Check if the latest email received has the $textBody
     * @param $textBody
     * @return mixed
     */
    public function receiveAnEmailWithTextBody( $textBody )
    {
        $message = $this->fetchLastMessage();
        $this->assertEquals( $textBody, $message['text_body'] );
    }

    /**
     * Check if the latest email received has the $htmlBody
     * @param $htmlBody
     * @return mixed
     */
    public function receiveAnEmailWithHtmlBody( $htmlBody )
    {
        $message = $this->fetchLastMessage();
        $this->assertEquals( $htmlBody, $message['html_body'] );
    }

    /**
     * Look for a string in the most recent email (Text)
     * @param $expected
     * @return mixed
     */
    public function seeInEmailTextBody( $expected )
    {
        $email = $this->fetchLastMessage();
        $this->assertContains( $expected, $email['text_body'], "Email body contains text" );
    }

    /**
     * Look for a string in the most recent email (HTML)
     * @param $expected
     * @return mixed
     */
    public function seeInEmailHtmlBody( $expected )
    {
        $email = $this->fetchLastMessage();
        $this->assertContains( $expected, $email['html_body'], "Email body contains HTML" );
    }

    /**
     * Get all messages of the default inbox
     * @return array
     */
    protected function fetchMessages()
    {
        $messages = $this->client->get( "inboxes/{$this->config['inbox_id']}/messages" )->json();

        if( empty( $messages ) )
        {
            $this->fail( "No messages received" );
        }

        return $messages;
    }

    /**
     * Get the most recent message of the default inbox
     * @return array
     */
    protected function fetchLastMessage()
    {
        return array_shift( $this->fetchMessages() );
    }

    /**
     * Clean all the messages from inbox
     * @return void
     */
    protected function cleanInbox()
    {
        $this->client->patch( "inboxes/{$this->config['inbox_id']}/clean" )->send();
    }
}