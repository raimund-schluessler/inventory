import 'regenerator-runtime/runtime'
import VueTestUtils from '@vue/test-utils'
import { OC } from './OC.js'
import OCA from './OCA.js'

global.OC = new OC()
global.OCA = OCA

// Mock nextcloud translate functions
VueTestUtils.config.mocks.t = function(app, string) {
	return string
}
VueTestUtils.config.mocks.n = function(app, singular, plural, count) {
	return singular
}
