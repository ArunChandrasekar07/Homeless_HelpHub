<div align="center">

<img src="./assets/logo.png" alt="Homeless Help Hub Logo" width="100"/>

# 🏠 Homeless Help Hub

### Role-Based Welfare Coordination & Support Platform

**Production-grade full-stack platform connecting vulnerable communities with volunteers, donors, and government authorities.**

Built from scratch — applying **role-based workflow orchestration** to solve real-world support distribution problems.

<br/>

<img src="https://readme-typing-svg.demolab.com?font=JetBrains+Mono&weight=600&size=18&duration=2600&pause=900&color=22C55E&center=true&vCenter=true&width=900&lines=Role-Based+Access+Control;Government+Verification;Volunteer+Coordination;Transparent+Donation+Workflow" />

<br/><br/>

[![Live](https://img.shields.io/badge/Live-Homeless%20Help%20Hub-22c55e?style=for-the-badge&logo=googlechrome&logoColor=white)](https://homelesshelphub.infinityfree.me/)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind](https://img.shields.io/badge/Tailwind-CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

<br/>

[👥 Roles](#-role-system) • [🏗 Architecture](#-architecture) • [⚡ Quick Start](#-quick-start) • [📊 Workflow](#-request-workflow)

</div>

---

# 🧠 The Problem

In real-world welfare systems, people in need often struggle to receive timely support due to:

| Issue | Description |
|---|---|
| Fake Requests | Resources may be misused |
| Slow Verification | Help gets delayed |
| Poor Coordination | Volunteers and donors lack visibility |
| Low Transparency | Trust decreases across stakeholders |

This project solves these issues using a **multi-role approval workflow** with verification, assignment, and progress tracking.

---

# 📈 System Metrics

| Metric | Value |
|---|---:|
| Architecture | Role-Based |
| User Roles | 4 |
| Verification Layer | Government Approval |
| Request Tracking | Real-Time |
| Donation Transparency | Yes |
| Workflow Automation | Yes |

---

# ⚙️ Role System

## Multi-Role Workflow

Every actor in the system has a defined responsibility.

```text
User submits request
        ↓
Government verifies request
        ↓
Volunteer accepts task
        ↓
Donor contributes support
        ↓
Request completed
```

This guarantees structured and accountable welfare delivery.

---

# 👥 Dashboards

## 👤 User Dashboard
- Submit support requests
- Upload identity/proof
- Track request progress
- View assigned volunteer

## 🧑‍🤝‍🧑 Volunteer Dashboard
- Browse approved requests
- Accept assignments
- Update progress
- Mark completion

## 🏛 Government Officer Dashboard
- Verify documents
- Approve / reject requests
- Assign volunteers
- Monitor accountability

## 💳 Donor Dashboard
- Donate to verified requests
- Track donation history
- View impact

---

# 🏗 Architecture

```text
User / Volunteer / Donor / Officer
               │
               ▼
Frontend (HTML + Tailwind + Bootstrap)
               │
               ▼
PHP Backend
(Auth + Business Logic)
               │
               ▼
Role Access Layer
(RBAC + Workflow Engine)
               │
               ▼
MySQL Database
(Users, Requests, Donations)
```

---

# 📂 Project Structure

```text
Homeless_HelpHub/
│
├── index.html
├── auth.php
├── db.php
├── login.php
├── logout.php
│
├── submit_request.php
├── edit_request.php
├── approve_request.php
├── assign_request.php
├── update_assignment.php
├── donate_request.php
├── add_feedback.php
│
├── user_dashboard.php
├── volunteer_dashboard.php
├── donor_dashboard.php
└── gov_dashboard.php
```

---

# 📊 Request Workflow

```text
User submits request
        │
        ▼
Document Verification
(Government Officer)
        │
        ├── REJECT → Close request
        │
        └── APPROVE
              │
              ▼
Volunteer Assignment
              │
              ▼
Donation Support
              │
              ▼
Request Completed
```

---

# 🆚 Why This Architecture?

| Approach | Secure | Scalable | Transparent |
|---|---|---|---|
| Basic Donation Portal | ❌ | ⚠️ | ❌ |
| NGO Manual Process | ⚠️ | ❌ | ⚠️ |
| **Homeless Help Hub** | ✅ | ✅ | ✅ |

Role-based architecture improves accountability and reduces misuse.

---

# ⚡ Quick Start

### Prerequisites

- XAMPP / WAMP  
- PHP 8+  
- MySQL 8+  
- phpMyAdmin  

---

### Setup

```bash
git clone https://github.com/ArunChandrasekar07/Homeless_HelpHub.git
```

```bash
# Move project to server root
cp -r Homeless_HelpHub /xampp/htdocs/
```

```bash
# Configure database credentials
db.php
```

```bash
# Run project
http://localhost/Homeless_HelpHub/
```

---

# 🗄 Database Modules

Main entities:

```text
Users
Requests
Volunteers
Donors
Officers
Assignments
Donations
Feedback
```

These modules power the full support lifecycle.

---

# 👨‍💻 Author

<div align="center">

## Arun Chandrasekar

AI Engineer • Backend Engineer  
Integrated M.Tech Software Engineering — VIT Vellore

[![Portfolio](https://img.shields.io/badge/Portfolio-arunc.vercel.app-black?style=for-the-badge&logo=vercel)](https://arunc.vercel.app)
[![GitHub](https://img.shields.io/badge/GitHub-ArunChandrasekar07-181717?style=for-the-badge&logo=github)](https://github.com/ArunChandrasekar07)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-arunchandrasekar1-0A66C2?style=for-the-badge&logo=linkedin)](https://linkedin.com/in/arunchandrasekar1)

*Built to create social impact through production-grade engineering.*

</div>
