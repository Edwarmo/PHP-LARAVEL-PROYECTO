import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Dialog from '@/Components/ui/Dialog.vue'
import DialogContent from '@/Components/ui/DialogContent.vue'
import DialogDescription from '@/Components/ui/DialogDescription.vue'
import DialogFooter from '@/Components/ui/DialogFooter.vue'
import DialogHeader from '@/Components/ui/DialogHeader.vue'
import DialogTitle from '@/Components/ui/DialogTitle.vue'

const TeleportStub = {
  props: ['to'],
  template: '<div class="teleport-stub"><slot /></div>'
}

describe('Dialog', () => {
  it('renders dialog when open', () => {
    const wrapper = mount(Dialog, {
      props: { open: true },
      slots: { default: 'contenido' },
      global: { stubs: { Teleport: TeleportStub } }
    })
    expect(wrapper.text()).toContain('contenido')
  })

  it('does not show when closed', () => {
    const wrapper = mount(Dialog, {
      props: { open: false },
      slots: { default: 'contenido' },
      global: { stubs: { Teleport: TeleportStub } }
    })
    expect(wrapper.find('[class*="fixed"]').exists()).toBe(false)
  })

  it('renders with custom class', () => {
    const wrapper = mount(Dialog, {
      props: { open: true, class: 'custom-dialog' },
      global: { stubs: { Teleport: TeleportStub } }
    })
    expect(wrapper.html()).toContain('custom-dialog')
  })

  it('emits update:open on backdrop click', async () => {
    const wrapper = mount(Dialog, {
      props: { open: true },
      global: { stubs: { Teleport: TeleportStub } }
    })
    const backdrop = wrapper.find('[class*="fixed"]')
    await backdrop.trigger('click')
    expect(wrapper.emitted('update:open')).toBeTruthy()
    expect(wrapper.emitted('update:open')[0]).toEqual([false])
  })

  it('closes dialog via v-model', async () => {
    const wrapper = mount(Dialog, {
      props: { open: true },
      global: { stubs: { Teleport: TeleportStub } }
    })
    expect(wrapper.text()).toBeDefined()
    await wrapper.setProps({ open: false })
    expect(wrapper.find('[class*="fixed"]').exists()).toBe(false)
  })
})

describe('DialogContent', () => {
  it('renders slot content', () => {
    const wrapper = mount(DialogContent, {
      slots: { default: 'contenido' }
    })
    expect(wrapper.text()).toBe('contenido')
  })
})

describe('DialogDescription', () => {
  it('renders slot content', () => {
    const wrapper = mount(DialogDescription, {
      slots: { default: 'descripción' }
    })
    expect(wrapper.text()).toBe('descripción')
  })
})

describe('DialogFooter', () => {
  it('renders slot content', () => {
    const wrapper = mount(DialogFooter, {
      slots: { default: 'footer' }
    })
    expect(wrapper.text()).toBe('footer')
  })
})

describe('DialogHeader', () => {
  it('renders slot content', () => {
    const wrapper = mount(DialogHeader, {
      slots: { default: 'header' }
    })
    expect(wrapper.text()).toBe('header')
  })
})

describe('DialogTitle', () => {
  it('renders slot content', () => {
    const wrapper = mount(DialogTitle, {
      slots: { default: 'título' }
    })
    expect(wrapper.text()).toBe('título')
  })
})
