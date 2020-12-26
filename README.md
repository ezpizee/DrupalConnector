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
 * Drupal 8
 * Ezpizee's subscription


INSTALLATION
------------

 1) Download the module, or pulled, and placed under */modules/custom* folder
 
 2) At the root of your Drupal environment, run the following composer commands, to install the dependencies:
   * *composer require mashape/unirest-php*
   * *composer require ezpizee/connector-utils "dev-master"*
   * *composer require ezpizee/utils "dev-master"*
   * *composer require ezpizee/microservices-utils "dev-master"*
   
 3) Go to *Manage &gt; Extend (/admin/modules)*
   * Check *Ezpizee API Client on Drupal*
   * Check *Ezpizee Portal on Drupal*
   * Click *Install* button (bottom of the page)

 4) Go to *Manage &gt; Configuration (/admin/config)*
   * Under *WEB SERVICES*, click on *Ezpizee Portal*
   * Fill in the value for Client ID, Client Secret, App Name
   * Select the Environment to integrate with Ezpizee (if not sure, select *Production*)
   * Click *Save Configuration*


MAINTAINERS
-----------

Current maintainers:
 * Sothea Nim - https://www.ezpizee.com/en/about/team/sothea-nim.html
 * Sokhon Pang - https://www.ezpizee.com/en/about/team/sokhon-pang.html

