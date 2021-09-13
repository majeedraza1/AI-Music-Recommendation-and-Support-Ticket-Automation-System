module.exports = {
	// prefix: 'tw-',
	purge: {
		content: [
			'./resources/**/*.vue',
			'./resources/**/*.js',
			'./resources/**/*.jsx',
			'./resources/**/*.scss',
		]
	},
	darkMode: false, // or 'media' or 'class'
	theme: {
		extend: {
			colors: {
				"primary": 'var(--shapla-primary, #d26e4b)',
				"on-primary": 'var(--shapla-on-primary, #ffffff)',
			},
		},
		screens: {
			'sm': {'max': '767px'},
			'md': {'min': '768px'},
			'lg': {'min': '1024px'},
			'xl': {'min': '1280px'},
			'2xl': {'min': '1536px'},
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
