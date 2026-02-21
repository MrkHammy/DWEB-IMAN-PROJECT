<p align="center">
  <img src="DWEB PROJECT/IMGS/LOGO/logo.PNG" alt="Fox Lab Logo" width="120">
</p>

<h1 align="center">Fox Lab</h1>
<p align="center">
  <strong>Cybersecurity Awareness & Training Platform</strong>
</p>
<p align="center">
  Train your team to identify and prevent cyber threats with realistic simulations, interactive tools, and expert-curated educational resources.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/CSS3-Custom-1572B6?logo=css3&logoColor=white" alt="CSS3">
  <img src="https://img.shields.io/badge/XAMPP-Apache-FB7A24?logo=xampp&logoColor=white" alt="XAMPP">
</p>

---

## 📖 About

**Fox Lab** is a full-stack cybersecurity awareness and training platform built for **Holy Angel University (HAU)**. It provides students and organizations with hands-on tools to understand, practice, and defend against modern cyber threats — all through an intuitive, responsive web interface.

---

## ✨ Features

| Feature | Description |
|---------|-------------|
| 🎣 **Phishing Simulator** | Realistic email phishing scenarios with a 4-per-session quiz format. Users analyze emails and identify red flags across 15+ unique scenarios. |
| 🔐 **Password Tester** | Real-time password strength checker with entropy scoring, breach detection against 110+ common passwords, and actionable improvement tips. |
| 💻 **Online Code Compiler** | Browser-based code editor supporting **Python** and **Java** with 8 guided tutorials each, powered by the Piston API with local fallback. |
| 📚 **Security Glossary** | Comprehensive glossary of **85+ cybersecurity terms** across 8 categories with pronunciations, usage context, related terms, and learning resources. |
| 🔖 **Bookmark System** | Logged-in users can bookmark glossary terms for quick reference, with a dedicated bookmarked filter in the sidebar. |
| 📝 **Security Blogs** | 8+ expert-authored blog posts covering AI in cybersecurity, Zero Trust, ransomware defense, OWASP Top 10, and more. |
| 🛡️ **Admin Dashboard** | Full blog management system for admins — create, edit, delete posts with image uploads, HTML content editor, and live preview. |
| 🏢 **Partner Organizations** | Showcase of partnered organizations (CSIA, GDG, CISCO) with external links to their pages. |
| 🔍 **Smart Search** | Global predictive search bar with autocomplete across all glossary terms. |
| 👤 **User Authentication** | Secure session-based login/register system with role-based access (Student / Admin). |

---

## 🖼️ Platform Preview

<table>
  <tr>
    <td align="center" width="33%">
      <img src="DWEB PROJECT/IMGS/platform_features_imgs/phishing.png" alt="Phishing Simulator" width="280"><br>
      <strong>Phishing Simulator</strong>
    </td>
    <td align="center" width="33%">
      <img src="DWEB PROJECT/IMGS/platform_features_imgs/how strong is ur password.png" alt="Password Tester" width="280"><br>
      <strong>Password Tester</strong>
    </td>
    <td align="center" width="33%">
      <img src="DWEB PROJECT/IMGS/platform_features_imgs/online compiler.png" alt="Online Compiler" width="280"><br>
      <strong>Online Code Compiler</strong>
    </td>
  </tr>
</table>

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| **Frontend** | HTML5, CSS3 (Custom), Vanilla JavaScript (ES6+) |
| **Backend** | PHP 8.x with PDO (Prepared Statements) |
| **Database** | MySQL 8.0 (InnoDB) |
| **Server** | Apache via XAMPP |
| **Fonts** | Google Fonts — Inter, Fira Code |
| **Icons** | Font Awesome 6.5.1 |
| **Code Execution** | Piston API + Local Python 3.12 / Java 23 Fallback |

---

## 📁 Project Structure

```
DWEB PROJECT/
├── index.php                  # Home page (Hero, Features, Stats, Tips, Partners)
├── database.sql               # Complete database schema & seed data
├── config/
│   └── db.php                 # PDO connection & auth helpers
├── includes/
│   ├── header.php             # Global header with navigation & search
│   └── footer.php             # Global footer
├── pages/
│   ├── phishing.php           # Phishing simulation quiz
│   ├── checker.php            # Password strength tester
│   ├── compiler.php           # Online code editor
│   ├── terms.php              # Security glossary
│   ├── blog.php               # Blog listing & single post view
│   ├── admin-blogs.php        # Admin blog management dashboard
│   ├── partners.php           # Partner organizations
│   └── login.php              # Authentication (Login / Register)
├── api/
│   ├── execute.php            # Code execution proxy (Piston API)
│   └── search_terms.php       # Global search autocomplete API
├── assets/
│   ├── css/
│   │   └── style.css          # All platform styles
│   └── js/
│       ├── main.js            # Global scripts & search
│       ├── compiler.js        # Code editor & tutorials
│       └── phishing.js        # Phishing quiz logic
├── uploads/
│   └── blog/                  # Uploaded blog images
└── IMGS/
    ├── LOGO/                  # Platform logo
    ├── blog/                  # Blog post images (SVG)
    ├── org_logos/              # Partner organization logos
    └── platform_features_imgs/# Feature card images
```

---

## ⚡ Quick Start

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.x)
- Python 3.12+ *(optional, for local code execution)*
- Java 23+ *(optional, for local code execution)*

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/foxlab.git
   ```

2. **Copy to XAMPP**
   ```
   Move the project folder to C:\xampp\htdocs\DWEB PROJECT
   ```

3. **Start XAMPP**
   - Launch Apache and MySQL from the XAMPP Control Panel

4. **Import the database**
   ```bash
   mysql -u root < database.sql
   ```
   Or import `database.sql` via phpMyAdmin at `http://localhost/phpmyadmin`

5. **Open in browser**
   ```
   http://localhost/DWEB%20PROJECT/
   ```

### Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Student | `charlie@foxlab.com` | `Password123!` |
| Admin | `admin@foxlab.com` | `Password123!` |

---

## 👥 Team Contributions

<div align="center">
  <h2>Team Contributions</h2>

  <table width="100%">
    <thead>
      <tr align="left" style="background-color: #24292e; color: #ffffff;">
        <th width="30%">Member</th>
        <th width="70%">Contributions</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><b>Bautista, Mark Anthony</b></td>
        <td>Wireframe Designer, GUI Developer, Backend Developer, SQL Encoder, Documentation, Glossary of Terms</td>
      </tr>
      <tr>
        <td><b>Bermas, Estella Mae</b></td>
        <td>Wireframe Designer, GUI Developer, Documentation, Glossary of Terms</td>
      </tr>
      <tr>
        <td><b>Gamboa, Rodel Vincent</b></td>
        <td>Backend, SQL Encoder, Glossary of Terms</td>
      </tr>
      <tr>
        <td><b>Marcelino, Princess Camille</b></td>
        <td>Project Manager, Wireframe Designer, Backend Developer, SQL Encoder, Glossary of Terms, Documentation</td>
      </tr>
      <tr>
        <td><b>Roque, Daryl John Clark</b></td>
        <td>Backend, SQL Encoder, Glossary of Terms, Documentation</td>
      </tr>
    </tbody>
  </table>
</div>

---

## 📄 License

This project was developed as an academic requirement for **Holy Angel University**. All rights reserved.

---

<p align="center">
  <strong>Fox Lab</strong> — Empowering cybersecurity awareness through education and training.
</p>>







