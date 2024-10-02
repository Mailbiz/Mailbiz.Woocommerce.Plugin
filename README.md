# Mailbiz WooCommerce Plugin

This project is aimed at developing a custom plugin for WooCommerce.

The plugin adds support to WooCommerce for integrating with Mailbiz's Open Tracker.

The plugin adds a configuration panel to the WooCommerce settings page to allow the Mailbiz's support team to add the user credentials.
Once this is set up, the plugin will automatically add the tracking scripts to the WooCommerce store.

## Getting Started

### Prerequisites

- Docker
- Docker Compose

### Installation

##### 1. Clone the repository:
  ```sh
  git clone https://github.com/yourusername/woocommerce-plugin.git
  cd woocommerce-plugin
  ```

##### 2. Set your .env file
  Copy the `.env.example` file to `.env` and set the desired `PORT`.
  <small>(If you change the `PORT` after installing WP you might need to delete the `docker-volumes` and re-install)</small>

##### 3. Start the Docker containers:
  ```sh
  docker compose up --build -d
  ```

##### 4. Access your WordPress site:
  Open your browser and go to `http://localhost:60000`.
  Follow the WordPress installation steps and activate WooCommerce.

  Suggested settings:
  - Site Title: `WooCommerce Plugin`
  - Username: `username`
  - Password: `password`
  - Email: `email@email.com`

  Login to the WordPress admin dashboard: `http://localhost:60000/wp-login.php`

##### 5. Install and activate WooCommerce:
  - Access `http://localhost:60000/wp-admin/plugin-install.php`
  - Search for `WooCommerce` and click on `Install Now`
  <small>This might take a few minutes</small>
  - Click on `Activate`
  - Follow the WooCommerce setup wizard

##### 6. Link the plugin to the WordPress installation:
  - ...

## Project Structure

- `docker-compose.yml`: Docker Compose configuration file to set up WordPress, MySQL, and WooCommerce.
- `src/`: Directory containing the source code for the custom WooCommerce plugin.
- `README.md`: Project documentation.

## Usage

After setting up the environment, you can start developing your custom WooCommerce plugin. Any changes made to the plugin files in the `src/` directory will be reflected in your WordPress installation.

## License

This project is licensed under the MIT License. See the `LICENSE` file for more details.