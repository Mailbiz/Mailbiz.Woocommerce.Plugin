# Mailbiz WooCommerce Plugin

This plugin adds support to WooCommerce stores for integrating with Mailbiz's Open Tracker.

The plugin adds a configuration panel to the WooCommerce settings page to allow the Mailbiz's support team to add the user credentials.
Once this is set up, the plugin will automatically add the tracking scripts to the WooCommerce store.

## Getting Started

### Prerequisites

- Docker
- Docker Compose
- Yarn

### Installation

#### 1. Clone and install:
  - `git clone https://github.com/Mailbiz/Mailbiz.Woocommerce.Plugin; cd Mailbiz.Woocommerce.Plugin;`
  - `yarn install`

#### 2. Set your .env file
  Copy the `.env.example` file to `.env` and set the desired variables.
  
  <sub>(If you change the `PORT` after installing WP you might need to [reset the WordPress installation](#resetting-the-wordpress-installation))</sub>

#### 3. Start the Docker containers:

  - `yarn dev`

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
  - Search for `WooCommerce` and click on `Install Now` <br />
  *This might take a few minutes*
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

<sub>It is a good idea to add the maximum amount of data to the product to test every possibility. This includes adding products with and without variations.</sub>

#### 9. (optional) Add a payment method:
  - Access `http://localhost:60000/wp-admin/admin.php?page=wc-admin&task=payments`
  - Add "Cash on delivery" payment method

#### 10. (optional) Add a shipping method:
  - Access `http://localhost:60000/wp-admin/admin.php?page=wc-settings&tab=shipping`
  - Add a "Flat rate" shipping method for a region or all regions

#### 11. (optional) Set site visibility:
  - Access `[http://localhost:60000/wp-admin/admin.php?page=wc-settings&tab=shipping](http://localhost:60000/wp-admin/admin.php?page=wc-settings&tab=site-visibility)`
  - Set `Live` and save. changes.

#### 12. (optional) Enable WordPress debugging:
  - Set `define('WP_DEBUG', true);` in `docker-volumes/wordpress/wp-config.php` to enable debugging

#### 13. (optional) Configure xdebug (to debug PHP code):
  - Set the `XDEBUG_CLIENT_HOST` environment variable as your internal IP.
  - Also Rebuild docker images as this variable is used in `docker-compose.yml`: `yarn dev --build`
  - Install the [PHP Debug](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug) extension in VSCode (**don't forget to add breakpoints**).
  - Install the [Xdebug Helper](https://chromewebstore.google.com/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc) extension in Chrome (**don't forget to enable debug**).

*If debug not working, you can:*
  - Set the `XDEBUG_LOG_LEVEL` environment variable to `7` and rebuild image.
  - Check `/tmp/xdebug.log` inside container.
  - Check docs: https://xdebug.org/docs/

### Resetting the WordPress installation
- Delete the docker container
- Delete the `docker-volumes` directory
- Run `yarn dev --build`.

### Manual testing

- Navigate to a product page and add it to the cart. Event calls should be printed upon page reload.

## Project Structure

- `README.md`: This file.
- `docker-compose.yml`: Docker Compose configuration file to set up WordPress, MySQL and folder mapping.
- `docker-volumes`: Mapped directories for docker. Not versioned.
- `docker-volumes/db_data`: Directory for the MySQL database.
- `docker-volumes/wordpress`: Directory for the WordPress installation.
- `docker-volumes/plugins`: WordPress plugins folder.
- `src/`: Source code. Already mapped to the WordPress plugins folder as `mailbiz-woocommerce-tracker`.
- `tests/`: Tests. Already mapped to the `tests` image for running tests.

## Development

After setting up the environment, you can start developing. Any changes made to the plugin files in the `src/` directory will be reflected in your WordPress installation.

## Unit tests

- `yarn test`

### Docs

https://github.com/mockery/mockery
https://github.com/php-mock/php-mock-mockery

## Build / production (wip)

- `yarn build`

Or: copy the code inside the `src/*` directory to `mailbiz-woocommerce-tracker.zip` file and upload it to the WordPress admin dashboard.

### Bumping the version

- `package.json`
- `/src/mailbiz-woocommerce-tracker.php`

### Build

- `yarn build`

<sub>Or: copy the code inside the `src/*` directory to `mailbiz-woocommerce-tracker.zip` file.</sub>
- Create a tag `git tag -a v1.0.0 -m "Version 1.0.0"`
- Push the tag `git push origin v1.0.0`
- Create a release on GitHub and upload the built .zip file

## Compatibility

- WordPress: 4.1.0 (`script_loader_tag` added)
- PHP: 5.3.0 (use of `use` to make variable available inside closure added) (use of `namespace`)
- WooCommerce: 3.0 (`wc_get_products` added)

### Updating minimum requirements

- Update the `Requires at least` fields in the `/src/mailbiz-woocommerce-tracker.php` file.

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
