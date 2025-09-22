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
}
