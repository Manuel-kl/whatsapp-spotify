<?php

namespace App\Service;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class AiService
{
    public function sendAiRequest(string $message, string $systemPrompt)
    {
        $response = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($message)
            ->asText();

        return $response;
    }

    public function sendAiRequestWithHistory(string $message, string $systemPrompt, array $messageHistory = [])
    {
        $historyContext = $this->formatMessageHistory($messageHistory);

        $fullPrompt = $historyContext . "\n\nCurrent message: " . $message;

        $response = Prism::text()
            ->using(Provider::Gemini, 'gemini-2.0-flash')
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($fullPrompt)
            ->asText();

        return $response;
    }

    protected function formatMessageHistory(array $messageHistory): string
    {
        if (empty($messageHistory)) {
            return '';
        }

        $formattedHistory = "Previous conversation history:\n";

        foreach ($messageHistory as $msg) {
            $sender = $this->identifyMessageSender($msg);

            $timestamp = 'Unknown time';
            if (isset($msg['timestamp']) && $msg['timestamp']) {
                try {
                    $timestamp = \Carbon\Carbon::parse($msg['timestamp'])->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $timestamp = 'Unknown time';
                }
            }

            $formattedHistory .= "[$timestamp] $sender: {$msg['body']}\n";
        }

        return $formattedHistory;
    }

    protected function identifyMessageSender($message): string
    {
        $businessPhoneId = config('whatsapp.business_phone_id');

        if ($message['from'] === $businessPhoneId) {
            return 'AI Assistant';
        }

        return 'User';
    }
}
