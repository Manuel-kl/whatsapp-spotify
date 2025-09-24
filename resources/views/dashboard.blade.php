<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WhatsApp Spotify Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 text-white">
    <!-- Navigation -->
    <nav class="bg-gray-800 py-4 px-6 sticky top-0 z-50 border-b border-gray-700">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fab fa-spotify text-green-500 text-2xl"></i>
                <span class="text-xl font-bold">WhatsApp<span class="text-green-500">Spotify</span></span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Home
                </a>
                <a href="/dashboard" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full font-medium transition duration-300">
                    Dashboard
                </a>
                <a href="/spotify-playlists" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    My Playlists
                </a>
                <a href="/chat" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Chat
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold">Dashboard</h1>
        </div>

        @if(session('success'))
            <div class="bg-green-900 border border-green-700 text-green-200 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-900 border border-red-700 text-red-200 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Spotify Connection Status -->
        <div class="bg-gray-800 rounded-lg p-5 mb-6 border border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-900 rounded-full flex items-center justify-center mr-4">
                        <i class="fab fa-spotify text-green-500 text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">Spotify Connection</h2>
                        <p class="text-gray-400 text-sm">Status: Connected to Spotify</p>
                        <p class="text-xs text-gray-500">Token expires: {{ $spotifyToken->expires_at }}</p>
                    </div>
                </div>
                <form action="/api/spotify/disconnect" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">
                        Disconnect
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Functions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <a href="/spotify-playlists" class="bg-gray-800 rounded-lg p-6 border border-gray-700 hover:border-green-500 transition duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gray-700 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-list text-green-500 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold">Manage Playlists</h3>
                </div>
                <p class="text-gray-400 text-sm mb-4">View, create, and edit your Spotify playlists</p>
                <div class="text-green-500 text-sm">
                    <i class="fas fa-arrow-right mr-2"></i> View Playlists
                </div>
            </a>

            <a href="/chat" class="bg-gray-800 rounded-lg p-6 border border-gray-700 hover:border-green-500 transition duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gray-700 rounded-lg flex items-center justify-center mr-4">
                        <i class="fab fa-whatsapp text-green-500 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold">Chat Interface</h3>
                </div>
                <p class="text-gray-400 text-sm mb-4">Send WhatsApp messages to control your music</p>
                <div class="text-green-500 text-sm">
                    <i class="fas fa-arrow-right mr-2"></i> Open Chat
                </div>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 class="text-lg font-bold mb-4">Recent Activity</h2>
            <div class="text-gray-500 text-center py-8">
                <i class="fas fa-history text-2xl mb-2"></i>
                <p>No recent activity yet</p>
                <p class="text-sm mt-2">Start by sending a message in the chat interface</p>
            </div>
        </div>
    </div>
</body>
</html>