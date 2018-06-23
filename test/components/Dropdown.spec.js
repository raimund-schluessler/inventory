import { shallowMount } from '@vue/test-utils'
import Dropdown from '@/components/Dropdown.vue'

describe('Dropdown.vue', () => {
	it('toggles open state', () => {
		const wrapper = shallowMount(Dropdown);
		wrapper.find('.app-navigation-entry-utils').trigger('click');
		expect(wrapper.vm.open).toBe(true);
	})
	
	it('closes on background click', () => {
		const wrapper = shallowMount(Dropdown);
		wrapper.find('.app-navigation-entry-utils').trigger('click');
		document.body.click();
		expect(wrapper.vm.open).toBe(false);
	})
})
