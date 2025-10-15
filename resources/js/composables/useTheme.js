import { ref, watch } from 'vue'

const theme = ref('system')
const primaryColor = ref('#2563eb')

function applyTheme(val) {
  if (val === 'dark') {
    document.documentElement.classList.add('dark')
    document.documentElement.classList.remove('light')
  } else if (val === 'light') {
    document.documentElement.classList.add('light')
    document.documentElement.classList.remove('dark')
  } else {
    document.documentElement.classList.remove('dark')
    document.documentElement.classList.remove('light')
  }
}

function applyPrimaryColor(color) {
  document.documentElement.style.setProperty('--primary', color)
}

watch(theme, (val) => {
  localStorage.setItem('ldns_theme', val)
  applyTheme(val)
})
watch(primaryColor, (val) => {
  localStorage.setItem('ldns_primary_color', val)
  applyPrimaryColor(val)
})

export function useTheme() {
  return {
    theme,
    primaryColor,
    setTheme: (val) => { theme.value = val; applyTheme(val) },
    setPrimaryColor: (val) => { primaryColor.value = val; applyPrimaryColor(val) },
    applyTheme,
    applyPrimaryColor,
  }
}
