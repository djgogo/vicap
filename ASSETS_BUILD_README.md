# Asset Building Guide for Exedra Website

This guide explains how to build assets for both development and production environments.

## Simplified Asset Management

We now use a single directory (`/public/assets`) for both development and production assets. This simplifies the configuration and deployment process.

### Development Commands

- `npm run dev`: Build assets once for development
- `npm run watch`: Build assets for development and watch for changes
- `npm run dev-server`: Start a development server with hot reloading

### Production Build Process

1. Build the production assets locally:
   ```bash
   npm run build
   ```
   or
   ```bash
   npm run build:prod
   ```

2. This will create or update the `/public/assets` directory with all the compiled assets.

3. Commit these assets to Git:
   ```bash
   git add public/assets
   git commit -m "Update assets"
   git push
   ```

4. When deploying to the production server, the deployment script will automatically detect the pre-built assets in `/public/assets` and skip the build process on the server.

## Why This Setup?

This setup addresses issues with Node.js and npm on the Plesk Production server by:

1. Building assets locally where Node.js and npm work correctly
2. Committing the built assets to Git
3. Using the pre-built assets on the production server instead of trying to build them there

The simplified approach of using a single directory for both development and production environments reduces confusion and potential path-related issues.

## Important Notes

- Always run `npm run build` or `npm run build:prod` before deploying to production
- Always commit the `/public/assets` directory to Git
- If you make changes to the assets, you need to rebuild and recommit them before deployment
