import React from 'react'
import ReactDOM from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import App from './App.jsx'
import './index.css'
import * as Sentry from '@sentry/react'
// Add this line near the top of your main.jsx
import 'bootstrap/dist/css/bootstrap.min.css';

// Initialize Sentry for error tracking
// Only enable in production environment
if (import.meta.env.PROD) {
  Sentry.init({
    dsn: import.meta.env.VITE_SENTRY_DSN,
    integrations: [
      new Sentry.BrowserTracing(),
      new Sentry.Replay()
    ],
    // Set tracesSampleRate to 1.0 to capture 100% of transactions for performance monitoring
    // We recommend adjusting this value in production
    tracesSampleRate: 0.2,
    // Capture Replay for 10% of all sessions
    replaysSessionSampleRate: 0.1,
    // Capture Replay for 100% of sessions with an error
    replaysOnErrorSampleRate: 1.0,
  });
}

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <BrowserRouter>
      <App />
    </BrowserRouter>
  </React.StrictMode>,
)
