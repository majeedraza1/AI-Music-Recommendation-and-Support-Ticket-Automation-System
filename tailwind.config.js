module.exports = {
    content: [
        './resources/**/*.{vue,js,jsx,scss,ts,tsx}',
    ],
    theme: {
        extend: {
            colors: {
                "primary": 'var(--shapla-primary, #d26e4b)',
                "on-primary": 'var(--shapla-on-primary, #ffffff)',
            },
        },
    },
    variants: {
        extend: {},
    },
    plugins: [],
    corePlugins: {
        preflight: false,
    }
}
