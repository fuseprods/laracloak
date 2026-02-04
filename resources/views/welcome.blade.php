@extends('layouts.guest')

@section('content')
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
        box-shadow: 0 0 20px rgba(0,0,0,0.3);
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
        0% { left: 0; opacity: 0; }
        50% { opacity: 1; }
        100% { left: 100%; opacity: 0; }
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
        background: rgba(0,0,0,0.2);
        padding: 0.75rem 1rem;
        display: flex;
        gap: 0.5rem;
        border-bottom: 1px solid var(--border);
    }

    .browser-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--border); }

    .mockup-content {
        aspect-ratio: 16/9;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-body) 100%);
        color: var(--text-muted);
    }
</style>

<div class="welcome-portal-container">
    <!-- Hero friendly section -->
    <header class="hero-container">
        <h1 class="welcome-text">¡Hola! Bienvenid@ a Laracloak</h1>
        <p class="subtitle mx-auto" style="max-width: 700px;">
            Descubre una forma sencilla y segura de conectar tus herramientas sin complicaciones. 
            Crea puentes inteligentes hacia tus automatizaciones manteniendo el control absoluto.
        </p>

        <!-- Visual Concept -->
        <div class="proxy-visual">
            <div class="proxy-node">🌐</div>
            <div class="proxy-line"></div>
            <div class="opaque-badge">PROXY SEGURO</div>
            <div class="proxy-line"></div>
            <div class="proxy-node">⚡</div>
        </div>

        <p class="text-muted" style="font-size: 0.95rem;">
            Protección total para tus endpoints de n8n, Make y APIs externas.
        </p>
    </header>

    @auth
    <!-- Back to portal for active users -->
    <div class="text-center mb-16">
        <a href="{{ route('panel.index') }}" class="btn btn-primary">
            {{ __('Go to my Dashboard') }}
        </a>
    </div>
    @endauth

    <!-- Creative but consistent cards -->
    <section class="features-section">
        <div class="glass-card">
            <div class="card-icon">🛡️</div>
            <h3 class="card-title">Seguridad sin secretos</h3>
            <p class="card-desc">
                Recibe y envía datos sin exponer jamás la ubicación real de tus servidores. Laracloak actúa como una capa de invisibilidad para tu infraestructura.
            </p>
        </div>
        <div class="glass-card">
            <div class="card-icon">✨</div>
            <h3 class="card-title">Simplicidad Total</h3>
            <p class="card-desc">
                Tus usuarios interactúan con una interfaz amigable, mientras el sistema se encarga de la parte compleja en el servidor.
            </p>
        </div>
        <div class="glass-card">
            <div class="card-icon">🎨</div>
            <h3 class="card-title">Aprendizaje Abierto</h3>
            <p class="card-desc">
                Como proyecto Open Source, Laracloak está diseñado para que explores cómo funcionan los proxies seguros y las interfaces dinámicas.
            </p>
        </div>
    </section>

    <!-- Visual Placeholders -->
    <section class="mockup-wrapper">
        <div class="text-center mb-8">
            <h2 style="font-size: 2rem;">Echa un vistazo por dentro</h2>
        </div>

        <div class="browser-frame mb-16">
            <div class="browser-bar">
                <div class="browser-dot"></div>
                <div class="browser-dot"></div>
                <div class="browser-dot"></div>
            </div>
            <div class="mockup-content">
                <div class="text-center p-4">
                    <p style="font-weight: 700; color: var(--primary);">[ Tu Dashboard aquí ]</p>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Visualiza de un vistazo todas tus automatizaciones.</p>
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
                    <p style="font-weight: 700; color: var(--primary);">[ Tu Interfaz Dinámica aquí ]</p>
                    <p style="font-size: 0.9rem; margin-top: 0.5rem;">Formularios generados automáticamente desde JSON.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection