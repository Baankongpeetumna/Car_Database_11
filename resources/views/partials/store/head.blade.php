<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' · VELOCE' : 'VELOCE · New & Used Cars' }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- Kanit (หัวข้อ) + Noto Sans Thai (เนื้อหา) รองรับภาษาไทย --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=kanit:400,500,600,700,800,600i,700i,800i|noto-sans-thai:400,500,600,700&display=swap">

@vite(['resources/css/app.css', 'resources/js/app.js'])
