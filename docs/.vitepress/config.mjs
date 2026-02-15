export default {
    title: "Laracloak",
    description: "Secure Proxy & Dynamic Interface Platform",
    head: [['link', { rel: 'icon', href: '/img/logo.png' }]],
    appearance: 'dark',

    locales: {
        root: {
            label: 'English',
            lang: 'en',
            description: 'Secure Proxy & Dynamic Interface Platform',
            themeConfig: {
                nav: [
                    { text: 'Home', link: '/' },
                    { text: 'Guide', link: '/getting_started' },
                    { text: 'GitHub', link: 'https://github.com/fuseprods/laracloak' }
                ],
                sidebar: [
                    {
                        text: 'Getting Started',
                        items: [
                            { text: 'Quick Start', link: '/getting_started' },
                            { text: 'Installation & Configuration', link: '/configuration' },
                            { text: 'Design System', link: '/design_system' }
                        ]
                    },
                    {
                        text: 'Core Concepts',
                        items: [
                            { text: 'Upstream Proxy', link: '/upstream_proxy' },
                            { text: 'User Roles & Permissions', link: '/user_roles' },
                            { text: 'JWT Authentication', link: '/jwt_usage' },
                            { text: 'JWT Protocol Spec', link: '/jwt_protocol' }
                        ]
                    },
                    {
                        text: 'Feature Guides',
                        items: [
                            { text: 'Visual Page Editor', link: '/page_editor' },
                            { text: 'Dashboards', link: '/dashboards' },
                            { text: 'Dynamic Forms', link: '/forms' },
                            { text: 'Creating Pages', link: '/creating_pages' }
                        ]
                    },
                    {
                        text: 'Maintenance',
                        items: [
                            { text: 'Post-Mortem 2026-02-04', link: '/post-mortem_2026-02-04' }
                        ]
                    }
                ]
            }
        },
        es: {
            label: 'Español',
            lang: 'es',
            link: '/es/',
            description: 'Plataforma de Interfaz Dinámica y Proxy Seguro',
            themeConfig: {
                nav: [
                    { text: 'Inicio', link: '/es/' },
                    { text: 'Guía', link: '/es/getting_started' },
                    { text: 'GitHub', link: 'https://github.com/fuseprods/laracloak' }
                ],
                sidebar: [
                    {
                        text: 'Comenzando',
                        items: [
                            { text: 'Inicio Rápido', link: '/es/getting_started' },
                            { text: 'Instalación y Configuración', link: '/es/configuration' },
                            { text: 'Sistema de Diseño', link: '/es/design_system' }
                        ]
                    },
                    {
                        text: 'Conceptos Principales',
                        items: [
                            { text: 'Proxy Upstream', link: '/es/upstream_proxy' },
                            { text: 'Roles y Permisos', link: '/es/user_roles' },
                            { text: 'Autenticación JWT', link: '/es/jwt_usage' },
                            { text: 'Especificación Protocolo JWT', link: '/es/jwt_protocol' }
                        ]
                    },
                    {
                        text: 'Guías de Funcionalidades',
                        items: [
                            { text: 'Editor Visual de Páginas', link: '/es/page_editor' },
                            { text: 'Tableros (Dashboards)', link: '/es/dashboards' },
                            { text: 'Formularios Dinámicos', link: '/es/forms' },
                            { text: 'Creando Páginas', link: '/es/creating_pages' }
                        ]
                    },
                    {
                        text: 'Mantenimiento',
                        items: [
                            { text: 'Post-Mortem 2026-02-04', link: '/es/post-mortem_2026-02-04' }
                        ]
                    }
                ]
            }
        }
    },

    themeConfig: {
        logo: '/img/logo.png',
        socialLinks: [
            { icon: 'github', link: 'https://github.com/fuseprods/laracloak' }
        ]
    }
}
