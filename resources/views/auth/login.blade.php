<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bis Natal Sekolah Minggu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
    </style>
    <script>
        console.log(`
            %cWeb ini dikembangkan oleh Gerrant Hiya
            %cProfessional Software & IoT Developer
            
            %c📧 Business Email : g@ghiya.my.id
            %c🌐 Website        : https://ghiya.my.id
            `,
            "font-size:14px;font-weight:bold;color:#0d6efd;",
            "font-size:12px;color:#333;",
            "font-size:12px;color:#444;",
            "font-size:12px;color:#444;"
        );
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-4">
                <span class="text-4xl">🎄</span>
            </div>
            <h1 class="text-3xl font-bold text-white">Bis Natal Sekolah Minggu</h1>
            <p class="text-white/80 mt-2">GBI HMJ Neo Soho - 20 Desember 2025</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-6">Login</h2>

            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required autofocus
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                           placeholder="Masukkan username">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" id="password" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                           placeholder="Masukkan password">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl">
                    Masuk
                </button>

                <small>untuk Bantuan Login, hubungi <a href="https://wa.me/6281401668149" class="text-indigo-600 hover:underline">Gerrant Hiya</a></small>
            </form>
        </div>

        <p class="text-center text-white/60 text-sm mt-6">
            {{ date('Y') }} &copy; <a href="http://ghiya.my.id" target="_blank">Gerrant Hiya</a> | All rights reserved.
        </p>
    </div>
</body>
</html>
