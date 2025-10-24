tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#0B8A3A',
                'primary-dark': '#00582A',
                accent: '#FFD400',
                'accent-dark': '#E6BF00',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
            spacing: {
                '128': '32rem',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-in': 'slideIn 0.3s ease-out',
                'bounce-subtle': 'bounceSubtle 2s infinite',
            },
            boxShadow: {
                'elegant': '0 25px 50px -12px rgba(0, 0, 0, 0.08)',
                'elegant-lg': '0 35px 60px -12px rgba(0, 0, 0, 0.12)',
            }
        }
    }
}
