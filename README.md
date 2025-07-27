# 🌀 Notion Publisher – Powered by The Void

A minimal self-hosted CMS that turns your Notion pages into public HTML project pages.  
Just drop a Notion share link in the admin panel, and boom — cleanly generated public pages, managed entirely by you.

---

## ✨ Features

- 📄 Auto-generated public-facing HTML pages with Notion embeds
- 🏷️ Tag support, visibility toggle, sorting & filtering
- ⚡ Super lightweight (PHP + MariaDB only)
- 💾 No bloat, no JS frameworks, no nonsense
- 🖼️ Beautiful public index with sorting by date/title
- 🔁 Reusable `.env` for fast configuration

---

## 🚀 Setup Instructions

### 1. Clone the repo

```bash
git clone https://github.com/Kyoo123/notion-publisher.git
cd notion-publisher
```

---

### 2. Create your database

```sql
CREATE DATABASE notion_links;
```

Then import the table schema:

```bash
mysql -u root -p notion_links < sql/schema.sql
```

---

### 3. Configure the `.env` file

Edit `.env` to match your setup:

```env
# MySQL credentials
MYSQL_HOST=localhost
MYSQL_USER=public
MYSQL_PASSWORD=your_password
MYSQL_DATABASE=notion_links

# Embed domain (from Notion → yourname.notion.site)
EMBED_DOMAIN=yourname.notion.site

# Local folder where HTML should be saved
FILE_PATH=/var/www/html/public

# Public base URL of generated pages
PUBLIC_URL=https://yourdomain.dev/public
```

---

### 4. Set up hosting

Make sure all `.php` files are accessible via a web server like Apache or NGINX.  
If using XAMPP, drop them into `htdocs/NotionENV/` or similar.

---

### 5. Visit your panels

- ➕ `admin.php` — Add new projects
- 📋 `manage.php` — Toggle visibility, sort/filter, or delete
- 🌍 `index.php` — Public listing page

---

## 🧠 How It Works

- Admin pastes a Notion URL → it's parsed and embedded as `https://yourname.notion.site/ebd/{page_id}`
- HTML files are saved to your configured path
- MariaDB stores metadata like title, tags, and visibility
- Public page lists everything (or just visible ones)

---

## 💡 Requirements

- PHP 7.4+
- MariaDB or MySQL
- A Notion workspace with a public domain (`yourname.notion.site`)

---

## 🛡 Security Tips

- Put `admin.php` and `manage.php` behind Cloudflare Zero Trust or HTTP auth
- Never commit your real `.env` to Git
- Make sure `FILE_PATH` is only writable by PHP, not world-writable

---

## 🧩 Credits

Built by [@Kyoo123](https://voidcore.dev) using ✨ vibes and PHP.

---

## 📄 License

MIT. Use it. Fork it. Break it. Fix it. The Void provides.
