# Mailbiz WooCommerce Plugin

This plugin adds support to WooCommerce stores for integrating with Mailbiz's Open Tracker.

The plugin adds a configuration panel to the WooCommerce settings page to allow the Mailbiz's support team to add the user credentials.
Once this is set up, the plugin will automatically add the tracking scripts to the WooCommerce store.

## Getting Started

### Prerequisites

- Docker
- Docker Compose

### Installation

#### 1. Clone the repository:
  ```sh
  git clone https://github.com/yourusername/woocommerce-plugin.git
  cd woocommerce-plugin
  ```

#### 2. Set your .env file
  Copy the `.env.example` file to `.env` and set the desired `PORT`.
  <small>(If you change the `PORT` after installing WP you might need to [reset the WordPress installation](#resetting-the-wordpress-installation)</small>

#### 3. Start the Docker containers:
  ```sh
  docker compose up --build -d
  ```

#### 4. Access your WordPress site:
  Open your browser and go to `http://localhost:60000`.
  Follow the WordPress installation steps.

  Suggested settings:
  - Site Title: `Mailbiz WooCommerce Tracker`
  - Username: `username`
  - Password: `password`
  - Email: `email@email.com`

  Login to the WordPress admin dashboard: `http://localhost:60000/wp-login.php`

#### 5. Install and activate WooCommerce:
  - Access `http://localhost:60000/wp-admin/plugin-install.php`
  - Search for `WooCommerce` and click on `Install Now`
  <small>This might take a few minutes</small>
  - Click on `Activate`
  - Follow the WooCommerce setup wizard

#### 6. Activate Mailbiz WooCommerce Plugin:
  - Access `http://localhost:60000/wp-admin/plugins.php`
  - Find `Mailbiz WooCommerce Tracker` in the installed plugins list
  - Click on `Activate`

### Resetting the WordPress installation
- Delete the docker container
- Delete the `docker-volumes` directory
- Run `docker compose up --build -d` again.

### Testing
- Navigate to a product page and add it to the cart. Tracking events should be visible in the dev console.

## Project Structure

- `README.md`: This file.
- `docker-compose.yml`: Docker Compose configuration file to set up WordPress, MySQL and folder mapping.
<br>
- `docker-volumes`: Mapped directories for docker. Not versioned.
- `docker-volumes/db_data`: Directory for the MySQL database.
- `docker-volumes/wordpress`: Directory for the WordPress installation.
- `docker-volumes/plugins`: WordPress plugins folder.
<br>
- `src/`: Directory containing the source code for the Mailbiz WooCommerce Tracker. Already mapped to the WordPress plugins folder.

## Usage

After setting up the environment, you can start developing. Any changes made to the plugin files in the `src/` directory will be reflected in your WordPress installation.

## Build / production

## Compatibility

- WordPress: 1.5.0 (?) (get_option added)

#### Versions used for development

- WordPress: 6.6.2
- WooCommerce: 9.3.3
- MySQL: 8.0
- PHP: 8.2.24