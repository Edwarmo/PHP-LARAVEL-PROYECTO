import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Label from '@/Components/ui/Label.vue'

describe('Label', () => {
  it('renders slot content', () => {
    const wrapper = mount(Label, {
      slots: { default: 'Nombre' }
    })
    expect(wrapper.text()).toBe('Nombre')
    expect(wrapper.element.tagName).toBe('LABEL')
  })

  it('applies for attribute', () => {
    const wrapper = mount(Label, {
      props: { htmlFor: 'email-input' },
      slots: { default: 'Email' }
    })
    expect(wrapper.attributes('for')).toBe('email-input')
  })
})
