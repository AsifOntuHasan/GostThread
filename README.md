# GostThread
GhostThread is an anonymous social media platform where users post, like, comment and share freely **without login or profile**.  "No Login. No Profile. Just Conversation." 👻  Features a sleek dark UI, image uploads, dual feeds, and a powerful admin panel for content moderation using banned words and manual review.
Here's a **clean, professional, and impressive GitHub README** for your **GhostThread** project, optimized for a final-year software engineering project.

```markdown
# 👻 GhostThread - Anonymous Social Media

**"No Login. No Profile. Just Conversation."**

A fully anonymous social media platform where users can post, like, comment, bookmark, and share without creating any account. Built with a powerful admin moderation dashboard to maintain a safe environment.

![GhostThread Preview](https://via.placeholder.com/800x400/0a0a1a/6c63ff?text=GhostThread+Preview)

## ✨ Key Features

### For Users
- **Complete Anonymity** — No signup, no username, no profile
- Post text + images anonymously
- Like, comment, bookmark, and share posts
- Two feed modes: **Recent** and **Random (Explore)**
- Personal Bookmarks page
- Responsive dark UI with immersive particle background
- Rate limiting to prevent spam

### For Admins
- Modern admin dashboard with role-based access
- Review & moderate pending posts and comments
- Approve / Reject / Delete with full action logging
- Dynamic moderation word management (Terrorist, Sexual, Cyberbullying)
- Admin user management (Super Admin only)
- System settings and moderation logs

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript, Three.js (particle background)
- **Backend**: PHP 8
- **Database**: MySQL
- **Styling**: Custom modern dark gradient UI with Font Awesome
- **Security**: Prepared Statements, Rate Limiting, File Upload Validation

## 📸 Screenshots

### User Interface
![User Feed](https://via.placeholder.com/600x350/1a1a3e/ffffff?text=User+Feed)
![Create Post](https://via.placeholder.com/600x350/1a1a3e/ffffff?text=Create+Post)

### Admin Panel
![Pending Posts](https://via.placeholder.com/600x350/0a0a1a/6c63ff?text=Admin+Pending+Posts)
![Moderation Dashboard](https://via.placeholder.com/600x350/0a0a1a/6c63ff?text=Admin+Dashboard)

*(Replace placeholder images with actual screenshots from your project)*

## 🚀 Quick Start

### Prerequisites
- XAMPP / WAMP / LAMP Stack
- PHP 8.0+
- MySQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/ghostthread.git
   cd ghostthread
   ```

2. **Setup Database**
   - Create a database named `ghostthread`
   - Import `ghostthread.sql` file (located in the root)
   - Or run `setup.php` in your browser

3. **Configure**
   - Update database credentials in `user/config/db.php` if needed

4. **Start Server**
   - Place the project in `htdocs` (XAMPP) or equivalent
   - Start Apache and MySQL
   - Open browser and go to `http://localhost/ghostthread/user`

5. **Admin Access**
   - URL: `http://localhost/ghostthread/admin`
   - Default credentials:
     - **Username**: `admin`
     - **Password**: `password`

> **⚠️ Important**: Change the default admin password immediately after first login.

## 📁 Project Structure

```
ghostthread/
├── user/                    # Main user-facing application
├── admin/                   # Admin moderation panel
├── api/                     # Backend API endpoints
├── assets/
│   ├── uploads/posts/       # Uploaded images
│   └── css/
├── includes/
│   └── ContentModerator.php
├── ghostthread.sql          # Database schema + sample data
├── setup.php                # One-click database setup
└── README.md
```

## 🔒 Security Features

- Prepared statements to prevent SQL injection
- Input validation and sanitization
- File upload security (MIME type + size check)
- Rate limiting on posts and comments
- Role-based admin access (Moderator vs Super Admin)
- Action logging for all moderation activities

## 🎯 Future Enhancements

- Threaded comment replies
- User reporting system
- Image content moderation (AI integration)
- CSRF protection
- Trending topics section
- Analytics dashboard for admins

## 🤝 Contributing

This is a final-year group project. Contributions are welcome!

1. Fork the project
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is for educational purposes only.

---

**Made with ❤️ for anonymous freedom of expression.**

```

### How to Use This README:

1. **Replace placeholders**:
   - Your actual GitHub username
   - Real group member names
   - Add actual screenshots (highly recommended)

2. **Add real images**:
   - Take screenshots of:
     - Homepage feed
     - Create post modal
     - Pending posts admin page
     - Moderation words page
     - Single post view

3. **Optional Improvements**:
   - Add a live demo link if you host it
   - Add badges (PHP, MySQL, etc.)
   - Add a "Demo Video" section

Would you like me to also create:
- A shorter **one-page version**?
- A **project poster** description?
- **Installation instructions** tailored for your university lab?

Just say the word and I'll refine it further! 👻

**Pro Tip**: Add this README as the main file in your GitHub repository — it will make your project look very professional to teachers and recruiters.
