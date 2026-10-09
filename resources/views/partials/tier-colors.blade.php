@once
    <style>
        .tier-dot,
        .tier-bar {
            background-color: var(--tier-color, #71717A);
            background-image: none;
        }

        .tier-avatar,
        .tier-on {
            background-color: var(--tier-color, #71717A);
            color: var(--tier-ink, #FFFFFF);
        }

        .tier-on {
            border-color: var(--tier-color, #71717A);
        }

        .tier-on > span {
            color: inherit;
        }

        .tier-on .tier-dot {
            background-color: currentColor;
        }

        .tier-badge {
            background-color: color-mix(
                in srgb,
                var(--tier-color, #71717A) 16%,
                transparent
            );
            color: #27272A;
        }

        .tier-stat {
            background-color: color-mix(
                in srgb,
                var(--tier-color, #71717A) 7%,
                transparent
            );
        }

        .tier-tint {
            background-image: linear-gradient(
                to bottom,
                color-mix(
                    in srgb,
                    var(--tier-color, #71717A) 12%,
                    transparent
                ),
                transparent
            );
        }

        .tier-text {
            color: color-mix(
                in srgb,
                var(--tier-color, #71717A) 75%,
                #18181B
            );
        }

        .dark .tier-badge {
            color: #F4F4F5;
        }

        .dark .tier-text {
            color: color-mix(
                in srgb,
                var(--tier-color, #71717A) 70%,
                white
            );
        }

        input:checked + .tier-choice {
            border-color: var(--tier-color, #71717A);
            background-color: color-mix(
                in srgb,
                var(--tier-color, #71717A) 12%,
                transparent
            );
            box-shadow: 0 0 0 2px color-mix(
                in srgb,
                var(--tier-color, #71717A) 35%,
                transparent
            );
        }
    </style>
@endonce