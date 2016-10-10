<?php

namespace IdeasBucket\Common\Swiftmailer\Transport;

use Swift_Mime_Message;
use GuzzleHttp\ClientInterface;

/**
 * Class MandrillTransport
 *
 * @package IdeasBucket\Common\Swiftmailer\Transport
 *
 * Note: Adapted from Laravel Framework.
 * @see https://github.com/laravel/framework/blob/5.3/LICENSE.md
 */
class Mandrill extends AbstractTransport
{
    /**
     * Guzzle client instance.
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * The Mandrill API key.
     *
     * @var string
     */
    protected $key;

    /**
     * Create a new Mandrill transport instance.
     *
     * @param  \GuzzleHttp\ClientInterface  $client
     * @param  string  $key
     */
    public function __construct(ClientInterface $client, $key)
    {
        $this->client = $client;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $data = [
            'key' => $this->key,
            'to' => $this->getToAddresses($message),
            'raw_message' => $message->toString(),
            'async' => true,
        ];

        if (version_compare(ClientInterface::VERSION, '6') === 1) {

            $options = ['form_params' => $data];

        } else {

            $options = ['body' => $data];

        }

        $this->client->post('https://mandrillapp.com/api/1.0/messages/send-raw.json', $options);

        return $this->numberOfRecipients($message);
    }

    /**
     * Get all the addresses this message should be sent to.
     *
     * Note that Mandrill still respects CC, BCC headers in raw message itself.
     *
     * @param  \Swift_Mime_Message $message
     * @return array
     */
    protected function getToAddresses(Swift_Mime_Message $message)
    {
        $to = [];

        if ($message->getTo()) {

            $to = array_merge($to, array_keys($message->getTo()));

        }

        if ($message->getCc()) {

            $to = array_merge($to, array_keys($message->getCc()));

        }

        if ($message->getBcc()) {

            $to = array_merge($to, array_keys($message->getBcc()));

        }

        return $to;
    }

    /**
     * Get the API key being used by the transport.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the API key being used by the transport.
     *
     * @param  string  $key
     * @return string
     */
    public function setKey($key)
    {
        return $this->key = $key;
    }
}