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
        <div class="opaque-badge">{{ __('SAFE PROXY') }}</div>
        <div class="proxy-line"></div>
        <div class="proxy-node">⚡</div>
    </div>

    <p class="text-muted" style="font-size: 0.95rem;">
        {{ __('Total protection for your n8n, Make, and external API endpoints.') }}
    </p>
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

    <div class="browser-frame mb-16">
        <div class="browser-bar">
            <div class="browser-dot"></div>
            <div class="browser-dot"></div>
            <div class="browser-dot"></div>
        </div>
        <div class="mockup-content">
            <div class="text-center p-4">
                <p style="font-weight: 700; color: var(--primary);">[ {{ __('Your Dashboard here') }} ]</p>
                <p style="font-size: 0.9rem; margin-top: 0.5rem;">
                    {{ __('Visualize all your automations at a glance.') }}</p>
            </div>
        </div>
    </div>

    <div class="browser-frame">
        <div class="browser-bar">
            <div class="browser-dot"></div>
            <div class="browser-dot"></div>
            <div class="browser-dot"></div>
        </div>
        <div class="mockup-content">
            <div class="text-center p-4">
                <p style="font-weight: 700; color: var(--primary);">[ {{ __('Your Dynamic Interface here') }} ]</p>
                <p style="font-size: 0.9rem; margin-top: 0.5rem;">{{ __('Forms automatically generated from JSON.') }}
                </p>
            </div>
        </div>
    </div>
</section>