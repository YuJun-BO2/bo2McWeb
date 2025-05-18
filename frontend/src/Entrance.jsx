// Entrance.jsx
import { useState } from 'react'
import { motion } from 'framer-motion';

function Entrance({ onEnter }) {
    return (
        <motion.div 
            key="entrance"
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.95 }}
            transition={{ duration: 0.2 }}
            className="w-full max-w-3xl px-8 py-10 bg-black/80 rounded-2xl py-20 shadow-lg backdrop-blur-sm"
        >
            <img src="/bo2_Full_size.png" alt="logo" className="w-64 mx-auto mb-10 rounded-2xl" />
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
                href="https://github.com/YuJun-BO2/bo2McWeb/"
                className="inline-block flex-1 px-6 py-3 mx-10 my-8 bg-gray-200 text-gray-900 rounded hover:bg-gray-400 transition"
                >
                    加入開發
                </a>
                <a
                href="https://discord.gg/cCrQUAkqtf"
                className="inline-block flex-1 px-6 py-3 mx-10 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition"
                >
                    加入 Discord
                </a>
            </div>
        </motion.div>
  )
}

export default Entrance
