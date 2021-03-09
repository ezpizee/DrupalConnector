var EzpzOverrideEndpoints = {
  "refresh_token": "/ezpizee/api/client?endpoint=/api/drupal/refresh/token",
  "expire_in": "/ezpizee/api/client?endpoint=/api/drupal/expire-in",
  "get_auth": "/ezpizee/api/client?endpoint=/api/drupal/authenticated-user",
  "loginPageRedirectUrl": "{loginPageRedirectUrl}",
  "installPageRedirectUrl": "/",
  "csrfToken": "/ezpizee/api/client?endpoint=/api/drupal/crsf-token",
  "scriptUrlRegex": /^(?:http|https):\/\/[^/]+(\/.*)\/(\/sites\/default\/).*\.js(\?.*)?$/
};
