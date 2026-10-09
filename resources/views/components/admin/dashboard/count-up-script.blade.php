<script>
    // Count-up animation for elements with [data-countup] (skipped if the user prefers reduced motion)
    (function () {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            var target = parseFloat(el.dataset.countup);
            if (isNaN(target) || target === 0) return;

            var decimals = parseInt(el.dataset.decimals || '0', 10);
            var duration = 900;
            var start = null;

            function fmt(n) {
                return n.toLocaleString(undefined, {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });
            }

            function step(ts) {
                if (start === null) start = ts;
                var t = Math.min((ts - start) / duration, 1);
                var eased = 1 - Math.pow(1 - t, 3);
                el.textContent = fmt(target * eased);
                if (t < 1) requestAnimationFrame(step);
                else el.textContent = fmt(target);
            }

            el.textContent = fmt(0);
            requestAnimationFrame(step);
        });
    })();
</script>
