<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Spotify Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-green-900 via-black to-green-900 text-white min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-900 bg-opacity-90 py-4 px-6 sticky top-0 z-50 border-b border-gray-800">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fab fa-spotify text-green-500 text-2xl"></i>
                <span class="text-xl font-bold">WhatsApp<span class="text-green-500">Spotify</span></span>
            </div>
            <div>
                <a href="/dashboard" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full font-medium transition duration-300">Open App</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="lg:w-1/2">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">
                        Manage Your <span class="text-green-500">Spotify Playlists</span> Through WhatsApp
                    </h1>
                    <p class="text-xl text-gray-300 mb-8">
                        Connect with friends, share music, and manage your Spotify playlists directly through WhatsApp conversations.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="/dashboard" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full font-bold text-lg transition duration-300">
                            Open App
                        </a>
                        <a href="#how-it-works" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-3 rounded-full font-bold text-lg transition duration-300">
                            See How It Works
                        </a>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <div class="bg-gray-800 rounded-2xl p-6 border border-gray-700">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold">WhatsApp Chat</h3>
                            <div class="flex space-x-2">
                                <div class="w-3 h-3 bg-gray-600 rounded-full"></div>
                                <div class="w-3 h-3 bg-gray-600 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            </div>
                        </div>
                        <div class="bg-gray-900 rounded-lg p-4 h-80 overflow-y-auto">
                            <div class="flex flex-col space-y-4">
                                <div class="bg-green-600 text-white rounded-lg p-3 max-w-xs self-start">
                                    Hey! Check out this playlist I made for you
                                </div>
                                <div class="flex justify-end">
                                    <div class="bg-gray-700 text-white rounded-lg p-3 max-w-xs">
                                        Sure, send it over!
                                    </div>
                                </div>
                                <div class="bg-green-600 text-white rounded-lg p-3 max-w-xs self-start">
                                    <div class="font-semibold mb-1">🎵 My Summer Vibes Playlist</div>
                                    <div class="flex items-center mt-2">
                                        <i class="fas fa-music mr-2"></i>
                                        <span>12 tracks • 45 min</span>
                                    </div>
                                    <button class="mt-2 bg-white text-black px-3 py-1 rounded-full text-sm font-medium">
                                        Add to My Playlists
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 px-4 bg-gray-900 bg-opacity-50">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-16">How You Can Use WhatsApp Spotify</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
                    <div class="text-green-500 text-3xl mb-4">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Chat Commands</h3>
                    <p class="text-gray-400">
                        Send commands in WhatsApp to search for songs, add tracks to playlists, or create new playlists.
                    </p>
                </div>
                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
                    <div class="text-green-500 text-3xl mb-4">
                        <i class="fas fa-music"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Music Sharing</h3>
                    <p class="text-gray-400">
                        Share songs, albums, or playlists directly in WhatsApp and they'll appear in your Spotify app.
                    </p>
                </div>
                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
                    <div class="text-green-500 text-3xl mb-4">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Collaboration</h3>
                    <p class="text-gray-400">
                        Work on playlists together with friends in real-time through WhatsApp conversations.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- App Preview Section -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-center mb-16">How It Works</h2>
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="lg:w-1/2">
                    <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold">WhatsApp Chat Interface</h3>
                            <div class="flex space-x-2">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            </div>
                        </div>
                        <div class="bg-gray-800 rounded-lg p-4 h-96 overflow-y-auto">
                            <div class="flex flex-col space-y-4">
                                <div class="bg-green-600 text-white rounded-lg p-3 max-w-xs self-start">
                                    Hey! Check out this playlist I made for you
                                </div>
                                <div class="flex justify-end">
                                    <div class="bg-gray-700 text-white rounded-lg p-3 max-w-xs">
                                        Sure, send it over!
                                    </div>
                                </div>
                                <div class="bg-green-600 text-white rounded-lg p-3 max-w-xs self-start">
                                    <div class="font-semibold mb-1">🎵 My Summer Vibes Playlist</div>
                                    <div class="flex items-center mt-2">
                                        <i class="fas fa-music mr-2"></i>
                                        <span>12 tracks • 45 min</span>
                                    </div>
                                    <button class="mt-2 bg-white text-black px-3 py-1 rounded-full text-sm font-medium">
                                        Add to My Playlists
                                    </button>
                                </div>
                                <div class="flex justify-end">
                                    <div class="bg-gray-700 text-white rounded-lg p-3 max-w-xs">
                                        Added! This is a great selection
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <h3 class="text-2xl font-bold mb-4">Manage Playlists Through WhatsApp</h3>
                    <p class="text-gray-300 mb-6">
                        Our platform connects your WhatsApp and Spotify accounts, allowing you to manage your music directly from your conversations. 
                        Share playlists, add songs, and discover new music through your existing WhatsApp chats.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Share Spotify links directly in WhatsApp</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Create playlists by sending song requests to friends</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Discover new music through conversations</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Collaborate on playlists with friends</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Spotify Integration Section -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold">Spotify Integration</h2>
                <p class="text-gray-400 mt-4 max-w-2xl mx-auto">
                    Connect your Spotify account to manage playlists directly from WhatsApp.
                </p>
            </div>
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="lg:w-1/2">
                    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold">Spotify Dashboard</h3>
                            <div class="text-green-500">
                                <i class="fab fa-spotify text-2xl"></i>
                            </div>
                        </div>
                        <div class="bg-gray-900 rounded-lg p-4 h-80 overflow-y-auto">
                            <div class="mb-4 p-3 bg-gray-800 rounded-lg">
                                <div class="font-semibold">My Summer Vibes</div>
                                <div class="text-sm text-gray-400">12 songs, 45 min</div>
                            </div>
                            <div class="mb-4 p-3 bg-gray-800 rounded-lg">
                                <div class="font-semibold">Workout Mix</div>
                                <div class="text-sm text-gray-400">18 songs, 1 hour 10 min</div>
                            </div>
                            <div class="mb-4 p-3 bg-gray-800 rounded-lg">
                                <div class="font-semibold">Chill Beats</div>
                                <div class="text-sm text-gray-400">20 songs, 1 hour 30 min</div>
                            </div>
                            <div class="mb-4 p-3 bg-gray-800 rounded-lg">
                                <div class="font-semibold">Road Trip Tunes</div>
                                <div class="text-sm text-gray-400">15 songs, 55 min</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <h3 class="text-2xl font-bold mb-4">Manage Your Music Library</h3>
                    <p class="text-gray-300 mb-6">
                        After connecting your Spotify account, you can manage your playlists, albums, and tracks directly through WhatsApp conversations.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Create new playlists from WhatsApp chat</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Add songs to existing playlists</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Share full playlists with friends</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <span>Discover music through friends' recommendations</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="bg-gray-800 rounded-2xl p-8 border border-gray-700">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="md:w-2/3">
                        <h2 class="text-2xl font-bold mb-3">Start Managing Your Playlists Today</h2>
                        <p class="text-gray-300">
                            Connect your Spotify account to unlock powerful features that let you manage your music directly through WhatsApp.
                        </p>
                    </div>
                    <div class="md:w-1/3 flex justify-center">
                        <a href="/dashboard" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full font-bold text-lg w-full text-center transition duration-300">
                            Open App
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 py-10 px-4 border-t border-gray-800">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center space-x-2">
                        <i class="fab fa-spotify text-green-500 text-2xl"></i>
                        <span class="text-xl font-bold">WhatsApp<span class="text-green-500">Spotify</span></span>
                    </div>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-spotify text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-whatsapp text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-github text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-6 pt-6 text-center text-gray-500 text-sm">
                <p>© 2025 WhatsApp Spotify Manager. This is a demo application and is not affiliated with Spotify or WhatsApp.</p>
            </div>
        </div>
    </footer>
</body>
</html>