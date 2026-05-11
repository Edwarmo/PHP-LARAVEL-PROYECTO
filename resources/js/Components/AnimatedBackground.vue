<script setup>
import { onMounted, onUnmounted } from 'vue'

let ctx = null

onMounted(async () => {
  const { gsap } = await import('gsap')

  ctx = gsap.context(() => {
    document.querySelectorAll('.gsap-particle').forEach((el) => {
      gsap.to(el, {
        x: () => gsap.utils.random(-70, 70),
        y: () => gsap.utils.random(-70, 70),
        scale: () => gsap.utils.random(0.7, 1.9),
        opacity: () => gsap.utils.random(0.15, 0.95),
        duration: () => gsap.utils.random(3, 7),
        ease: 'sine.inOut',
        repeat: -1,
        yoyo: true,
        delay: () => gsap.utils.random(0, 2.5),
      })
    })

    document.querySelectorAll('.gsap-line').forEach((el, i) => {
      const len = el.getTotalLength?.() ?? 200
      gsap.set(el, { strokeDasharray: len, strokeDashoffset: len })
      gsap.to(el, {
        strokeDashoffset: 0,
        opacity: 0.25,
        duration: gsap.utils.random(2.5, 5),
        ease: 'power2.inOut',
        repeat: -1,
        yoyo: true,
        delay: i * 0.4,
      })
    })

    gsap.to('.gsap-grid', {
      opacity: 0.1,
      duration: 5,
      ease: 'sine.inOut',
      repeat: -1,
      yoyo: true,
    })

    gsap.to('.orb-a', { x: 70, y: -50, scale: 1.2, duration: 9,  ease: 'sine.inOut', repeat: -1, yoyo: true })
    gsap.to('.orb-b', { x: -60, y: 70, scale: 0.85, duration: 13, ease: 'sine.inOut', repeat: -1, yoyo: true })
    gsap.to('.orb-c', { x: 30,  y: 40, scale: 1.15, duration: 11, ease: 'sine.inOut', repeat: -1, yoyo: true })
  })
})

onUnmounted(() => {
  if (ctx) ctx.revert()
})
</script>

<template>
  <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none select-none">
    <div class="absolute inset-0" style="background:#06080f;"></div>

    <div class="orb-a absolute rounded-full" style="width:700px;height:700px;top:-180px;left:-120px;background:radial-gradient(circle,rgba(0,200,255,0.08) 0%,transparent 70%);filter:blur(50px);"></div>
    <div class="orb-b absolute rounded-full" style="width:800px;height:800px;bottom:-250px;right:-180px;background:radial-gradient(circle,rgba(180,255,0,0.06) 0%,transparent 70%);filter:blur(70px);"></div>
    <div class="orb-c absolute rounded-full" style="width:500px;height:500px;top:45%;left:45%;transform:translate(-50%,-50%);background:radial-gradient(circle,rgba(0,180,255,0.05) 0%,transparent 70%);filter:blur(35px);"></div>

    <div class="gsap-grid absolute inset-0" style="opacity:0.055;background-image:linear-gradient(rgba(0,220,255,0.7) 1px,transparent 1px),linear-gradient(90deg,rgba(0,220,255,0.7) 1px,transparent 1px);background-size:60px 60px;"></div>

    <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <radialGradient id="rg-c" cx="50%" cy="50%" r="50%">
          <stop offset="0%" stop-color="#00dcff" stop-opacity="1"/>
          <stop offset="100%" stop-color="#00dcff" stop-opacity="0"/>
        </radialGradient>
        <radialGradient id="rg-l" cx="50%" cy="50%" r="50%">
          <stop offset="0%" stop-color="#c8ff00" stop-opacity="1"/>
          <stop offset="100%" stop-color="#c8ff00" stop-opacity="0"/>
        </radialGradient>
        <filter id="gf">
          <feGaussianBlur stdDeviation="2.5" result="b"/>
          <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
        </filter>
      </defs>

      <line class="gsap-line" x1="8%"  y1="18%" x2="28%" y2="38%" stroke="#00dcff" stroke-width="0.8" opacity="0"/>
      <line class="gsap-line" x1="28%" y1="38%" x2="58%" y2="28%" stroke="#c8ff00" stroke-width="0.8" opacity="0"/>
      <line class="gsap-line" x1="58%" y1="28%" x2="78%" y2="58%" stroke="#00dcff" stroke-width="0.8" opacity="0"/>
      <line class="gsap-line" x1="78%" y1="58%" x2="88%" y2="78%" stroke="#c8ff00" stroke-width="0.8" opacity="0"/>
      <line class="gsap-line" x1="18%" y1="68%" x2="48%" y2="83%" stroke="#00dcff" stroke-width="0.8" opacity="0"/>
      <line class="gsap-line" x1="68%" y1="18%" x2="83%" y2="33%" stroke="#c8ff00" stroke-width="0.8" opacity="0"/>
      <line class="gsap-line" x1="42%" y1="12%" x2="72%" y2="22%" stroke="#00dcff" stroke-width="0.8" opacity="0"/>
      <line class="gsap-line" x1="5%"  y1="55%" x2="22%" y2="72%" stroke="#c8ff00" stroke-width="0.8" opacity="0"/>

      <circle class="gsap-particle" cx="8%"  cy="18%" r="2.5" fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="28%" cy="38%" r="3.5" fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="78%" cy="58%" r="3"   fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="88%" cy="78%" r="2"   fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="18%" cy="68%" r="2.8" fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="48%" cy="83%" r="2.2" fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="42%" cy="12%" r="3"   fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="5%"  cy="55%" r="2.5" fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="93%" cy="25%" r="2"   fill="url(#rg-c)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="52%" cy="48%" r="1.8" fill="url(#rg-c)" filter="url(#gf)"/>

      <circle class="gsap-particle" cx="58%" cy="28%" r="2.5" fill="url(#rg-l)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="68%" cy="18%" r="3.5" fill="url(#rg-l)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="63%" cy="73%" r="2.8" fill="url(#rg-l)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="33%" cy="88%" r="2"   fill="url(#rg-l)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="93%" cy="43%" r="3"   fill="url(#rg-l)" filter="url(#gf)"/>
      <circle class="gsap-particle" cx="22%" cy="72%" r="2"   fill="url(#rg-l)" filter="url(#gf)"/>

      <g class="gsap-particle" filter="url(#gf)" opacity="0.55">
        <line x1="47%" y1="43%" x2="53%" y2="43%" stroke="#00dcff" stroke-width="0.8"/>
        <line x1="50%" y1="40%" x2="50%" y2="46%" stroke="#00dcff" stroke-width="0.8"/>
        <circle cx="50%" cy="43%" r="1.5" fill="none" stroke="#00dcff" stroke-width="0.6"/>
      </g>
      <g class="gsap-particle" filter="url(#gf)" opacity="0.5">
        <line x1="22%" y1="28%" x2="28%" y2="28%" stroke="#c8ff00" stroke-width="0.8"/>
        <line x1="25%" y1="25%" x2="25%" y2="31%" stroke="#c8ff00" stroke-width="0.8"/>
        <circle cx="25%" cy="28%" r="1.5" fill="none" stroke="#c8ff00" stroke-width="0.6"/>
      </g>
      <g class="gsap-particle" filter="url(#gf)" opacity="0.5">
        <line x1="72%" y1="63%" x2="78%" y2="63%" stroke="#00dcff" stroke-width="0.8"/>
        <line x1="75%" y1="60%" x2="75%" y2="66%" stroke="#00dcff" stroke-width="0.8"/>
        <circle cx="75%" cy="63%" r="1.5" fill="none" stroke="#00dcff" stroke-width="0.6"/>
      </g>
    </svg>

    <div class="absolute inset-0" style="background:radial-gradient(ellipse at 50% 40%,transparent 25%,rgba(6,8,15,0.92) 100%);"></div>
    <div class="absolute bottom-0 left-0 right-0" style="height:1px;background:linear-gradient(90deg,transparent 0%,rgba(0,220,255,0.4) 50%,transparent 100%);"></div>
  </div>
</template>
