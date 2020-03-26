# Drupal 8 Composer Template

This repository is a reference implementation and start state for our opinionated, modern Drupal 8 workflow utilizing [Composer](https://getcomposer.org/) and Pantheon.

## Important files and directories

### `/web`

Pantheon will serve the site from the `/web` subdirectory due to the configuration in `pantheon.yml`. This is necessary for a Composer-based workflow. Having your website in this subdirectory also allows for tests, scripts, and other files related to your project to be stored in your repo without polluting your web document root or being web accessible from Pantheon. They may still be accessible from your version control project if it is public. See [the `pantheon.yml`](https://pantheon.io/docs/pantheon-yml/#nested-docroot) documentation for details.

#### `/config`

One of the directories moved to the git root is `/config`. This directory holds Drupal's `.yml` configuration files. In more traditional repo structure these files would live at `/sites/default/config/`. Thanks to [this line in `settings.php`](https://github.com/pantheon-systems/example-drops-8-composer/blob/54c84275cafa66c86992e5232b5e1019954e98f3/web/sites/default/settings.php#L19), the config is moved entirely outside of the web root.

### `composer.json`

This project uses Composer to manage third-party PHP dependencies.

The `require` section of `composer.json` should be used for any dependencies your web project needs, even those that might only be used on non-Live environments. All dependencies in `require` will be pushed to Pantheon.

The `require-dev` section should be used for dependencies that are not a part of the web application but are necessary to build or test the project. Some examples are `php_codesniffer` and `phpunit`.

This project uses the following required dependencies:

- **composer/installers**: Relocates the installation location of certain Composer projects by type; for example, this component allows Drupal modules to be installed to the `modules` directory rather than `vendor`.

- **drupal/core-composer-scaffold**: Allows certain necessary files, e.g. index.php, to be copied into the required location at installation time.

- **drupal/core-recommended**: This package contains Drupal itself, including the Drupal scaffold files.

- **pantheon-systems/drupal-integrations**: This package provides additional scaffold files required to install this site on the Pantheon platform. These files do nothing if the site is deployed elsewhere.

The following optional dependencies are also included as suggestions:

- **pantheon-systems/quicksilver-pushback**: This component allows commits from the Pantheon Dashboard to be automatically pushed back to GitHub for sites using the Build Tools Workflow. This package does nothing if that workflow has not been set up for this site.

- **drush/drush**: Drush is a commandline tool that provides ways to interact with site maintenance from the command line.

- **drupal/console**: Drupal Console is similar to and an alternative for Drush. You may use either or both.

- **cweagans/composer-patches**: Allows a site to be altered with patch files at installation time.

- **drupal/config_direct_save**: Provides a way to export configuration directly to the filesystem (in SFTP mode) directly from the Drupal admin interface. This is a convenient way to manage configuration files.

- **drupal/config_installer**: Allows a site to be re-installed through the Drupal web installer using existing exported configuration files.

- **drush-ops/behat-drush-endpoint**: Used by Behat tests.

- **rvtraveller/qs-composer-installer**: Allows a site to install quicksilver hooks from a Composer package.

- **zaporylie/composer-drupal-optimizations**: This package makes `composer update` operations run more quickly when updating Drupal and Drupal's dependencies.

Any of the optional dependencies may be removed if they are not needed or desired.

## Updating your site

When using this repository to manage your Drupal site, you will no longer use the Pantheon dashboard to update your Drupal version. Instead, you will manage your updates using Composer. Ensure your site is in Git mode, clone it locally, and then run `composer` commands from there.  Commit and push your files back up to Pantheon as usual.
