CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Maintainers


INTRODUCTION
------------

Ezpizee Drupal Connector intends to be used for integrating Ezpizee, SaaS based e-commerce platform, with Drupal,
providing a fast and full access to all administration links.

 * For a full description of the module, visit the project page:
   https://github.com/ezpizee/DrupalConnector

 * To submit bug reports and feature suggestions, or to track changes:
   https://github.com/ezpizee/DrupalConnector/issues


REQUIREMENTS
------------

 * PHP 7.x
 * Drupal 8.x or later
 * Ezpizee's subscription


INSTALLATION
------------

1) Download the latest release from our repository https://github.com/ezpizee/DrupalConnector and unzip
 
2) Log in to your Drupal instance as admin
 
3) Go to **Manage &gt; Extend**
 
4) Click the "**+ Install new module**" button
 
5) Choose the **/dist/ezpz.zip** from your unzipped folder to upload
 
6) Click the **Install** button

7) Go to *Manage &gt; Configuration (/admin/config)*
    * Under **WEB SERVICES**, click on **Ezpizee Portal**
    * Fill in the value for 
      * **Client ID** (obtain from https://www.ezpizee.com/en/user/admin-ui.html),
      * **Client Secret** (obtain from https://www.ezpizee.com/en/user/admin-ui.html),
      * **App Name** (a unique name that is not already been used in any other Ezpizee installation)
    * Select the Environment to integrate with Ezpizee (when not sure, select **Production**)
    * Click **Save Configuration**

8) Click on the *Ezpizee Portal* from Drupal admin menu


INSTALLATION TROUBLESHOOT
------------------------

If you get an error when installing (i.e. "The website encountered an unexpected error. Please try again later."),
you will need to install manually

1) Download the latest release from our repository https://github.com/ezpizee/DrupalConnector and unzip

2) Unzip **/dist/ezpz.zip**

3) Upload the folder **/dist/ezpz** to **{/path/to/your/drupal}/modules/custom**

4) Login to your Drupal as admin

5) Go to **Manage &gt; Extend &gt; Install**

6) Check both **Ezpizee Portal on Drupal** and **Ezpizee API Client on Drupal**

7) Click **Install** button at the end of the page

8) You should now see the following success message
   
   ```
   2 modules have been enabled: Ezpizee API Client on Drupal, Ezpizee Portal on Drupal.
   ```

9) Go to *Manage &gt; Configuration (/admin/config)*
    * Under **WEB SERVICES**, click on **Ezpizee Portal**
    * Fill in the value for 
      * **Client ID** (obtain from https://www.ezpizee.com/en/user/admin-ui.html),
      * **Client Secret** (obtain from https://www.ezpizee.com/en/user/admin-ui.html),
      * **App Name** (a unique name that is not already been used in any other Ezpizee installation)
    * Select the Environment to integrate with Ezpizee (when not sure, select **Production**)
    * Click **Save Configuration**

10) Click on the *Ezpizee Portal* from Drupal admin menu

MAINTAINERS
-----------

Current maintainers:
 * Sothea Nim - https://github.com/nimsothea
 * Sokhon Pang - https://github.com/pangkhon

