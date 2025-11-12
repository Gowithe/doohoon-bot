git add news-scheduler.php
git commit -m "Add news scheduler"
git push origin main
```

### **2️⃣ ตั้ง Cron Job (EasyCron)**

ไปที่ https://www.easycron.com/

1. Sign Up (ฟรี)
2. Create 2 Cron Jobs:

**Cron 1 - เช้า 07:00:**
```
URL: https://doohoon-bot.onrender.com/news-scheduler.php
Time: 0 7 * * * (Thailand)
```

**Cron 2 - เย็น 18:00:**
```
URL: https://doohoon-bot.onrender.com/news-scheduler.php
Time: 0 18 * * * (Thailand)
```

3. Save & Enable

### **3️⃣ ทดสอบ**
- EasyCron → Execute
- Check LINE ว่าได้ข่าวไหม

---

## 🎯 **ระบบทำงาน:**
```
EasyCron 07:00 & 18:00
    ↓
ดึงข่าว (Finnhub)
    ↓
สรุปภาษาไทย (OpenAI)
    ↓
ส่ง LINE Broadcast ให้ทุกคน follow
```

---

## 📝 **ตัวอย่างข่าวที่จะได้:**
```
📰 ข่าวหุ้นเช้า

ตลาดหุ้นสหรัฐฯ ปิดสูงขึ้น 2.5%
โดย Tech stocks นำทาง...
[ส่งให้ทุกคน follow bot]