FROM php:8.1-cli

WORKDIR /app

RUN apt-get update && apt-get install -y curl

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080"]
```

5. "Commit changes"

---

#### **สร้างไฟล์ `Procfile`:**

1. GitHub → "Add file" → "Create new file"
2. ชื่อไฟล์: `Procfile` (ไม่มี extension!)
3. เนื้อหา:
```
web: php -S 0.0.0.0:$PORT
