<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spotify Playlists - WhatsApp Spotify Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                <a href="/dashboard" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Dashboard
                </a>
                <a href="/analytics" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Analytics
                </a>
                <a href="/chat" class="text-gray-300 hover:text-white px-3 py-2 rounded-md font-medium transition duration-300">
                    Chat
                </a>
                <a href="/spotify-playlists" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full font-medium transition duration-300">
                    My Playlists
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold">My Spotify Playlists</h1>
                <p class="text-gray-400 text-sm mt-1">Manage your music collection</p>
            </div>
            <button id="create-playlist-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-300 self-end">
                <i class="fas fa-plus mr-2"></i> Create Playlist
            </button>
        </div>

        <!-- User Profile Section -->
        <div class="bg-gray-800 rounded-lg p-5 mb-6 border border-gray-700">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-gray-700 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-user text-xl text-green-500"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold" id="user-display-name">Loading...</h2>
                    <p class="text-gray-400 text-sm" id="user-id">ID: Loading...</p>
                    <div class="flex items-center mt-2 space-x-3">
                        <span class="text-xs bg-gray-700 px-2 py-1 rounded">Playlists: <span id="playlist-count">0</span></span>
                        <span class="text-xs bg-gray-700 px-2 py-1 rounded">Followers: <span id="followers-count">0</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Playlists Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8" id="playlists-container">
            <div class="text-center py-10 text-gray-500">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p>Loading playlists...</p>
            </div>
        </div>

        <!-- Playlist Tracks Modal -->
        <div id="tracks-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
            <div class="bg-gray-800 rounded-xl w-11/12 max-w-4xl max-h-[85vh] overflow-hidden flex flex-col">
                <div class="p-5 border-b border-gray-700 flex justify-between items-center">
                    <h2 class="text-xl font-bold" id="modal-playlist-name">Playlist Tracks</h2>
                    <button id="close-modal" class="text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto bg-gray-900" id="tracks-list">
                    <div class="text-center py-10 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Loading tracks...</p>
                    </div>
                </div>
                <div class="p-4 border-t border-gray-700 bg-gray-800">
                    <button id="add-track-btn" class="hidden bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-full font-medium transition duration-300">
                        <i class="fas fa-plus mr-2"></i> Add Track
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Playlist Modal -->
    <div id="create-playlist-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-gray-800 rounded-xl w-11/12 max-w-md p-6">
            <h2 class="text-2xl font-bold mb-4">Create New Playlist</h2>
            <div class="mb-4">
                <label class="block text-gray-300 mb-2">Playlist Name</label>
                <input type="text" id="playlist-name-input" class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Enter playlist name">
            </div>
            <div class="mb-4">
                <label class="block text-gray-300 mb-2">Description (Optional)</label>
                <textarea id="playlist-description-input" class="w-full bg-gray-700 text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Enter playlist description"></textarea>
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" id="playlist-public" class="form-checkbox text-green-500" checked>
                    <span class="ml-2 text-gray-300">Make public</span>
                </label>
            </div>
            <div class="flex justify-end space-x-3">
                <button id="cancel-create-playlist" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium">
                    Cancel
                </button>
                <button id="confirm-create-playlist" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                    Create
                </button>
            </div>
        </div>
    </div>

    <script>
        const API_BASE_URL = '/api';
        let currentPlaylistId = null;

        // DOM Elements
        const playlistsContainer = document.getElementById('playlists-container');
        const tracksModal = document.getElementById('tracks-modal');
        const closeModal = document.getElementById('close-modal');
        const tracksList = document.getElementById('tracks-list');
        const modalPlaylistName = document.getElementById('modal-playlist-name');
        const createPlaylistBtn = document.getElementById('create-playlist-btn');
        const createPlaylistModal = document.getElementById('create-playlist-modal');
        const cancelCreatePlaylist = document.getElementById('cancel-create-playlist');
        const confirmCreatePlaylist = document.getElementById('confirm-create-playlist');
        const playlistNameInput = document.getElementById('playlist-name-input');
        const playlistDescriptionInput = document.getElementById('playlist-description-input');
        const playlistPublic = document.getElementById('playlist-public');
        const userIdElement = document.getElementById('user-id');
        const userDisplayNameElement = document.getElementById('user-display-name');
        const playlistCountElement = document.getElementById('playlist-count');
        const followersCountElement = document.getElementById('followers-count');

        document.addEventListener('DOMContentLoaded', function() {
            loadUserProfile();
            loadPlaylists();
            
            // Event listeners
            closeModal.addEventListener('click', () => {
                tracksModal.classList.add('hidden');
            });
            
            createPlaylistBtn.addEventListener('click', () => {
                createPlaylistModal.classList.remove('hidden');
            });
            
            cancelCreatePlaylist.addEventListener('click', () => {
                createPlaylistModal.classList.add('hidden');
                playlistNameInput.value = '';
                playlistDescriptionInput.value = '';
                playlistPublic.checked = true;
            });
            
            confirmCreatePlaylist.addEventListener('click', createPlaylist);
        });

        async function loadUserProfile() {
            try {
                const response = await fetch(`${API_BASE_URL}/spotify/user-profile`);
                const data = await response.json();
                
                if (data.success) {
                    userDisplayNameElement.textContent = data.data.display_name || data.data.id;
                    userIdElement.textContent = `ID: ${data.data.id}`;
                    playlistCountElement.textContent = data.data.playlists_count || 0;
                    followersCountElement.textContent = data.data.followers_count || 0;
                } else {
                    userDisplayNameElement.textContent = 'Anonymous';
                    userIdElement.textContent = 'ID: N/A';
                }
            } catch (error) {
                console.error('Error loading user profile:', error);
                userDisplayNameElement.textContent = 'Error loading profile';
            }
        }

        async function loadPlaylists() {
            try {
                const response = await fetch(`${API_BASE_URL}/spotify/playlists`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    renderPlaylists(data.data);
                } else {
                    playlistsContainer.innerHTML = '<div class="col-span-full text-center py-10 text-gray-500">No playlists found</div>';
                }
            } catch (error) {
                console.error('Error loading playlists:', error);
                playlistsContainer.innerHTML = '<div class="col-span-full text-center py-10 text-red-500">Error loading playlists</div>';
            }
        }

        function renderPlaylists(playlists) {
            if (!playlists || playlists.length === 0) {
                playlistsContainer.innerHTML = '<div class="col-span-full text-center py-10 text-gray-500">No playlists found</div>';
                return;
            }

            playlistsContainer.innerHTML = '';
            
            playlists.forEach(playlist => {
                const playlistElement = document.createElement('div');
                playlistElement.className = 'bg-gray-800 rounded-xl p-4 border border-gray-700 hover:border-green-500 transition duration-300 cursor-pointer';
                playlistElement.innerHTML = `
                    <div class="flex items-center mb-3">
                        <div class="w-16 h-16 bg-gray-700 rounded flex items-center justify-center mr-4">
                            <i class="fas fa-list text-green-500 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold truncate">${playlist.name}</h3>
                            <p class="text-sm text-gray-400">${playlist.track_count || 0} tracks</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 mb-3 line-clamp-2">${playlist.description ? playlist.description.replace(/<[^>]*>/g, '') : 'No description'}</p>
                    <div class="flex justify-between items-center">
                        <div class="flex space-x-2">
                            <a href="${playlist.external_urls?.spotify || '#'}" target="_blank" class="open-spotify-btn flex items-center justify-center bg-[#1DB954] hover:bg-[#1ed760] text-white w-8 h-8 rounded-full transition duration-300" title="Open in Spotify">
                                <i class="fab fa-spotify text-sm"></i>
                            </a>
                        </div>
                        <button class="view-tracks-btn bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm transition duration-300" data-id="${playlist.id}">
                            View Tracks
                        </button>
                    </div>
                `;
                
                const viewTracksBtn = playlistElement.querySelector('.view-tracks-btn');
                viewTracksBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openTracksModal(playlist.id, playlist.name);
                });
                
                playlistElement.addEventListener('click', () => {
                    openTracksModal(playlist.id, playlist.name);
                });
                
                playlistsContainer.appendChild(playlistElement);
            });
        }

        async function openTracksModal(playlistId, playlistName) {
            currentPlaylistId = playlistId;
            modalPlaylistName.textContent = playlistName;
            
            try {
                const response = await fetch(`${API_BASE_URL}/spotify/playlists/tracks`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ playlist_id: playlistId })
                });
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    renderTracks(data.data.tracks);
                } else {
                    tracksList.innerHTML = '<div class="p-6 text-center text-gray-500">No tracks found</div>';
                }
            } catch (error) {
                console.error('Error loading tracks:', error);
                tracksList.innerHTML = '<div class="p-6 text-center text-red-500">Error loading tracks</div>';
            }
            
            tracksModal.classList.remove('hidden');
        }

        function renderTracks(tracks) {
            if (!tracks || tracks.length === 0) {
                tracksList.innerHTML = '<div class="p-6 text-center text-gray-500">No tracks in this playlist</div>';
                return;
            }

            tracksList.innerHTML = '';
            
            tracks.forEach((track, index) => {
                const trackElement = document.createElement('div');
                trackElement.className = 'p-4 border-b border-gray-700 flex items-center';
                trackElement.innerHTML = `
                    <div class="w-10 h-10 bg-gray-700 rounded flex items-center justify-center mr-4">
                        <i class="fas fa-music text-green-500 text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium truncate">${track.track.name}</div>
                        <div class="text-sm text-gray-400 truncate">${track.track.artists.map(artist => artist.name).join(', ')}</div>
                    </div>
                    <div class="text-gray-500 text-sm mx-4">${formatDuration(track.track.duration_ms)}</div>
                    <div class="flex space-x-2">
                        <a href="${track.track.external_urls?.spotify || '#'}" target="_blank" class="open-spotify-btn flex items-center justify-center bg-[#1DB954] hover:bg-[#1ed760] text-white w-8 h-8 rounded-full transition duration-300" title="Open in Spotify">
                            <i class="fab fa-spotify text-sm"></i>
                        </a>
                        <button class="delete-track-btn bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-sm transition duration-300" data-track-uri="${track.track.uri}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
                
                const deleteBtn = trackElement.querySelector('.delete-track-btn');
                deleteBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    deleteTrackFromPlaylist(track.track.uri);
                });
                
                tracksList.appendChild(trackElement);
            });
        }

        function formatDuration(durationMs) {
            const seconds = Math.floor(durationMs / 1000);
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
        }

        async function deleteTrackFromPlaylist(trackUri) {
            if (!confirm('Are you sure you want to remove this track from the playlist?')) {
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE_URL}/spotify/playlists/delete-song`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        playlist_id: currentPlaylistId,
                        track_uri: trackUri
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Refresh the tracks list
                    openTracksModal(currentPlaylistId, modalPlaylistName.textContent);
                } else {
                    alert('Failed to delete track: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error deleting track:', error);
                alert('Error deleting track');
            }
        }

        async function createPlaylist() {
            const name = playlistNameInput.value.trim();
            const description = playlistDescriptionInput.value.trim();
            const isPublic = playlistPublic.checked;
            
            if (!name) {
                alert('Please enter a playlist name');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE_URL}/spotify/playlists/create`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name: name,
                        description: description,
                        public: isPublic
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    createPlaylistModal.classList.add('hidden');
                    playlistNameInput.value = '';
                    playlistDescriptionInput.value = '';
                    playlistPublic.checked = true;
                    
                    // Refresh playlists list
                    loadPlaylists();
                } else {
                    alert('Failed to create playlist: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error creating playlist:', error);
                alert('Error creating playlist');
            }
        }
    </script>
</body>
</html>