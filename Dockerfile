# 使用 ubuntu/apache2 作為基底
FROM ubuntu/apache2:latest

# 安裝 PHP 和 Node.js
RUN apt-get update && \
    apt-get install -y php libapache2-mod-php curl git && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# 啟用 Apache rewrite 模組
RUN a2enmod rewrite

# 複製 PHP API 到容器
COPY backend/api/ /var/www/html/api/

# 複製前端原始碼
COPY frontend/ /frontend/

# 在 frontend 目錄下安裝依賴並 build
WORKDIR /frontend
RUN npm install && npm run build

# 複製 build 後的前端檔案到 Apache 網頁根目錄
COPY /frontend/dist/ /var/www/html/

# 複製 setup 頁面 PHP 到 www/html/setup
COPY frontend/pages/setup/ /var/www/html/setup/

WORKDIR /var/www/html
EXPOSE 80