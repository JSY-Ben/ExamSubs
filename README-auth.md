# Microsoft Entra Authentication

## Required Config

Update the `entra` section in `config.php`:

- `tenant_id`
- `client_id`
- `client_secret`
- `redirect_uri` (set to your site URL + `/auth/callback.php`)

Example redirect URI:

```
https://your-domain.example.com/auth/callback.php
```

## App Registration Settings

- Single-tenant
- Web platform
- Redirect URI as above
- API permissions: `openid`, `profile`, `email`

## Notes

- All `/staff/*` pages require authentication.
- Logout clears the local session.
