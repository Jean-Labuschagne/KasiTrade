# KasiTrade - C2C E-Commerce Platform

South African township marketplace built for Eduvos ITECA3-12 Web Development & e-Commerce.

Project context

- Technical stack: PHP 8.2, MySQL, Bootstrap 5.3, HTML5, CSS3, JavaScript, and jQuery-ready patterns.
- Design approach: mobile-first, low-data, high-contrast, large touch targets, and simple navigation for township users.
- Marketplace focus: informal traders, spaza shop owners, and side-hustlers buying and selling locally.

Functional features and code links

- Shared layout and navigation live in [includes/header.php](includes/header.php) and [includes/footer.php](includes/footer.php).
- Database connection and PDO setup live in [config/database.php](config/database.php).
- Reusable helper functions, authentication helpers, and security checks live in [config/functions.php](config/functions.php).
- Main styling lives in [assets/css/style.css](assets/css/style.css), [assets/css/responsive.css](assets/css/responsive.css), and [assets/css/bootstrap-custom.css](assets/css/bootstrap-custom.css).
- Form validation and SA ID checking live in [assets/js/validation.js](assets/js/validation.js).
- Dynamic listing interactions and AJAX-style behaviour live in [assets/js/listings.js](assets/js/listings.js).
- Database schema, sample township data, and table relationships live in [setup.sql](setup.sql).

Customer page features

- Home page in [index.php](index.php) shows the hero section, categories, featured listings, and how KasiTrade works.
- Browse page in [browse.php](browse.php) supports search, filters, pagination, and responsive listing cards.
- Listing page in [listing.php](listing.php) shows product details, image gallery support, seller details, and related actions.
- Auth pages in [auth/login.php](auth/login.php) and [auth/register.php](auth/register.php) handle login and registration.
- Create listing page in [create-listing.php](create-listing.php) lets sellers post items with validation and image handling.
- Profile page in [profile.php](profile.php) shows user details, reviews, listings, and purchase activity.
- Messages page in [messages.php](messages.php) supports buyer-seller chat.
- Mobile support is built into the CSS breakpoints so the UI adapts to phone, tablet, and desktop screens.
- Low-data support is handled with compressed image usage, simple layout choices, and limited animation.
- Accessibility support includes large touch targets, clear contrast, and focus styles.

Admin page features

- Admin dashboard in [admin/dashboard.php](admin/dashboard.php) shows summary cards, recent users, recent listings, and recent reports.
- User management in [admin/users.php](admin/users.php) supports RBAC-aware administration and account control.
- Listing moderation in [admin/listings.php](admin/listings.php) supports review, approval, and oversight of marketplace posts.
- Transactions view in [admin/transactions.php](admin/transactions.php) tracks trading activity.
- Disputes handling in [admin/disputes.php](admin/disputes.php) manages buyer-seller conflicts.
- Reports view in [admin/reports.php](admin/reports.php) manages reports and admin notes.
- The admin UI uses the same Bootstrap-based shared layout from [includes/header.php](includes/header.php) and the same theme files in [assets/css/](assets/css/).

Designing and prototype scope

- Main website is structured for responsive behaviour across mobile, tablet, and desktop.
- Admin website uses role-based access control so admin features only show to permitted users.
- Data model is defined in [setup.sql](setup.sql) with tables, foreign keys, JSON columns, and ENUM fields.
- The project covers the major diagrams required by the brief: CRC cards, EERD, context diagram, DFD level 1, use case diagram, and database design.

Coding notes

- PHP is used for server-side rendering, database access, authentication, and CRUD actions.
- HTML is used for layout, navigation, forms, and product cards.
- JavaScript is used for validation, dynamic interactions, and richer page behaviour.
- CSS is split into theme overrides, responsive rules, and Bootstrap customisations.
- MySQL stores users, roles, listings, categories, transactions, messages, reviews, reports, disputes, and pickup points.

Features in progress

- Dark mode support is being reviewed because some browser dark-mode settings can still affect the UI.
- Card and surface styling may still be adjusted to improve readability in dark browser themes.
- More theme-aware overrides may be added if the browser forces dark rendering.

Conclusion

- KasiTrade was designed around township trading needs, mobile access, and simple user flows.
- The codebase already supports the core marketplace, admin control, and responsive behaviour needed for Deliverable 3.
- Next steps are to finish the remaining visual polish, tighten theme behaviour, and prepare the final implementation evidence.

