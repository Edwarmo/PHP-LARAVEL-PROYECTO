import { config } from '@vue/test-utils'

// Global test configuration
config.global.stubs = {
  transition: false,
  'transition-group': false
}