<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $notification->title }}</title>
</head>
<body>
    <h2>{{ $notification->title }}</h2>
    <p>{{ $notification->message }}</p>

    @if(!empty($notification->meta))
        <hr />
        <pre>{{ json_encode($notification->meta, JSON_PRETTY_PRINT) }}</pre>
    @endif

    <p>— {{ config('app.name') }}</p>
</body>
</html>
