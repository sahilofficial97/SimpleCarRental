# Simple Car Rentall (Symfony Webapp)

This is a Symfony web application. You can run it locally using the Symfony CLI.

## Quick start (Windows PowerShell)

1. Ensure the Symfony CLI is installed:
   - Check: `symfony -v`
2. Build frontend assets (outputs to `public/build/`):
   - `npm run dev`
   - For production: `npm run build`
3. Start the local web server (HTTP):
   - `symfony serve --no-tls --dir "C:\\Users\\Jack\\Documents\\GitHub\\SimpleCarRental\\Simple-Car-Rental"`
4. Open the app:
   - URL: http://127.0.0.1:8000

### Alternative (daemon mode)
If you prefer the background daemon (may require exclusive access to the CLI log file on Windows):

```
symfony serve -d --no-tls --dir "C:\Users\Jack\Documents\GitHub\SimpleCarRental\Simple-Car-Rental"
symfony server:status
```

### Useful commands
- App info: `symfony console about`
- Clear cache: `symfony console cache:clear`
- List routes: `symfony console debug:router`

### Asset build notes
- Build artifacts must live under `public/build/` (configured in `webpack.config.js`).
- If you see a `build/` folder at the project root, delete it; it is ignored by Git and not served by the web server.

### Notes
- The CLI manages a PHP runtime automatically. If you need Composer, use `symfony composer <command>`.
- The document root is `public/`.
- Default environment is `dev` (see `.env`).
