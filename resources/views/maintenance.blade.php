<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — {{ config('app.name') }}</title>
    <link rel="icon" href="/favicon.png" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: .6; }
            100% { transform: scale(1.5); opacity: 0; }
        }
        .float-anim   { animation: float 3.5s ease-in-out infinite; }
        .pulse-ring   { animation: pulse-ring 2s ease-out infinite; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50 to-slate-100">

    {{-- Decorative blobs --}}
    <div class="fixed top-0 left-0 w-72 h-72 rounded-full opacity-10 blur-3xl pointer-events-none" style="background:#0f4c9a;transform:translate(-30%,-30%)"></div>
    <div class="fixed bottom-0 right-0 w-96 h-96 rounded-full opacity-10 blur-3xl pointer-events-none" style="background:#1a6fd4;transform:translate(30%,30%)"></div>

    <div class="relative z-10 text-center px-6 py-10 max-w-sm w-full">

        {{-- Animated icon --}}
        <div class="relative inline-flex items-center justify-center mb-8">
            <div class="absolute w-24 h-24 rounded-full pulse-ring" style="background:#0f4c9a22"></div>
            <img src="/favicon.png" alt="Logo" class="w-20 h-20 object-contain float-anim drop-shadow-xl">
        </div>

        {{-- Heading --}}
        <h1 class="text-2xl font-extrabold text-slate-800 mb-2 leading-tight">
            Sedang Maintenance
        </h1>
        <p class="text-sm text-slate-500 leading-relaxed mb-8">
            {{ $message ?? 'Sistem sedang dalam pemeliharaan. Silahkan kembali beberapa saat lagi.' }}
        </p>

        {{-- Progress bar decoration --}}
        <div class="w-full bg-slate-200 rounded-full h-1.5 mb-8 overflow-hidden">
            <div class="h-1.5 rounded-full" style="background:linear-gradient(90deg,#0f4c9a,#3b82f6);width:60%;animation:progress 2.5s ease-in-out infinite alternate"></div>
        </div>
        <style>
            @keyframes progress {
                from { width: 20%; }
                to   { width: 90%; }
            }
        </style>

        {{-- App name --}}
        <p class="text-xs text-slate-400 font-medium">{{ config('app.name') }}</p>
    </div>
</body>
</html>
