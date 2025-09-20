<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WhatsappSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helps set up WhatsApp Cloud API credentials';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('WhatsApp Cloud API Setup Helper');
        $this->line('');

        $appId = config('whatsapp.app_id');
        $appSecret = config('whatsapp.app_secret');

        if (!$appId || !$appSecret) {
            $this->error('Please set your WHATSAPP_APP_ID and WHATSAPP_APP_SECRET in the .env file first.');
            return 1;
        }

        $this->info('1. First, get a short-lived access token:');
        $this->line('   - Go to https://developers.facebook.com/tools/explorer/');
        $this->line('   - Select your app');
        $this->line('   - Click "Get Token" -> "Get Page Access Token"');
        $this->line('   - Select your WhatsApp Business page');
        $this->line('');

        $shortLivedToken = $this->ask('2. Enter the short-lived access token you got from the Graph API Explorer');

        if (!$shortLivedToken) {
            $this->error('Short-lived token is required.');
            return 1;
        }

        $this->info('3. Exchanging short-lived token for long-lived token...');

        $response = Http::get('https://graph.facebook.com/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'fb_exchange_token' => $shortLivedToken,
        ]);

        if ($response->failed()) {
            $this->error('Failed to exchange token: ' . $response->body());
            return 1;
        }

        $longLivedToken = $response->json()['access_token'];
        $this->info('Long-lived token: ' . $longLivedToken);
        $this->line('');

        $this->info('4. Getting page ID...');

        $response = Http::get('https://graph.facebook.com/me/accounts', [
            'access_token' => $longLivedToken,
        ]);

        if ($response->failed()) {
            $this->error('Failed to get pages: ' . $response->body());
            return 1;
        }

        $pages = $response->json()['data'];
        if (empty($pages)) {
            $this->error('No pages found. Please make sure you have a WhatsApp Business page.');
            return 1;
        }

        $pageId = $pages[0]['id'];
        $pageName = $pages[0]['name'];
        $this->info("Page ID: {$pageId} ({$pageName})");
        $this->line('');

        $this->info('5. Getting WhatsApp Business Account ID...');

        $response = Http::get("https://graph.facebook.com/v18.0/{$pageId}/whatsapp_business_accounts", [
            'access_token' => $longLivedToken,
            'fields' => 'id,name,whatsapp_business_profile'
        ]);

        if ($response->failed()) {
            $this->error('Failed to get WhatsApp Business Account: ' . $response->body());
            return 1;
        }

        $whatsappAccounts = $response->json()['data'];
        if (empty($whatsappAccounts)) {
            $this->error('No WhatsApp Business Accounts found. Please make sure you have set up WhatsApp Business.');
            return 1;
        }

        $businessAccountId = $whatsappAccounts[0]['id'];
        $businessAccountName = $whatsappAccounts[0]['name'] ?? 'Unknown';
        $this->info("WhatsApp Business Account ID: {$businessAccountId} ({$businessAccountName})");
        $this->line('');

        $this->info('Setup complete! Please add these values to your .env file:');
        $this->line("WHATSAPP_ACCESS_TOKEN={$longLivedToken}");
        $this->line("WHATSAPP_BUSINESS_PHONE_ID={$businessAccountId}");
        $this->line('');

        $this->info('Next steps:');
        $this->line('1. Update your .env file with the values above');
        $this->line('2. Set a custom WHATSAPP_WEBHOOK_VERIFY_TOKEN in your .env file');
        $this->line('3. Register your webhook URL in the WhatsApp Business Manager:');
        $this->line('   https://yourdomain.com/api/whatsapp/webhook');
        $this->line('4. Test sending a message using the WhatsappController@sendMessage method');

        return 0;
    }
}
