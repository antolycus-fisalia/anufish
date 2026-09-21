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

<body class="min-h-screen bg-anufish-background text-anufish-text">

    <div class="grid min-h-screen lg:grid-cols-[250px_1fr]">

        <x-navigation.sidebar />

        <main class="min-w-0">

            <x-navigation.topbar :title="$title" />

            <div class="mx-auto max-w-6xl p-4 md:p-8">

                {{ $slot }}

            </div>

        </main>

    </div>

</body>
</html>