import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Badge from '@/Components/ui/Badge.vue'

describe('Badge', () => {
  it('renders correctly', () => {
    const wrapper = mount(Badge, {
      slots: { default: 'badge text' }
    })
    expect(wrapper.text()).toBe('badge text')
    expect(wrapper.classes()).toContain('inline-flex')
  })

  it('renders different variants', () => {
    const variants = ['default', 'secondary', 'destructive', 'outline']
    
    variants.forEach(variant => {
      const wrapper = mount(Badge, {
        props: { variant }
      })
      expect(wrapper.classes()).toContain('inline-flex')
    })
  })
})