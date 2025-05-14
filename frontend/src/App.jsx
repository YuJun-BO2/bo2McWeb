import { useState } from 'react'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'

function App() {
  const [count, setCount] = useState(0)

  return (
    <div>
      <h1 class="text-4xl text-black-500 font-bold">Bo2 Minecraft Server</h1>
      <p className="mt-4 text-blue-600">如果這段文字是藍色，表示 Tailwind 正常運作。</p>
    </div>
  )
}

export default App
