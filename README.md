# 🤖 DOOHOON LINE BOT

LINE Bot ที่ดึงข้อมูลหุ้นจริง + ตอบคำถามทั่วไปด้วย AI

## ✨ ฟีเจอร์

- 📊 **ราคาหุ้นเรียลไทม์** - จาก Finnhub API
- 📰 **ข่าวหุ้นล่าสุด** - 7 วันล่าสุด
- 🤖 **วิเคราะห์ AI** - ใช้ OpenAI
- 💬 **ตอบคำถามทั่วไป** - เหมือน ChatGPT

## 📝 ตัวอย่าง
```
คุณ: NVDA
Bot: 📊 NVDA ราคา $140 | Uptrend | เป้าหมาย $160 | ซื้อ

คุณ: hello
Bot: สวัสดี! 👋 พิมพ์ชื่อหุ้น หรือถามเรื่องอื่น
```

## 🚀 Deploy ไป Render

1. Fork repository นี้
2. ไปที่ Render → Create Web Service
3. เลือก GitHub repository
4. ใส่ Environment Variables (3 ตัว)
5. Deploy!

## 🔑 API Keys

1. **LINE Channel Token** - https://developers.line.biz
2. **Finnhub API Key** - https://finnhub.io (ฟรี)
3. **OpenAI API Key** - https://openai.com (~$0.45/เดือน)

## 📁 โครงสร้าง
```
├── webhook.php
├── .env
├── .env.example
├── Procfile
├── render.yaml
├── composer.json
├── .gitignore
└── README.md
```

## ⚙️ Environment Variables
```
LINE_CHANNEL_TOKEN=your_token
FINNHUB_API_KEY=your_key
OPENAI_API_KEY=your_key
```

## 📊 Cost

| API | ราคา |
|-----|------|
| LINE | ฟรี |
| Finnhub | ฟรี |
| OpenAI | ~$0.45/เดือน |
| Render | ฟรี (Free Tier) |

## 🔗 Setup Webhook

ใน LINE Developers Console:
```
Webhook URL: https://your-app.onrender.com/webhook.php
```

## 🐛 Troubleshooting

### Bot ไม่ตอบ
1. ตรวจ Webhook URL ถูกต้องไหม
2. ตรวจ Environment Variables ใน Render
3. ดู Logs ใน Render Dashboard