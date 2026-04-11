# 🥤 QUENCH — Drinks E-Commerce Platform

A full-stack e-commerce web application for browsing and purchasing drinks, built for a project at SIT.

![PHP](https://img.shields.io/badge/-PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/-Bootstrap-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/-JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)

<img width="3600" height="2072" alt="image" src="https://github.com/user-attachments/assets/f3ddc388-d19c-435e-b80e-0d757773ad83" />
<img width="3600" height="2092" alt="image" src="https://github.com/user-attachments/assets/31db0383-a567-40c2-811b-a50b5beefb72" />
<img width="3600" height="2086" alt="image" src="https://github.com/user-attachments/assets/48580b49-51eb-4834-b741-b31beb06c0f2" />
<img width="3598" height="2082" alt="image" src="https://github.com/user-attachments/assets/12d0cc08-605b-4081-a698-444e95007b7b" />
<img width="3598" height="2088" alt="image" src="https://github.com/user-attachments/assets/219179d0-10a0-4a19-bc17-f50c9841e104" />



## Features

- **User Authentication** — Secure login/registration with session management
- **Product Catalogue** — Browse and search drinks with filtering options
- **Shopping Cart & Checkout** — Full cart system with discount coupon support (e.g. `QUENCH10`)
- **Admin Dashboard** — Manage products, orders, and users
- **Dark / Light Theme** — Full theme toggle with consistent styling across all pages
- **AI Chatbot** — Floating chat widget powered by Claude (Haiku) for customer support
- **Store Locator** — Interactive Google Maps integration to find nearby stores
- **Newsletter & Emails** — PHPMailer-powered welcome emails and newsletter subscription with scroll-triggered signup popup
- **Animations** — Parallax effects, animated counters, tilt cards, and scroll-triggered animations
- **WCAG Accessible** — Contrast and accessibility compliance

## Tech Stack

| Layer | Technologies |
|-------|-------------|
| Frontend | HTML, CSS, JavaScript, Bootstrap |
| Backend | PHP |
| Database | MySQL |
| Hosting | Google Cloud VM (LAMP stack) |
| Tools | Git, VS Code, MySQL Workbench |

## Project Structure

```
├── account/        # User account pages
├── admin/          # Admin dashboard
├── auth/           # Login & registration
├── components/     # Reusable PHP components (navbar, footer, chatbot)
├── config/         # Database & API configuration
├── css/            # Stylesheets
├── database/       # SQL schema & seed data
├── images/         # Assets
├── js/             # Client-side scripts
├── models/         # PHP data models
├── pages/          # Main site pages (about, products, etc.)
├── security/       # Security utilities
└── index.php       # Entry point
```

## Getting Started

### Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Apache (or any server with PHP support)
- Composer (for PHPMailer)

### Setup
1. Clone the repo
   ```bash
   git clone https://github.com/BlueberryDealer/INF1005-Project.git
   ```
2. Import the database schema from `database/` into MySQL
3. Configure database credentials in `config/`
4. Start your local server or deploy to a LAMP stack
5. Visit `http://localhost/` in your browser

## Contributors

Built by a team of SIT students for INF1005 Web Systems & Technologies.
