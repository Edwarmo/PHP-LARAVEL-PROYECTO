import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Button from '@/Components/ui/Button.vue'

describe('Button', () => {
  it('renders correctly with default props', () => {
    const wrapper = mount(Button, {
      slots: { default: 'Click me' }
    })
    expect(wrapper.text()).toBe('Click me')
    expect(wrapper.classes()).toContain('inline-flex')
  })

  it('renders different variants', () => {
    const variants = ['default', 'outline', 'secondary', 'ghost', 'destructive', 'link']
    
    variants.forEach(variant => {
      const wrapper = mount(Button, {
        props: { variant }
      })
      expect(wrapper.classes()).toContain('inline-flex')
    })
  })

  it('renders different sizes', () => {
    const sizes = ['default', 'xs', 'sm', 'lg', 'icon']
    
    sizes.forEach(size => {
      const wrapper = mount(Button, {
        props: { size }
      })
      expect(wrapper.classes()).toContain('inline-flex')
    })
  })

  it('emits click event', async () => {
    const wrapper = mount(Button)
    await wrapper.trigger('click')
    expect(wrapper.emitted()).toHaveProperty('click')
  })

  it('applies disabled state', () => {
    const wrapper = mount(Button, {
      props: { disabled: true }
    })
    expect(wrapper.attributes('disabled')).toBeDefined()
  })
})