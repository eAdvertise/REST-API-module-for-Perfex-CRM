import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import svgr from 'vite-plugin-svgr'
import tailwindcss from '@tailwindcss/vite'

const repairCapturedReturns = () => ({
  name: 'repair-captured-returns',
  enforce: 'pre',
  transform(code, id) {
    if (!id.endsWith('.tsx')) return null

    const repaired = code.replace(/return\s*\n(\s*)\{/g, 'return void 0 ||\n$1{')
    return repaired === code ? null : { code: repaired, map: null }
  },
})

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return {
    plugins: [
      repairCapturedReturns(),
      tailwindcss(),
      svgr({
        svgrOptions: {
          typescript: false,
        },
      }),
      react(),
    ],
    base: './',
    build: {
      minify: false,
      cssMinify: false,
      sourcemap: false,
      target: 'esnext',
      rollupOptions: {
        treeshake: false,
        output: {
          manualChunks: undefined,
          inlineDynamicImports: true,
        },
      },
      reportCompressedSize: false,
      chunkSizeWarningLimit: 10000,
      assetsDir: '',
    },
    esbuild: {
      target: 'esnext',
      minify: false,
    },
    optimizeDeps: {
      force: true,
      include: ['react', 'react-dom'],
    },
  }
})
