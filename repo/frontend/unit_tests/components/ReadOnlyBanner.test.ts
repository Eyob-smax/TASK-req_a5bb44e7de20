import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ReadOnlyBanner from '../../src/components/ReadOnlyBanner.vue'

describe('ReadOnlyBanner', () => {
  it('renders read-only message', () => {
    const wrapper = mount(ReadOnlyBanner)
    expect(wrapper.text()).toContain('read-only mode')
    expect(wrapper.attributes('role')).toBe('alert')
  })

  it('has aria-live assertive', () => {
    const wrapper = mount(ReadOnlyBanner)
    expect(wrapper.attributes('aria-live')).toBe('assertive')
  })
})
