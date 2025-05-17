import { motion } from 'framer-motion';

function MainContent() {
    return (
        <motion.div
            key="main"
            initial={{ opacity: 0, y: 50 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -50 }}
            transition={{ duration: 0.2 }}
            className="w-full bg-white/90 text-black px-10 py-10 rounded-xl shadow-lg"
        >
            <h1 className="text-2xl font-bold mb-6 text-left">歡迎來到 Bo2 主頁</h1>

            <div className="flex flex-col items-center gap-4 border-2 border-gray-300 rounded-lg py-8 px-8 sm:px-12 md:px-30 lg:px-45 xl:px-75 2xl:px-120 md:flex-row md:items-center md:justify-between">
                {/* 左側：圖片 */}
                <img
                    src="https://crafatar.com/renders/body/1c62a0f42337441c833560ca98e5c9e4"
                    alt="player"
                    className="w-32"
                />

                {/* 右側：文字 + 按鈕 */}
                <div className="flex flex-col items-center md:items-start gap-4 md:ml-8">
                    <p className="text-gray-800">您似乎尚未登入或未綁定玩家資料</p>

                    <a  href="https://discord.com/oauth2/authorize?client_id=1372575210229989466&response_type=code&redirect_uri=https%3A%2F%2Fmcc.bo2.tw%2Fapi%2Fauth%2Fdiscord%2Fcallback&scope=identify"
                        className="cursor-pointer px-6 py-3 bg-gray-600 text-white rounded hover:bg-gray-700 transition"
                    >
                        登入帳號
                    </a>
                </div>
            </div>
        </motion.div>
    )
}

export default MainContent
