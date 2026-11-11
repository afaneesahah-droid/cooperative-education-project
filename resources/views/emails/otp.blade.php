<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รหัสยืนยันตัวตน (OTP) สำหรับกู้คืนรหัสผ่าน</title>
    <style>
        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(30, 58, 138, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .header p {
            color: #bfdbfe;
            font-size: 13px;
            margin: 5px 0 0 0;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 10px;
            text-align: left;
        }
        .intro-text {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
            text-align: left;
        }
        .otp-container {
            background-color: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            display: inline-block;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #1d4ed8;
            margin: 0;
        }
        .expires-text {
            font-size: 13px;
            color: #ea580c;
            font-weight: 600;
            margin-top: 15px;
        }
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
        }
        .warning-text {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
            text-align: left;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            font-size: 11px;
            color: #94a3b8;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header Banner -->
            <div class="header">
                <h1>วิทยาลัยเทคนิคยะลา</h1>
                <p>ระบบแจ้งซ่อมงานศูนย์ข้อมูลเทคโนโลยีสารสนเทศ</p>
            </div>
            
            <!-- Content -->
            <div class="content">
                <div class="greeting">สวัสดีคุณผู้ใช้งาน,</div>
                <div class="intro-text">
                    เราได้รับคำขอในการกู้คืนรหัสผ่านสำหรับบัญชีของคุณ โปรดใช้รหัสความปลอดภัย OTP ด้านล่างนี้เพื่อดำเนินรายการต่อในขั้นตอนการรีเซ็ตรหัสผ่าน:
                </div>
                
                <!-- OTP Display -->
                <div class="otp-container">
                    <div class="otp-code">{{ $otp }}</div>
                </div>
                
                <div class="expires-text">
                    ⚠️ รหัส OTP นี้มีอายุการใช้งาน 10 นาทีเท่านั้น
                </div>
                
                <div class="divider"></div>
                
                <div class="warning-text">
                    * หากคุณไม่ได้เป็นผู้ส่งคำขอนี้ หรือไม่ได้ดำเนินการเปลี่ยนรหัสผ่าน โปรดเพิกเฉยต่ออีเมลฉบับนี้และไม่ต้องดำเนินการใดๆ เพื่อความปลอดภัยของบัญชีคุณ
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>© 2026 ศูนย์ข้อมูลเทคโนโลยีสารสนเทศ วิทยาลัยเทคนิคยะลา. สงวนลิขสิทธิ์.</p>
            </div>
        </div>
    </div>
</body>
</html>
