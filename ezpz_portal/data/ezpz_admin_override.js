var EzpzOverrideEndpoints = {
  "refresh_token":"/ezpizee/api/client?endpoint=/api/v1/drupal/refresh/token",
  "expire_in":"/ezpizee/api/client?endpoint=/api/v1/drupal/expire-in",
  "get_auth":"/ezpizee/api/client?endpoint=/api/v1/drupal/authenticated-user",
  "loginPageRedirectUrl":"{loginPageRedirectUrl}",
  "installPageRedirectUrl":"/",
  "csrfToken": "/ezpizee/api/client?endpoint=/api/v1/drupal/crsf-token",
  "scriptUrlRegex": /^(?:http|https):\/\/[^/]+(\/.*)\/(\/sites\/default\/).*\.js(\?.*)?$/
};
