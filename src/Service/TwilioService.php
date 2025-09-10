<?php
namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $client;
    private $from;

    public function __construct()
    {
        $sid = $_ENV['TWILIO_ACCOUNT_SID'];
        $token = $_ENV['TWILIO_AUTH_TOKEN'];
        $this->from = $_ENV['TWILIO_PHONE_NUMBER'];

        $this->client = new Client($sid, $token);
    }

    public function sendSms(string $to, string $message): void
    {
        $this->client->messages->create($to, [
            'from' => $this->from,
            'body' => $message,
        ]);
    }

    public function sendOtp(string $to, string $otp): void
    {
        $message = "Your OTP code is: {$otp}";
        $this->sendSms($to, $message);
    }
}
