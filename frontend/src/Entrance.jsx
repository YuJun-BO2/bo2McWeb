// Entrance.jsx
import { useState } from 'react'

function Entrance({ onEnter }) {
  return (
    <div id="entrance" className="w-full max-w-3xl px-8 py-10 bg-black/80 rounded-2xl py-20">
      <img src="bo2_Full_size.png" alt="logo" className="w-64 mx-auto mb-10" />
      <h1 className="text-4xl font-bold text-white mb-5">Bo2 Minecraft Community</h1>
      <p className="text-gray-300 mt-4 mb-10">since 2021</p>

      <div className="flex flex-col">
        <a
          onClick={onEnter}
          className="cursor-pointer inline-block flex-1 px-6 py-3 mx-10 bg-gray-600 text-white rounded hover:bg-gray-700 transition"
        >
          進入網站
        </a>
        <a
          href="#"
          className="inline-block flex-1 px-6 py-3 mx-10 my-8 bg-green-600 text-white rounded hover:bg-green-700 transition"
        >
          登入網站
        </a>
        <a
          href="https://discord.gg/cCrQUAkqtf"
          className="inline-block flex-1 px-6 py-3 mx-10 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"
        >
          加入 Discord
        </a>
      </div>
    </div>
  )
}

export default Entrance
