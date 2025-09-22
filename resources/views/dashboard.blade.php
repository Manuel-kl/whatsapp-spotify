<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WhatsApp Spotify Integration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">
                            <i class="fab fa-spotify text-success"></i>
                            WhatsApp Spotify Integration Dashboard
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <i class="fab fa-spotify"></i> Spotify Integration
                                    </div>
                                    <div class="card-body">
                                        @if(session('spotify_access_token'))
                                            <p class="text-success">
                                                <i class="fas fa-check-circle"></i>
                                                Connected to Spotify!
                                            </p>
                                            <p class="text-muted small">
                                                Token expires: {{ session('spotify_token_expires_at') }}
                                            </p>
                                            <form action="{{ route('spotify.disconnect') }}" method="POST" class="mt-2">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-unlink"></i> Disconnect
                                                </button>
                                            </form>
                                        @else
                                            <p class="text-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Not connected to Spotify
                                            </p>
                                            <a href="{{ route('spotify.authorize') }}" class="btn btn-success">
                                                <i class="fab fa-spotify"></i> Connect Spotify
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <i class="fab fa-whatsapp"></i> WhatsApp Integration
                                    </div>
                                    <div class="card-body">
                                        <p class="text-info">
                                            <i class="fas fa-info-circle"></i>
                                            WhatsApp webhook ready
                                        </p>
                                        <p class="text-muted small">
                                            Send messages to create playlists based on mood!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5>API Tests</h5>
                            <p class="text-muted">Test the Spotify API connection:</p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="/api/spotify/test-token" class="btn btn-outline-success" target="_blank">
                                    <i class="fas fa-flask"></i> Test Client Token
                                </a>
                                <a href="/api/spotify/connection-status" class="btn btn-outline-info" target="_blank">
                                    <i class="fas fa-link"></i> Check Connection
                                </a>
                                @if(session('spotify_access_token'))
                                <a href="/api/spotify/user-profile" class="btn btn-outline-primary" target="_blank">
                                    <i class="fas fa-user"></i> Get Profile
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <h5>How it works</h5>
                            <ol class="text-muted">
                                <li>Connect your Spotify account using the button above</li>
                                <li>Send a WhatsApp message describing your mood</li>
                                <li>Our AI will create a personalized playlist for you</li>
                                <li>Enjoy your music!</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>