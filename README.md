[![WordPress tested 5.8](https://img.shields.io/badge/WordPress-v5.7%20tested-0073aa.svg)](#)  [![PHPStan ](.github/phpstan.svg)](https://github.com/szepeviktor/phpstan-wordpress)  [![PHPUnit ](.github/coverage.svg)](https://brianhenryie.github.io/bh-wp-account-and-login-ux/) [![PHPCS WPCS](https://img.shields.io/badge/PHPCS-WordPress%20Coding%20Standards-8892BF.svg)](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards)

# WP Account & Login UX

WooCommerce can be configured to require an account to checkout, but does not check for an existing account until the customer clicks "Place order". This plugin moves the customer email field to the beginning of the checkout form and if an account exists matching the email address entered, a login form is displayed inline.


![WooCommerce Checkout](./assets/bh-wp-account-and-login-ux-checkout.gif "WooCommerce checkout login changes")


This is a work-in-progress. Some obvious improvements needed:

* Loading icon over the billing email as it is checked