# Projek-fix

## Setup
- Jalankan autoload PSR-4:
	- `composer dump-autoload`

## Konfigurasi OAuth (SSO)
- Buat file `.env` dari `.env.example`, lalu isi:
	- `GOOGLE_OAUTH_CLIENT_ID`
	- `GOOGLE_OAUTH_CLIENT_SECRET`
	- (opsional) `GOOGLE_OAUTH_REDIRECT_URI` (default callback: `/auth/google_callback.php`)
- Di Google Cloud Console, pastikan Authorized redirect URI sama persis:
	- `http://localhost/Projek-fix/auth/google_callback.php`

## Namespace & Autoload
- Namespace root: `App\\`
- Folder source: `src/`
- Autoload: Composer PSR-4 (file `vendor/autoload.php`)
