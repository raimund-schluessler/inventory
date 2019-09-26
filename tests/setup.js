import 'regenerator-runtime/runtime'
import VueTestUtils from '@vue/test-utils'

global.expect = require('expect')

// Mock nextcloud translate functions
VueTestUtils.config.mocks["t"] = function(app, string) {
	return string
}
VueTestUtils.config.mocks["n"] = function(app, singular, plural, count) {
	return singular
}
