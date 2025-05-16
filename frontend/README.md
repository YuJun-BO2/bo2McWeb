# Bo2 Minecraft Web Frontend

本部分為 Bo2 社群網站的前端部分，使用 [React](https://react.dev/) + [Vite](https://vitejs.dev/) 建構，並搭配 [Tailwind CSS](https://tailwindcss.com/) 做樣式處理。

## 安裝與開發

開發前，請確保已安裝Node.js。

```bash
npm install
npm run dev
```

## 建構

假如像要建構能夠部屬在IIS等伺服器上的檔案請使用以下指令

```bash
npm run build
```

之後將 dist/ 資料夾內容部署到 wwwroot 或其他靜態主機