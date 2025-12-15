<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Discover - EXPoints</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-xl font-bold text-gray-800">EXPoints</a>
                    <div class="hidden sm:flex sm:space-x-8 sm:ml-10">
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2">Home</a>
                        <a href="{{ route('discover') }}" class="text-indigo-600 font-medium px-3 py-2">Discover</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Login</a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Discover Games</h2>

                    <div id="games-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="text-center py-8 text-gray-500">Loading games...</div>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mt-8 mb-4">Recent Posts</h3>
                    <div id="posts-list" class="space-y-4">
                        <div class="text-center py-8 text-gray-500">Loading posts...</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Fetch games
        fetch('/api/posts')
            .then(response => response.json())
            .then(data => {
                const posts = data.data || data;
                const games = [...new Set(posts.map(post => post.game))];
                const gamesContainer = document.getElementById('games-list');

                if (games.length === 0) {
                    gamesContainer.innerHTML = '<p class="text-gray-500">No games found.</p>';
                } else {
                    gamesContainer.innerHTML = games.slice(0, 9).map(game => `
                        <a href="/games/${encodeURIComponent(game)}"
                           class="block p-4 bg-gray-50 rounded-lg hover:bg-indigo-50 hover:border-indigo-200 border transition">
                            <h4 class="font-medium text-gray-900">${game}</h4>
                            <p class="text-sm text-gray-500">${posts.filter(p => p.game === game).length} posts</p>
                        </a>
                    `).join('');
                }

                // Show recent posts
                const postsContainer = document.getElementById('posts-list');
                if (posts.length === 0) {
                    postsContainer.innerHTML = '<p class="text-gray-500">No posts found.</p>';
                } else {
                    postsContainer.innerHTML = posts.slice(0, 5).map(post => `
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-gray-900">${post.title}</h4>
                                    <p class="text-sm text-indigo-600">${post.game}</p>
                                </div>
                                <span class="text-xs text-gray-500">by ${post.username}</span>
                            </div>
                            <p class="text-gray-600 mt-2 text-sm">${post.content.substring(0, 150)}${post.content.length > 150 ? '...' : ''}</p>
                        </div>
                    `).join('');
                }
            })
            .catch(error => {
                document.getElementById('games-list').innerHTML = '<p class="text-red-500">Failed to load games.</p>';
                document.getElementById('posts-list').innerHTML = '<p class="text-red-500">Failed to load posts.</p>';
            });
    </script>
</body>
</html>
