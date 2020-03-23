import { config } from '@vue/test-utils'
import OC from './OC.js'
import OCA from './OCA.js'

global.OC = OC
global.OCA = OCA

// Mock nextcloud translate functions
config.mocks.t = function(app, string) {
	return string
}
config.mocks.n = function(app, singular, plural, count) {
	return singular
}
