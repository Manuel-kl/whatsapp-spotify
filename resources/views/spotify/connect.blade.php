<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Spotify</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Connect Your Spotify Account</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-center">
                            To use our Spotify features, you need to connect your Spotify account.
                        </p>
                        <p class="text-center">
                            Click the button below to authorize our application to access your Spotify data.
                        </p>
                        <div class="text-center">
                            <a href="{{ $authUrl }}" class="btn btn-success btn-lg">
                                <i class="fab fa-spotify"></i> Connect Spotify
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>