import { OC } from './OC.js'
import OCA from './OCA.js'

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
