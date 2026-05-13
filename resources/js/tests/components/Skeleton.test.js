import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Skeleton from '@/Components/ui/Skeleton.vue'

describe('Skeleton', () => {
  it('renders with default variant (text)', () => {
    const wrapper = mount(Skeleton)
    expect(wrapper.classes()).toContain('animate-pulse')
  })

  it('renders with card variant', () => {
    const wrapper = mount(Skeleton, {
      props: { variant: 'card' }
    })
    expect(wrapper.classes()).toContain('animate-pulse')
  })

  it('renders with avatar variant', () => {
    const wrapper = mount(Skeleton, {
      props: { variant: 'avatar' }
    })
    expect(wrapper.classes()).toContain('rounded-full')
  })

  it('renders with button variant', () => {
    const wrapper = mount(Skeleton, {
      props: { variant: 'button' }
    })
    expect(wrapper.classes()).toContain('animate-pulse')
  })

  it('accepts custom class', () => {
    const wrapper = mount(Skeleton, {
      props: { class: 'custom-class' }
    })
    expect(wrapper.classes()).toContain('custom-class')
  })
})
