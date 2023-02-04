<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <title>Hello, world!</title>
</head>
<body>
    @include('layouts.header')
    <h1>Hello, world!</h1>
    <button type="button" class="btn btn-link">Link</button>
</body>

</html>
