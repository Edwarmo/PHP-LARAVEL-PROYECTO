import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Input from '@/Components/ui/Input.vue'

describe('Input', () => {
  it('renders with default type', () => {
    const wrapper = mount(Input)
    expect(wrapper.element.tagName).toBe('INPUT')
    expect(wrapper.attributes('type')).toBe('text')
  })

  it('renders with custom type', () => {
    const wrapper = mount(Input, {
      props: { type: 'email' }
    })
    expect(wrapper.attributes('type')).toBe('email')
  })

  it('renders placeholder', () => {
    const wrapper = mount(Input, {
      props: { placeholder: 'Ingresa tu email' }
    })
    expect(wrapper.attributes('placeholder')).toBe('Ingresa tu email')
  })

  it('binds modelValue', () => {
    const wrapper = mount(Input, {
      props: { modelValue: 'test' }
    })
    expect(wrapper.element.value).toBe('test')
  })

  it('emits update:modelValue on input', async () => {
    const wrapper = mount(Input)
    await wrapper.setValue('nuevo valor')
    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
  })

  it('applies disabled state', () => {
    const wrapper = mount(Input, {
      props: { disabled: true }
    })
    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('applies required state', () => {
    const wrapper = mount(Input, {
      props: { required: true }
    })
    expect(wrapper.attributes('required')).toBeDefined()
  })
})
