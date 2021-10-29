import { config } from '@vue/test-utils'
import { OC } from './OC.js'
import OCA from './OCA.js'
// eslint-disable-next-line node/no-unpublished-import
import 'regenerator-runtime/runtime'

global.OC = new OC()
global.OCA = OCA
global.URL.createObjectURL = () => {}

// Mock nextcloud translate functions
config.mocks.t = function(app, string) {
	return string
}
config.mocks.n = function(app, singular, plural, count) {
	return singular
}
