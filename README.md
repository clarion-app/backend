# clarion-app/backend

This is a Laravel package providing APIs and services for Clarion, a decentralized platform for managing your data and applications.

## Features

- OAuth2 authentication with Laravel Passport
- Clarion network management: create/join networks
- Composer, NPM, and app package installation/uninstallation

## Requirements

- PHP >= 8.0
- Laravel >= 12.x
- Composer
- MariaDB or PostgreSQL
- Node.js and NPM (for frontend asset compilation)

## Installation

1. Require the package via Composer:

   ```bash
   composer require clarion-app/backend
   ```

2. Publish the configuration file:

   ```bash
   php artisan vendor:publish --provider="ClarionApp\Backend\ClarionBackendServiceProvider" --tag="clarion-config"
   ```

3. Add the following to your `.env` file:

   ```dotenv
   FRONTEND_URL=https://your-frontend-url.example.com
   ```

4. Run database migrations:

   ```bash
   php artisan migrate
   ```

5. Install Laravel Passport:

   ```bash
   php artisan passport:install
   ```

## Configuration

After publishing, the configuration can be found in `config/clarion.php`:

```php
return [
    'node_id'      => env('CLARION_NODE_ID'),
    'frontend_url' => env('FRONTEND_URL'),
];
```

- `node_id`: Unique UUID for this Clarion node
- `frontend_url`: URL of the Clarion frontend for device presentation

## Usage

### Artisan Commands

- `php artisan clarion:setup-node-id` — Generate and set node ID

### API Endpoints

See `src/Routes.php` for the full list. Key endpoints include:

- `GET /Description.xml` — UPnP device descriptor
- `POST /api/clarion/system/network/create` — Create a new Clarion network
- `POST /api/clarion/system/network/join` — Join an existing network
- `POST /api/clarion/system/composer/install` — Install a Composer package
- `POST /api/clarion/network/join` — Join a peer network
- `GET /api/clarion/network/local_nodes` — List local nodes
- `GET /api/clarion/network/requests` — List network join requests
- `GET /api/docs/packages` — Retrieve API package documentation

### Scheduling

A Clarion node discovery job runs every five seconds when the queue worker is active. Start your queue worker with:

```bash
php artisan queue:work --queue=default
```

## Contributing

Contributions are welcome! Please submit issues and pull requests via GitHub.

## License

This project is licensed under the MIT License.

## Author

Tim Schwartz (<tim@metaverse.systems>)