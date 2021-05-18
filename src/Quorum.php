<?php
namespace Nano;

use GuzzleHttp\Client;

class Quorum
{
    protected $ipPort;
    protected $client;

    public function __construct($ipPort)
    {
        $this->setIpPort($ipPort);
    }

    public function __invoke()
    {
        $response = $this->getClient()->post($this->getIpPort(), [
            'body' => json_encode([
                "action" => "confirmation_active"
            ])
        ]);
        $elections = json_decode($response->getBody(), true)['confirmations'];
        $voters = [];
        $representatives = [];
        foreach ($elections as $index => $election) {
            if (!($index % 1000)) {
                echo $index . "/" . count($elections) . "\n";
            }
            $response = $this->getClient()->post($this->getIpPort(), [
                'body' => json_encode([
                    "action" => "confirmation_info",
                    "json_block" => "true",
                    "root" => $election,
                    "representatives" => true
                ])
            ]);
            $electionJson = json_decode($response->getBody(), true);
            if (isset($electionJson['error']) || $electionJson['voters'] == 1) {
                continue;
            }
            foreach ($electionJson["blocks"] as $hash => $block) {
                //Used for outputing slow nodes blocks
                //if ($electionJson['voters'] > 20 && !isset($block["representatives"]['nano_1awsn43we17c1oshdru4azeqjz9wii41dy8npubm4rg11so7dx3jtqgoeahy'])) {
                //    echo $hash . " - " .  $block["tally"] / 1000000000000000000000000000000 . "\n";
                //}

                foreach ($block["representatives"] as $representative => $amount) {
                    if (!isset($voters[$representative])) {
                        $voters[$representative] = 0;
                    }
                    $voters[$representative]++;
                    $representatives[$representative] = $amount / 1000000000000000000000000000000;
                }
            }
        }
        asort($voters);
        foreach ($voters as $representative => $votes) {
            echo $representative . " " . $representatives[$representative] . " " . $votes . "\n";
        }
    }

    public function getClient()
    {
        if (!isset($this->client)) {
            $this->client = new Client();
        }
        return $this->client;
    }

    public function getIpPort()
    {
        return $this->ipPort;
    }

    public function setIpPort($ipPort)
    {
        $this->ipPort = $ipPort;
        return $this;
    }
}