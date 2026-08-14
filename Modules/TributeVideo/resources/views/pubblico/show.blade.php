<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $video->nome_completo }} — Video memoriale</title>
    <style>
        body {
            margin: 0;
            background: #0a0805;
            color: #faf6ec;
            font-family: Georgia, serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            box-sizing: border-box;
        }
        video {
            width: 100%;
            max-width: 720px;
            border: 1px solid #c2a35a55;
        }
        h1 {
            font-weight: 400;
            font-size: 1.5rem;
            letter-spacing: 0.02em;
            margin: 1.5rem 0 0;
        }
        p {
            color: #c2a35a;
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0.35rem 0 0;
        }
    </style>
</head>
<body>
    <video controls autoplay playsinline preload="metadata">
        <source src="{{ $video->cloudinary_url }}" type="video/mp4">
    </video>
    <h1>{{ $video->nome_completo }}</h1>
    @if ($video->date)
        <p>{{ $video->date }}</p>
    @endif
</body>
</html>
