
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import "./stylesheets/inline-style-0.css"
import "./stylesheets/inline-style-1.css"
import App from './App.tsx'

const root = document.getElementById('root')

if (!root) {
  throw new Error('Documentation root element was not found')
}

createRoot(root).render(
  <StrictMode>
    <App />
  </StrictMode>
)
