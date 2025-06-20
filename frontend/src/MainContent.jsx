import { useEffect, useState } from 'react'
import { motion } from 'framer-motion'

function MainContent() {
    const [session, setSession] = useState(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        fetch('/api/session-check.php', {
            credentials: 'include' // ❗ 確保 cookie 帶入
        })
            .then(res => res.json())
            .then(data => {
                if (data.loggedIn) {
                    setSession(data.session)
                }
                setLoading(false)
            })
            .catch(() => setLoading(false))
    }, [])

    if (loading) return <p>載入中...</p>

    return (
        <motion.div
            key="main"
            initial={{ opacity: 0, y: 50 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -50 }}
            transition={{ duration: 0.2 }}
            className="w-full bg-white/80 text-black px-10 py-10 rounded-xl shadow-lg backdrop-blur-lg"
        >
            <h1 className="text-2xl font-bold mb-6 text-left">歡迎來到 Bo2 主頁</h1>

            <div className="flex flex-col items-center gap-4 border-2 border-gray-300 rounded-lg py-8 px-8 sm:px-12 md:px-30 lg:px-45 xl:px-75 2xl:px-120 md:flex-row md:items-center md:justify-between">
                {/* <img
                    src="https://crafatar.com/renders/body/1c62a0f42337441c833560ca98e5c9e4"
                    alt="player"
                    className="w-32"
                /> */}

                <div className="flex flex-col items-center md:items-start gap-4 md:ml-8">
                    {!session && (
                        <>
                            <p className="text-gray-800">您尚未登入或未綁定玩家資料</p>
                            <a
                                href="https://discord.com/oauth2/authorize?client_id=1372575210229989466&response_type=code&redirect_uri=https%3A%2F%2Fmcc.bo2.tw%2Fapi%2Fauth%2Fdiscord%2Fcallback&scope=identify"
                                className="cursor-pointer px-6 py-3 bg-gray-600 text-white rounded hover:bg-gray-700 transition"
                            >
                                登入帳號
                            </a>
                        </>
                    )}

                    {session?.setup_status === '未設定' && (
                        <>
                            <p className="text-gray-800">🎉 帳號註冊成功，是否進行初次設定？</p>
                            <div className="flex gap-4">
                                <button
                                    className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    onClick={() => window.location.href = '/setup'}
                                >
                                    初次設定
                                </button>
                                <button
                                    className="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
                                    onClick={() => {
                                        fetch('/api/skip-setup.php').then(() => window.location.reload())
                                    }}
                                >
                                    跳過設定
                                </button>
                            </div>
                        </>
                    )}

                    {session?.setup_status === 'skip' && (
                        <>
                            <p className="text-gray-800">歡迎 {session.mccName}</p>
                        </>
                    )}

                    {session?.setup_status === '已設定' && (
                        <>
                            <p className="text-gray-800">歡迎 {session.mccName}</p>
                            <p className="text-green-700 font-bold">✅ 您的 MC UUID 已綁定</p>
                        </>
                    )}
                </div>
            </div>
        </motion.div>
    )
}

export default MainContent
