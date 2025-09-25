# WhatsApp Spotify MCP

## About the Project

This project is an AI-powered integration that connects WhatsApp Business API with Spotify's API to create personalized playlists. Users can request playlists through WhatsApp messages, and the system will generate custom Spotify playlists based on their moods, activities, or preferences. The AI analyzes user inputs, creates playlists using both user's music taste and contextual relevance, and delivers the playlist link back to the user via WhatsApp.

Key features:
- WhatsApp Business API integration
- Spotify OAuth authentication
- AI-powered playlist generation using Google Gemini
- Real-time job processing with progress updates
- Interactive message flow with confirmations
- User analytics and dashboard

## Environment Variables

To run this project, you'll need to set up the following environment variables in your `.env` file:

### WhatsApp Business API Credentials
- `WHATSAPP_APP_ID` - Your WhatsApp Business App ID
- `WHATSAPP_APP_SECRET` - Your WhatsApp Business App Secret
- `WHATSAPP_BUSINESS_PHONE_ID` - Your WhatsApp Business Phone Number ID
- `WHATSAPP_ACCESS_TOKEN` - Your WhatsApp Access Token
- `WHATSAPP_API_VERSION` - API version (default: v18.0)
- `WHATSAPP_WEBHOOK_VERIFY_TOKEN` - Webhook verification token
- `WHATSAPP_AUTO_REPLY_PHONE` - Phone number for auto-replies

### Spotify API Credentials
- `SPOTIFY_CLIENT_ID` - Your Spotify Client ID
- `SPOTIFY_CLIENT_SECRET` - Your Spotify Client Secret
- `SPOTIFY_REDIRECT_URI` - Your Spotify Redirect URI (e.g., http://127.0.0.1:8000/spotify/callback)

### Google Gemini API
- `GEMINI_API_KEY` - Your Google Gemini API Key

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/manuel-kl/whatsapp-spotify-mcp.git
   cd whatsapp-spotify-mcp
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Set up environment variables**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure environment variables**
   Update the `.env` file with your:
   - Spotify API credentials (SPOTIFY_CLIENT_ID, SPOTIFY_CLIENT_SECRET, SPOTIFY_REDIRECT_URI)
   - WhatsApp Business API credentials (WHATSAPP_APP_ID, WHATSAPP_APP_SECRET, WHATSAPP_BUSINESS_PHONE_ID, WHATSAPP_ACCESS_TOKEN, WHATSAPP_API_VERSION, WHATSAPP_WEBHOOK_VERIFY_TOKEN, WHATSAPP_AUTO_REPLY_PHONE)
   - Google Gemini API key (GEMINI_API_KEY)
   - Database configuration

5. **Run database migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

7. **Start Laravel Horizon for processing and monitoring queues**
   ```bash
   php artisan horizon
   ```
