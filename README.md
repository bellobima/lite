# FCWM Student Complaint & Support Portal

A PHP and MySQL complaint portal for students and college support staff. Students register with their full name, email, matric number, department, and password; submit topic-based complaints; and follow each complaint using a generated reference. Administrators route complaints to departments, and department accounts can work only on their own department's queue.

The interface uses a navy, gold, and off-white institutional theme. The public home page and student dashboard use a single library image rather than a carousel; public complaint statistics are aggregate live database counts only. Admin overview, complaint search, department staff management, department overview, and assigned-case views each have their own page. Student and public pages also include a WhatsApp contact shortcut and contact dialog.

## Run locally with XAMPP

1. Copy the project folder into `C:\xampp\htdocs\lite` (or another folder under `htdocs`).
2. Start Apache and MySQL in the XAMPP Control Panel. The database user defaults to `root` with an empty password.
3. Open `http://localhost/lite/`. On first use, the application creates the `fcwm_complaints` database, required tables, and starter department list automatically.
4. Open **Initial administrator setup** from the sign-in page (or visit `/setup.php`) and create the first administrator. This form is disabled after an administrator account exists.
5. Sign in as admin, create department staff accounts, and route student complaints to the appropriate department.

The database connection can be customized with `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASS` environment variables. The MySQL account needs permission to create the configured database and its tables.

## Security and operations

- Student passwords use PHP's password hashing functions; database access uses PDO prepared statements.
- Student registration always creates a student account. Department staff accounts are created by administrators.
- Complaint detail pages enforce student ownership and department assignment checks. Public tracking additionally requires the student's matric number; a reference alone does not reveal the complaint.
- Forms use session CSRF tokens, and staff status changes are recorded in the complaint history.
- Use HTTPS for deployment, configure a dedicated least-privilege MySQL account, and use strong account passwords. Configure the web server to prevent directory browsing and protect configuration files.

Departments are seeded the first time the database is initialized. Edit the `$departments` list in `config.php` before first launch to match the college's official department names.

The library hero photograph is served from Unsplash under the Unsplash License. Its image URL is in `index.php` and `student.php`; an internet connection is needed to load the remote image. The site falls back to its navy hero background if it cannot be reached.
