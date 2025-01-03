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
  git clone https://github.com/Mailbiz/Mailbiz.Woocommerce.Plugin
  cd Mailbiz.Woocommerce.Plugin
  ```

#### 2. Set your .env file
  Copy the `.env.example` file to `.env` and set the desired variables.
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

#### 7. Configure the Mailbiz WooCommerce Plugin:
  - Access `http://localhost:60000/wp-admin/options-general.php?page=mailbiz-woocommerce-tracker`
  - Enable integrations
  - Fill in the `Chave de integração` (eg: `111111111111111111111111` or `653a628a7058d778ef8ebe06`)
  - Save changes

#### 8. (optional) Add a product to the store:
  - Access `http://localhost:60000/wp-admin/post-new.php?post_type=product`
  - Fill in product details
  - Publish the product

<small>It is a good idea to add the maximum amount of data to the product to test every possibility. This includes adding products with and without variations.</small>

#### 9. (optional) Add a payment method:
  - Access `http://localhost:60000/wp-admin/admin.php?page=wc-admin&task=payments`
  - Add "Cash on delivery" payment method

#### 10. (optional) Add a shipping method:
  - Access `http://localhost:60000/wp-admin/admin.php?page=wc-settings&tab=shipping`
  - Add a "Flat rate" shipping method for a region or all regions

#### 11. (optional) Enable WordPress debugging:
Set `define('WP_DEBUG', true);` in `docker-volumes/wordpress/wp-config.php` to enable debugging

#### 12. (optional) Configure xdebug (to debug PHP code):
- Setup the `XDEBUG_CLIENT_HOST` environment variable. It is used in the `docker-compose.yml` file, in the `XDEBUG_CONFIG` setting.<br />
*If not working, manually enter into the container and set the same options described in `XDEBUG_CONFIG` inside `xdebug.ini`.*<br />
*The `xdebug.ini` file can be found at `/usr/local/etc/php/conf.d/xdebug.ini`*
- Install the [PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) extension in VSCode (don't forget to add breakpoints).
- Install the [Xdebug Helper](https://chromewebstore.google.com/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc) extension in Chrome (don't forget to enable debug).

### Resetting the WordPress installation
- Delete the docker container
- Delete the `docker-volumes` directory
- Run `docker compose up --build -d` again.

### Testing
- Navigate to a product page and add it to the cart. Events should be tracked upon page reload.

## Project Structure

- `README.md`: This file.
- `docker-compose.yml`: Docker Compose configuration file to set up WordPress, MySQL and folder mapping.
- `docker-volumes`: Mapped directories for docker. Not versioned.
- `docker-volumes/db_data`: Directory for the MySQL database.
- `docker-volumes/wordpress`: Directory for the WordPress installation.
- `docker-volumes/plugins`: WordPress plugins folder.
- `src/`: Directory containing the source code for the Mailbiz WooCommerce Tracker. Already mapped to the WordPress plugins folder as `mailbiz-woocommerce-tracker`.

## Usage

After setting up the environment, you can start developing. Any changes made to the plugin files in the `src/` directory will be reflected in your WordPress installation.

## Build / production (wip)

Copy the code inside the `src/` directory to `mailbiz-woocommerce-tracker.zip` file and upload it to the WordPress admin dashboard.

## Compatibility

- WordPress: 4.1.0 (`script_loader_tag` added)
- PHP: 5.3.0 (use of `use` to make variable available inside closure added)
- WooCommerce: 3.0 (`wc_get_products` added)

#### Versions used for development

- WordPress: 6.6.2
- WooCommerce: 9.3.3
- MySQL: 8.0
- PHP: 8.2.24


## Docs

Docs
- https://woocommerce.github.io/code-reference/index.html
- https://wp-kama.com/plugin/woocommerce/function

Hooks
- https://woocommerce.github.io/code-reference/hooks/hooks.html
- https://wp-kama.com/plugin/woocommerce/hook
- https://developer.wordpress.org/apis/hooks/action-reference/
