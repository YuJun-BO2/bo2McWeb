# Bo2 Minecraft Web Frontend

本部分為 Bo2 社群網站的前端部分，使用 [React](https://react.dev/) + [Vite](https://vitejs.dev/) 建構，並搭配 [Tailwind CSS](https://tailwindcss.com/) 做樣式處理。

## 安裝與開發

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

## Github Page 部屬

本專案前端會自動部署至 GitHub Pages，僅需至 GitHub 頁面中的 Settings > Pages 中設定即可。
若你是從本專案 fork 而來，建議完成建構後（npm run build），再啟用 GitHub Pages 並選擇：

- 分支：gh-pages

- 資料夾：/(root)

GitHub 會自動為你建立一個公開的預覽頁面。