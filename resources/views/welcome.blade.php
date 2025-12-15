<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EXPoints - Landing</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Landing-only stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/css/landingpage.css') }}" />
</head>
<body>

    <!-- TOP NAV (transparent) -->
    <header class="lp-header container-xl">
        <nav class="lp-nav">
            <a href="{{ route('home') }}" class="lp-brand">
                <img src="{{ asset('assets/img/EXPoints Logo.png') }}" alt="+EXPoints" class="lp-brand-img">
            </a>

            <ul class="lp-menu">
                <li><a href="#" class="lp-link">About</a></li>
                <li><a href="#" class="lp-link">Support</a></li>
                <li><a href="#" class="lp-link">Meet the Devs!</a></li>
                <li><a href="#" class="lp-link">Discover</a></li>
            </ul>
        </nav>
    </header>

    <!-- HERO -->
    <main class="lp-hero container-xl">
        <div class="lp-grid">
            <!-- Panda -->
            <div class="lp-panda">
                <img src="{{ asset('assets/img/LandingPagePanda.png') }}" alt="Panda mascot" class="lp-panda-img">
            </div>

            <!-- Copy + CTAs -->
            <div class="lp-copy">
                <h1 class="lp-title">Read One of the Most Trusted Reviews Online!</h1>
                <p class="lp-sub">
                    EXPoints is THE Gamer Forum, from properly rated reviews that ensures each Gamer has played through each game
                    they're reviewing to making sure the community is non-toxic, we also encourage interactivity with our StarUp system!
                </p>

                <div class="lp-ctas">
                    <a href="{{ route('login') }}" class="btn lp-btn lp-btn-light">LOGIN!</a>
                    <a href="{{ route('register') }}" class="btn lp-btn lp-btn-brand">REGISTER!</a>
                </div>
            </div>
        </div>

        <!-- Floating glyphs (decor only) -->
        <span class="glyph g-circle"  style="--x:12%; --y:72%"></span>
        <span class="glyph g-square"  style="--x:35%; --y:49%"></span>
        <span class="glyph g-cross"   style="--x:57%; --y:80%"></span>
        <span class="glyph g-cross"   style="--x:68%; --y:39%"></span>
        <span class="glyph g-circle"  style="--x:43%; --y:92%"></span>
        <span class="glyph g-square"  style="--x:75%; --y:91%"></span>
    </main>

    <!-- Posters strip (two vertical strips inside one merged frame) -->
    <aside class="posters">
        <div class="posters-track">
            <!-- A -->
            <img class="strip" src="{{ asset('assets/img/BoxArt Strip - Landing Page.png') }}" alt="Popular games strip A">
            <!-- B -->
            <img class="strip" src="{{ asset('assets/img/BoxArt Strip - Landing Page 2.png') }}" alt="Popular games strip B">
            <!-- A clone (for seamless loop) -->
            <img class="strip" src="{{ asset('assets/img/BoxArt Strip - Landing Page.png') }}" alt="" aria-hidden="true">
        </div>
    </aside>

    <!-- Cursor trail effect -->
    <style>
        .cursor-trail {
            position: fixed;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 160, 255, 0.8), transparent);
            pointer-events: none;
            z-index: 9999;
            animation: fadeTrail 0.8s ease-out forwards;
        }

        @keyframes fadeTrail {
            to {
                transform: scale(3);
                opacity: 0;
            }
        }
    </style>

    <script>
        // Cursor trail effect
        document.addEventListener('mousemove', (e) => {
            if (Math.random() > 0.8) { // Only create trail 20% of the time for performance
                const trail = document.createElement('div');
                trail.className = 'cursor-trail';
                trail.style.left = e.clientX - 5 + 'px';
                trail.style.top = e.clientY - 5 + 'px';
                document.body.appendChild(trail);

                setTimeout(() => trail.remove(), 800);
            }
        });

        // Add parallax effect to glyphs
        document.addEventListener('mousemove', (e) => {
            const glyphs = document.querySelectorAll('.glyph');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;

            glyphs.forEach((glyph, index) => {
                const speed = (index + 1) * 0.5;
                const x = (mouseX - 0.5) * speed * 20;
                const y = (mouseY - 0.5) * speed * 20;
                glyph.style.transform = `translate(calc(-50% + ${x}px), calc(-50% + ${y}px))`;
            });
        });
    </script>

</body>
</html>
