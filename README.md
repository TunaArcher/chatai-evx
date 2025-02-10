# ระบบ AutoConX 

https://autoconx.app/

## 🎯 ภาพรวม

พัฒนาด้วยเทคโนโลยีสมัยใหม่เพื่อรองรับการโต้ตอบแบบเรียลไทม์ การยืนยันตัวตนที่ราบรื่น และการประมวลผลการชำระเงินที่ปลอดภัย ระบบถูกพัฒนาด้วย PHP 8.x และ CodeIgniter 4 (CI4) เป็นเฟรมเวิร์คหลัก พร้อมด้วย Node.js สำหรับการสื่อสารแบบ WebSocket

## 🛠️เทคโนโลยีที่ใช้

- **Backend:** PHP 8.x พร้อม CodeIgniter 4
- **WebSocket:** Node.js
- **ระบบปฏิบัติการ:** Ubuntu
- **การจัดการคิว:** RabbitMQ
- **มาตรฐาน API:** Open API
- **การยืนยันตัวตน:** OAuth 2.0 (Facebook, Google)
- **การชำระเงิน:** Stripe Payment Gateway

## 🚀 คุณสมบัติ 

- **การยืนยันตัวตนด้วย OAuth 2.0:** รองรับการเข้าสู่ระบบผ่าน Facebook และ Google
- **เชื่อมต่อแพลตฟอร์มต่าง ๆ** Line, Facebook, WhatsApp, IG
- **การสื่อสารแบบเรียลไทม์:** ใช้ WebSocket ผ่าน Node.js สำหรับการส่งข้อความและการแจ้งเตือนทันที
- **การประมวลผลการชำระเงิน:** รวมเข้ากับ Stripe เพื่อการทำธุรกรรมที่ปลอดภัย
- **รองรับ Open API:** ช่วยให้สามารถเชื่อมต่อกับระบบภายนอกได้
- **การจัดการคิว:** ใช้ RabbitMQ ในการประมวลผลงานแบบอะซิงโครนัส

## 🔧 การติดตั้ง

### ⚡ข้อกำหนดเบื้องต้น

ตรวจสอบให้แน่ใจว่าคุณได้ติดตั้งโปรแกรมต่อไปนี้บนเซิร์ฟเวอร์ของคุณ:

- PHP 8.x
- CodeIgniter 4
- Node.js
- RabbitMQ
- MySQL หรือฐานข้อมูลที่รองรับ
- Composer
- NPM

### 📌 ขั้นตอนการติดตั้ง

1. **โคลนโครงการ:**
   ```sh
   git clone <repository_url>
   cd <project_directory>
   ```
2. **ติดตั้งแพ็คเกจของ PHP:**
   ```sh
   composer install
   ```
3. **ตั้งค่าคอนฟิก:**
   - คัดลอกไฟล์ `.env.example` เป็น `.env` และอัปเดตค่าที่จำเป็น
4. **เรียกใช้คำสั่ง Migration สำหรับฐานข้อมูล:**
   ```sh
   php spark migrate
   ```
5. **เริ่มบริการ RabbitMQ:**
   ```sh
   sudo service rabbitmq-server start
   ```
6. **เริ่มเซิร์ฟเวอร์ WebSocket: อยู่อีก Repository -> https://github.com/TunaArcher/chatai-socket**

## 📌 การใช้งาน

- เข้าถึงระบบผ่าน `http://localhost:8080`
- ตรวจสอบให้แน่ใจว่า RabbitMQ และ WebSocket ทำงานเพื่อรองรับฟีเจอร์แบบเรียลไทม์
- ตั้งค่าคีย์ API สำหรับ OAuth และ Stripe ก่อนใช้งานจริง

## 🔜 อัพเดทล่าสุด
- Plan Pro / Year
- รับข้อความแบบ เสียง (Line, FB)
- History Context (15 Context)

## 🔜 กำลังทำ
- Traning By File (PDF, Excel)

## 🔜 ที่ต้องทำแก้ไข
- Traning By Text หลังจาก Builder Prompt ยังไม่สมบูรณ์ ทำให้ตอบเพี้ยน

## 🔜 ที่ต้องทำต่อ
- จัดการ Profile
- รับข้อความแบบ เสียง (IG, WhatsApp)
- Facebook Access Token มีอายุ 2 ชั่วโมง ต้องทำ Refresh Token
- Instagram Access Token มีอายุ 60 วัน ต้องทำ Refresh Token

## 📢 หมายเหตุ
- Line ทำ Refresh Token แล้ว
- WhatsApp ต้องมีการผูก Account Business กับ Meta ก่อน ไม่งั้นข้อความไม่เข้านะ
- WhatsApp ในขั้นตอนทดสอบ ให้รีเควสที่ Meta for Developer เพื่อสร้าง
- Facebook ตอน setting ถ้าไม่มีเพจ อาจจะเพราะมี account อื่น connect อยู่
- **513818688482856** - ID โทรศัพท์
- Verify เหมือนยังมีปัญหา

## 📝 Note 

- IG ยังไม่ได้ทดสอบ _Your app must be set to Live mode for Meta to send webhook notifications._
  [Ref: Facebook Developer](https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/webhooks)

## 📜 ลิขสิทธิ์

โครงการนี้อยู่ภายใต้ลิขสิทธิ์ MIT License

## 📧 ติดต่อ

หากมีคำถามหรือข้อสงสัย กรุณาติดต่อที่ [thanu.s@unityx.group]
