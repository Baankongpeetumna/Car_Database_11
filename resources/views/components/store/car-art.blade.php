@props(['color' => '#D7322E', 'type' => 'sedan'])

@php
    // ภาพประกอบรถด้านข้าง ใช้แทนรูปจริงเมื่อรถยังไม่มี image_url
    $bodies = [
        'sedan' => [
            'body' => 'M34,150 L34,128 Q36,112 66,106 L118,100 Q150,68 188,64 L248,64 Q286,68 314,99 L352,106 Q370,112 370,130 L370,150 Z',
            'glass' => 'M136,100 Q162,76 190,73 L246,73 Q272,77 294,100 Z',
            'pillar' => 'M214,73 L210,100',
            'wheels' => [108, 296],
        ],
        'suv' => [
            'body' => 'M36,152 L36,112 Q38,98 60,95 L96,93 L126,58 Q132,52 142,52 L290,52 Q306,53 316,68 L340,95 Q368,99 370,118 L370,152 Z',
            'glass' => 'M134,90 L150,62 L286,62 Q298,63 304,72 L320,90 Z',
            'pillar' => 'M222,62 L222,90',
            'wheels' => [108, 298],
        ],
        'pickup' => [
            'body' => 'M34,152 L34,100 L188,100 L188,62 Q190,54 200,54 L282,54 Q292,55 298,63 L324,94 Q364,98 370,116 L370,152 Z',
            'glass' => 'M200,92 L204,64 L280,64 Q288,65 292,72 L308,92 Z',
            'pillar' => 'M248,64 L248,92',
            'wheels' => [100, 300],
        ],
        'hatchback' => [
            'body' => 'M44,150 L44,112 Q46,96 70,86 L108,62 Q116,56 130,56 L238,56 Q270,58 300,90 L344,100 Q366,105 366,126 L366,150 Z',
            'glass' => 'M116,88 L132,66 L236,66 Q258,68 282,92 Z',
            'pillar' => 'M196,66 L196,90',
            'wheels' => [112, 296],
        ],
        'coupe' => [
            'body' => 'M30,150 L30,130 Q34,116 68,111 L140,104 Q176,74 216,72 L252,74 Q288,80 322,105 L356,110 Q372,116 372,134 L372,150 Z',
            'glass' => 'M156,103 Q184,82 216,80 L250,82 Q274,88 298,104 Z',
            'pillar' => 'M232,81 L236,104',
            'wheels' => [104, 300],
        ],
        'van' => [
            'body' => 'M36,152 L36,60 Q36,44 54,44 L292,44 Q306,45 316,60 L346,98 Q370,102 370,122 L370,152 Z',
            'glass' => 'M56,58 L118,58 L118,90 L56,90 Z M132,58 L200,58 L200,90 L132,90 Z M214,58 L290,58 Q300,59 306,68 L324,92 L214,92 Z',
            'pillar' => '',
            'wheels' => [104, 302],
        ],
    ];

    $b = $bodies[$type] ?? $bodies['sedan'];
    $uid = 'c'.substr(md5($color.$type.uniqid('', true)), 0, 8);
@endphp

<svg {{ $attributes->merge(['class' => 'w-full h-auto']) }} viewBox="0 0 400 190" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Car illustration">
    <defs>
        <linearGradient id="{{ $uid }}-shine" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity=".35"/>
            <stop offset=".45" stop-color="#fff" stop-opacity="0"/>
            <stop offset="1" stop-color="#000" stop-opacity=".18"/>
        </linearGradient>
    </defs>

    <ellipse cx="202" cy="168" rx="172" ry="9" fill="#1B1F24" opacity=".14"/>

    <path d="{{ $b['body'] }}" fill="{{ $color }}" stroke="#1B1F24" stroke-opacity=".22" stroke-width="2" stroke-linejoin="round"/>
    <path d="{{ $b['body'] }}" fill="url(#{{ $uid }}-shine)"/>
    <path d="{{ $b['glass'] }}" fill="#26303B" opacity=".88"/>
    @if ($b['pillar'])
        <path d="{{ $b['pillar'] }}" stroke="{{ $color }}" stroke-width="5"/>
    @endif

    {{-- ไฟหน้า / ไฟท้าย --}}
    <rect x="352" y="116" width="16" height="7" rx="3" fill="#FFE7A3"/>
    <rect x="34" y="118" width="10" height="7" rx="3" fill="#E5322D" opacity=".85"/>
    <path d="M60,132 L340,132" stroke="#1B1F24" stroke-opacity=".12" stroke-width="2"/>

    @foreach ($b['wheels'] as $cx)
        <circle cx="{{ $cx }}" cy="150" r="27" fill="#1B1F24"/>
        <circle cx="{{ $cx }}" cy="150" r="15" fill="#C9CDD3"/>
        <circle cx="{{ $cx }}" cy="150" r="5" fill="#5B626B"/>
    @endforeach
</svg>
