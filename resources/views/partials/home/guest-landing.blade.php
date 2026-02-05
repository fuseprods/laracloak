<style>
    .hero-container {
        text-align: center;
        padding: 4rem 1rem 6rem;
        position: relative;
        overflow: hidden;
    }

    .proxy-visual {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2rem;
        margin: 3rem 0;
        opacity: 0.9;
    }

    .proxy-node {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: var(--bg-card);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
    }

    .proxy-line {
        flex: 1;
        max-width: 100px;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--primary), transparent);
        position: relative;
    }

    .proxy-line::after {
        content: '';
        position: absolute;
        top: -2px;
        left: 0;
        width: 10px;
        height: 6px;
        background: #fff;
        border-radius: 50%;
        filter: blur(2px);
        animation: moveLine 2s infinite linear;
    }

    @keyframes moveLine {
        0% {
            left: 0;
            opacity: 0;
        }

        50% {
            opacity: 1;
        }

        100% {
            left: 100%;
            opacity: 0;
        }
    }

    .opaque-badge {
        padding: 0.75rem 2rem;
        background: var(--bg-card);
        border: 1px solid var(--primary);
        color: var(--primary);
        border-radius: 50px;
        font-family: monospace;
        font-weight: 600;
        box-shadow: 0 0 15px var(--primary-glow);
    }

    .features-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        max-width: 1100px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .mockup-wrapper {
        margin-top: 6rem;
        padding: 0 1rem;
    }

    .browser-frame {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        max-width: 1000px;
        margin: 0 auto;
    }

    .browser-bar {
        background: rgba(0, 0, 0, 0.2);
        padding: 0.75rem 1rem;
        display: flex;
        gap: 0.5rem;
        border-bottom: 1px solid var(--border);
    }

    .browser-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--border);
    }

    .mockup-content {
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-body) 100%);
        color: var(--text-muted);
        overflow: hidden;
        position: relative;
    }

    .screenshot-mockup {
        aspect-ratio: 1525 / 828;
    }

    .mockup-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .mockup-image:hover {
        transform: scale(1.02);
    }

    .btn-github:hover {
        transform: translateY(-3px);
        background: #000000 !important;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5) !important;
    }

    /* Lightbox Styles */
    #image-lightbox {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        cursor: zoom-out;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    #image-lightbox img {
        max-width: 95%;
        max-height: 95%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 0 40px rgba(13, 110, 253, 0.2);
    }

    #image-lightbox.active {
        display: flex;
    }
</style>

<!-- Hero friendly section -->
<header class="hero-container">
    <h1 class="welcome-text">{{ __('Hello! Welcome to Laracloak') }}</h1>
    <p class="subtitle mx-auto" style="max-width: 700px;">
        {{ __('Discover a simple and secure way to connect your tools without complications. Create smart bridges to your automations while maintaining absolute control.') }}
    </p>

    <!-- Visual Concept -->
    <div class="proxy-visual">
        <div class="proxy-node">🌐</div>
        <div class="proxy-line"></div>
        <div class="opaque-badge">{{ __('LARACLOAK') }}</div>
        <div class="proxy-line"></div>
        <div class="proxy-node">⚡</div>
    </div>

    <p class="text-muted" style="margin-bottom: 2rem; font-size: 0.95rem;">
        {{ __('Total protection for your n8n, Make, and external API endpoints.') }}
    </p>

    <div style="margin-top: 1rem;">
        <a href="https://github.com/fuseprods/laracloak" target="_blank" class="btn btn-github"
            style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 0.875rem 2rem; background: #24292f; color: #ffffff; border: 1px solid rgba(255,255,255,0.1); border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
            <svg height="24" width="24" viewBox="0 0 16 16" fill="currentColor" style="display: inline-block;">
                <path
                    d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z">
                </path>
            </svg>
            {{ __('View on GitHub') }}
        </a>
    </div>
</header>

<!-- Creative but consistent cards -->
<section class="features-section">
    <div class="glass-card">
        <div class="card-icon">🛡️</div>
        <h3 class="card-title">{{ __('Security without secrets') }}</h3>
        <p class="card-desc">
            {{ __('Receive and send data without ever exposing the real location of your servers. Laracloak acts as an invisibility layer for your infrastructure.') }}
        </p>
    </div>
    <div class="glass-card">
        <div class="card-icon">✨</div>
        <h3 class="card-title">{{ __('Total Simplicity') }}</h3>
        <p class="card-desc">
            {{ __('Your users interact with a friendly interface, while the system handles the complex part on the server.') }}
        </p>
    </div>
    <div class="glass-card">
        <div class="card-icon">🎨</div>
        <h3 class="card-title">{{ __('Open Learning') }}</h3>
        <p class="card-desc">
            {{ __('As an Open Source project, Laracloak is designed for you to explore how secure proxies and dynamic interfaces work.') }}
        </p>
    </div>
</section>

<!-- Visual Placeholders -->
<section class="mockup-wrapper">
    <div class="text-center mb-8">
        <h2 style="font-size: 2rem;">{{ __('Take a look inside') }}</h2>
    </div>

    <!-- First Mockup: Real Dashboard Screenshot -->
    <div class="browser-frame mb-16">
        <div class="browser-bar">
            <div class="browser-dot"></div>
            <div class="browser-dot"></div>
            <div class="browser-dot"></div>
        </div>
        <div class="mockup-content screenshot-mockup">
            <img src="{{ asset('img/dashboard-screenshot.png') }}" alt="{{ __('Dashboard Screenshot') }}"
                class="mockup-image" loading="lazy" onclick="openLightbox(this.src)">
        </div>
    </div>

    <!-- Spacer -->
    <div style="height: 10rem;"></div>

    <!-- Second Mockup: Interactive Forms Screenshot -->
    <div class="browser-frame">
        <div class="browser-bar">
            <div class="browser-dot"></div>
            <div class="browser-dot"></div>
            <div class="browser-dot"></div>
        </div>
        <div class="mockup-content screenshot-mockup">
            <img src="{{ asset('img/form-screenshot.png') }}" alt="{{ __('Form Editor Screenshot') }}"
                class="mockup-image" loading="lazy" onclick="openLightbox(this.src)">
        </div>
    </div>

    <!-- Final CTA -->
    <div class="text-center" style="margin-top: 6rem; padding-bottom: 4rem;">
        <h3 style="font-size: 1.5rem; margin-bottom: 2rem; color: var(--text-muted);">
            {{ __('Start building your secure bridges today.') }}
        </h3>
        <a href="https://github.com/fuseprods/laracloak" target="_blank" class="btn btn-github"
            style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 1rem 2.5rem; background: #24292f; color: #ffffff; border: 1px solid rgba(255,255,255,0.1); border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
            <svg height="24" width="24" viewBox="0 0 16 16" fill="currentColor" style="display: inline-block;">
                <path
                    d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z">
                </path>
            </svg>
            {{ __('Join the Project') }}
        </a>
    </div>
</section>

<!-- Lightbox structure -->
<div id="image-lightbox" onclick="this.classList.remove('active')">
    <img src="" id="lightbox-img" alt="Enlarged view">
</div>

<script>
    function openLightbox(src) {
        const lightbox = document.getElementById('image-lightbox');
        const img = document.getElementById('lightbox-img');
        if (lightbox && img) {
            img.src = src;
            lightbox.classList.add('active');
        }
    }

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const lightbox = document.getElementById('image-lightbox');
            if (lightbox) lightbox.classList.remove('active');
        }
    });
</script>