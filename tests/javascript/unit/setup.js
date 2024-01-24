import { OC } from './OC.js'
import OCA from './OCA.js'
// eslint-disable-next-line n/no-unpublished-import
import 'regenerator-runtime/runtime'

global.OC = new OC()
global.OCA = OCA
global.URL.createObjectURL = () => {}

// Mock nextcloud translate functions
global.t = function(app, string) {
	return string
}
global.n = function(app, singular, plural, count) {
	return singular
}
