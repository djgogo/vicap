Exedra Job Recruiting Website and App
=====================================

The "Exedra Website App" is a professional Job recruiting website application developed with the symfony framework.

Requirements
------------

  * PHP 8.2.0 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][2].

## Build/Configuration Instructions

### Environment Setup

1. **PHP Requirements**:
    - PHP 8.2 or higher
    - Required PHP extensions: simplexml, curl, fileinfo, zip

2. **Clone the Repository**:
   ```bash
   git clone [repository-url]
   cd exedra-website
   ```

3. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

4. **Environment Configuration**:
    - Copy `.env` to `.env.local` and configure your environment variables
    - Key configurations to set:
        - `DATABASE_URL`: Database connection string
        - `MAILER_DSN`: Email service configuration
        - `APP_ENV`: Set to `dev` for development, `prod` for production

5. **Database Setup**:
   ```bash
   bin/console doctrine:database:create
   bin/console doctrine:migrations:migrate
   bin/console doctrine:fixtures:load # Optional, for test data
   ```

6. **Build Frontend Assets**:
   ```bash
   npm run build # For production
   npm run dev # For development
   npm run watch # For development with auto-rebuild
   ```

## Additional Development Information

### Code Style

- The project follows Symfony coding standards
- PHP-CS-Fixer is configured in `.php-cs-fixer.dist.php`
- Run code style checks with:
  ```bash
  vendor/bin/php-cs-fixer fix --dry-run
  ```
- Fix code style issues with:
  ```bash
  vendor/bin/php-cs-fixer fix
  ```

### Frontend Assets

- Frontend assets are managed with Webpack Encore
- SCSS files are compiled to CSS
- Configuration is in `webpack.config.js`
- Key commands:
  ```bash
  npm run dev # Build assets for development
  npm run watch # Watch for changes and rebuild
  npm run build # Build assets for production
  ```

### Database Migrations

- Create a new migration after entity changes:
  ```bash
  bin/console make:migration
  ```
- Apply migrations:
  ```bash
  bin/console doctrine:migrations:migrate
  ```

### Debugging

- Use the Symfony profiler in development mode
- Access it at `/_profiler` after making a request
- For API debugging, use the `server:dump` command:
  ```bash
  bin/console server:dump
  ```

### Deployment

- Clear and warm up the cache before deployment:
  ```bash
  bin/console cache:clear --env=prod
  bin/console cache:warmup --env=prod
  ```
- Build production assets:
  ```bash
  npm run build
  ```
- Update database schema if needed:
  ```bash
  bin/console doctrine:migrations:migrate --env=prod
  ```