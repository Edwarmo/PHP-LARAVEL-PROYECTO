import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Card from '@/Components/ui/Card.vue'
import CardHeader from '@/Components/ui/CardHeader.vue'
import CardTitle from '@/Components/ui/CardTitle.vue'
import CardDescription from '@/Components/ui/CardDescription.vue'
import CardContent from '@/Components/ui/CardContent.vue'
import CardFooter from '@/Components/ui/CardFooter.vue'

describe('Card', () => {
  it('renders correctly', () => {
    const wrapper = mount(Card, {
      slots: { default: 'Card content' }
    })
    expect(wrapper.text()).toBe('Card content')
    expect(wrapper.classes()).toContain('flex')
    expect(wrapper.classes()).toContain('flex-col')
  })
})

describe('CardHeader', () => {
  it('renders correctly', () => {
    const wrapper = mount(CardHeader, {
      slots: { default: 'Header content' }
    })
    expect(wrapper.text()).toBe('Header content')
  })
})

describe('CardTitle', () => {
  it('renders correctly', () => {
    const wrapper = mount(CardTitle, {
      slots: { default: 'Title text' }
    })
    expect(wrapper.text()).toBe('Title text')
    expect(wrapper.classes()).toContain('font-heading')
  })
})

describe('CardDescription', () => {
  it('renders correctly', () => {
    const wrapper = mount(CardDescription, {
      slots: { default: 'Description text' }
    })
    expect(wrapper.text()).toBe('Description text')
    expect(wrapper.classes()).toContain('text-muted-foreground')
  })
})

describe('CardContent', () => {
  it('renders correctly', () => {
    const wrapper = mount(CardContent, {
      slots: { default: 'Content text' }
    })
    expect(wrapper.text()).toBe('Content text')
  })
})

describe('CardFooter', () => {
  it('renders correctly', () => {
    const wrapper = mount(CardFooter, {
      slots: { default: 'Footer content' }
    })
    expect(wrapper.text()).toBe('Footer content')
  })
})