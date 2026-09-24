@props([
    'title' => 'Anufish',
])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $title }} - Anufish</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-[100dvh] bg-anufish-background text-anufish-text antialiased">
    <main class="mx-auto flex min-h-[100dvh] w-full max-w-md items-center justify-center px-4 py-8 sm:px-6 sm:py-12">
        {{ $slot }}
    </main>
</body>
</html>
