import { useState, useEffect } from 'react'
import './App.css'
import { AnimatePresence } from 'framer-motion';
import Entrance from './Entrance.jsx'
import MainContent from './MainContent.jsx'

function App() {
  const [entered, setEntered] = useState(false)
  const [loading, setLoading] = useState(true) // 新增 loading 狀態

  useEffect(() => {
    fetch('/api/session-check.php', { credentials: 'include' })
      .then(res => res.json())
      .then(data => {
        setEntered(!!data.loggedIn)
        setLoading(false)
      })
      .catch(() => setLoading(false))
  }, [])

  if (loading) {
    return (
      <div className="flex flex-col min-h-screen font-sans text-gray-800 items-center justify-center">
        <p>載入中...</p>
      </div>
    )
  }

  return (
    <div className="flex flex-col min-h-screen font-sans text-gray-800">
      {/* ...existing code... */}
      <main className="flex-grow flex flex-col items-center justify-center text-center p-4">
        <AnimatePresence mode="wait">
          {!entered ? (
            <Entrance key="entrance" onEnter={() => setEntered(true)} />
          ) : (
            <MainContent key="main" />
          )}
        </AnimatePresence>
      </main>

      {/* 頁尾 */}
      <footer className="bg-gray-200/60 text-center text-sm text-gray-800 py-4">
        IP: Ocean.bo2.tw
      </footer>
    </div>
  )
}

export default App
