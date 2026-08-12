
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import "./stylesheets/inline-style-0.css"
import "./stylesheets/inline-style-1.css"
import App from './App.tsx'

createRoot(document.querySelector('html')).render(
  <StrictMode>
    <App />
  </StrictMode>
)
   