# Domain & Hosting Renewal Hub  
🌐 A PHP-based SaaS to manage clients’ hosting & domain expiries—with automated renewal alerts.

## 🔍 Overview  
Domain & Hosting Renewal Hub allows administrators to add clients, track domains and hosting accounts, monitor expiry dates, send automated email notifications, and manage subscription upgrades. Free plan supports up to 30 domains; Pro and Business plans unlock advanced features.  
It also includes a **Super Admin** role with full control over all admins, plans, and client management.

## ⚙️ Key Features  

### 👑 Super Admin  
- Full access to all admins, clients, domains, and hosting accounts  
- Can create, edit, or delete any admin account  
- Manage global system settings, subscription plans, and notifications  
- Monitor activity logs for all admins and clients  

### 👨‍💼 Admin Panel  
- Add and manage clients, domains, and hosting accounts  
- Track expiry dates and upcoming renewals  
- Send automated email notifications via PHPMailer  
- Upgrade client plans (Free → Pro → Business)  

### 📬 Automated Notifications  
- Configurable reminders: 30, 15, 7, 1 day before expiry  
- Custom email templates with client and domain details  
- Optional CC/BCC for admins  

### 💳 Subscription Plans  
| Plan        | Price      | Features                              |
|-------------|------------|----------------------------------------|
| **Free**    | ₹0         | Up to 30 domains, basic notifications |
| **Pro**     | ₹3,000     | 1000 domains, advanced reminders |
| **Business**| ₹10,000    | Unlimited, analytics, white-label   |

---

## 🧱 Technologies Used  
| Technology        | Role                              |
|-------------------|-----------------------------------|
| **PHP**           | Backend logic                     |
| **MySQL**         | Database for clients, domains, plans |
| **PHPMailer API** | Email notifications               |
| **Bootstrap 5**   | Responsive UI                     |
| **AJAX / jQuery** | Dynamic dashboard interactions    | 



---

## 🚀 Installation Guide  
1. Clone the repository:  
   ```bash
   git clone https://github.com/najeebahmad07/Hosting-Domain-Renewal-Notification-SaaS-Product.git
Import the provided SQL file into MySQL.

Configure includes/config.php with database credentials.

Configure SMTP in includes/mailer.php.

Deploy on a local or live server (XAMPP, LAMP, etc.).

Login as Super Admin to set up admins, clients, domains, and plans.

## 🔮 Roadmap & Future Enhancements
- 📱 SMS / WhatsApp notifications for renewals
- 💳 Payment gateway integration (Razorpay, Stripe)
- 👥 Role-based access control improvements
- 🌐 Multi-language UI support and white-label options
- 📊 Analytics and reporting dashboards

## 📝 License
Open-source under the **MIT License**

## 👤 Author
**Najeeb Ahmad**  
[GitHub Profile](https://github.com/najeebahmad07)

> *Never let a domain expire again — track, notify, renew.*
