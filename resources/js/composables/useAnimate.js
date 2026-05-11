import { onMounted, onUnmounted } from 'vue'

export function useAnimate(selector, config = {}) {
  let ctx = null

  onMounted(async () => {
    const { gsap } = await import('gsap')
    ctx = gsap.context(() => {
      document.querySelectorAll(selector).forEach((el) => {
        gsap.to(el, {
          x: () => config.x ?? gsap.utils.random(-70, 70),
          y: () => config.y ?? gsap.utils.random(-70, 70),
          scale: () => config.scale ?? gsap.utils.random(0.7, 1.9),
          opacity: () => config.opacity ?? gsap.utils.random(0.15, 0.95),
          duration: () => config.duration ?? gsap.utils.random(3, 7),
          ease: config.ease ?? 'sine.inOut',
          repeat: -1,
          yoyo: true,
          delay: () => config.delay ?? gsap.utils.random(0, 2.5),
        })
      })
    })
  })

  onUnmounted(() => {
    if (ctx) ctx.revert()
  })
}

export function useGsapTimeline() {
  let ctx = null

  onMounted(async () => {
    const { gsap } = await import('gsap')
    ctx = gsap.context(() => {})
  })

  onUnmounted(() => {
    if (ctx) ctx.revert()
  })

  return { ctx }
}
