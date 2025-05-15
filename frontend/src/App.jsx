import { useState } from 'react'
import './App.css'
import Entrance from './Entrance.jsx'

function App() {
  const [count, setCount] = useState(0)

  return (
    <div className="flex flex-col min-h-screen font-sans text-gray-800">
      {/* 導覽列 */}
      <header className="bg-gray-900/80 text-white px-6 py-4 flex justify-between items-center">
        <div className="text-xl font-bold"><a href="/">Bo2 Minecraft Server</a></div>
        <nav className="space-x-6">
          <a href="announcement" className="hover:underline">公告</a>
          <a href="status" className="hover:underline">狀態</a>
          <a href="support" className="hover:underline">支援</a>
        </nav>
      </header>

      {/* 主要內容 */}
      <main className="flex-grow flex flex-col items-center justify-center text-center p-4">
        {!entered ? <Entrance onEnter={() => setEntered(true)} /> : <MainContent />}
      </main>

      {/* 頁尾 */}
      <footer className="bg-gray-200/60 text-center text-sm text-gray-800 py-4">
        IP: Ocean.bo2.tw
      </footer>
    </div>
  )
}

export default App
