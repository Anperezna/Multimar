<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Multimar</title>

    <link rel="icon" href="/img/logo.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/img/logo.ico" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/js/app.ts'])
</head>

<body class="font-sans antialiased">
    <div id="app"></div>
</body>

</html>
