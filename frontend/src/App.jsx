import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'

function App() {
  const [count, setCount] = useState(0)

  return (
    <div className="flex flex-col min-h-screen font-sans text-gray-800">
      {/* 導覽列 */}
      <header className="bg-gray-900/80 text-white px-6 py-4 flex justify-between items-center">
        <div className="text-xl font-bold"><a href="/">Bo2 Minecraft Server</a></div>
        <nav className="space-x-6">
          <a href="https://www.bo2.tw" className="hover:underline">BO2首頁</a>
          <a href="today" className="hover:underline">公告</a>
          <a href="today" className="hover:underline">本屆模組包導覽</a>
          <a href="today" className="hover:underline">伺服器狀態</a>
          <a href="history" className="hover:underline">歷史</a>
          <a href="players" className="hover:underline">玩家紀錄</a>
          <a href="#" className="hover:underline">登入</a>
        </nav>
      </header>

      {/* 主要內容 */}
      <main className="flex-grow flex flex-col items-center justify-center text-center p-4">
        <div className="w-full max-w-3xl px-8 py-10 bg-black/80 rounded-2xl py-20">
          <img src="/bo2_Full_size.png" alt="logo" className="w-64 mx-auto mb-10" />
          <h1 className="text-4xl font-bold text-white mb-10">Bo2 Minecraft Server</h1>
          <p className="text-gray-300 mt-4 mb-10">since 2021</p>

          <a
           href="https://discord.gg/cCrQUAkqtf"
           className="inline-block px-6 py-3 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"
          >
           加入 Discord
          </a>
         </div>
      </main>

      {/* 頁尾 */}
      <footer className="bg-gray-200/60 text-center text-sm text-gray-800 py-4">
        IP: Ocean.bo2.tw
      </footer>
    </div>
  )
}

export default App
