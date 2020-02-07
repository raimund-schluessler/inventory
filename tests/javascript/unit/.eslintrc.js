module.exports = {
	env: {
		jest: true
	},
	// Do to https://github.com/benmosher/eslint-plugin-import/issues/1451
	rules: {
		"node/no-missing-import": ["off"],
		"import/no-unresolved": ["off"],
	}
}
