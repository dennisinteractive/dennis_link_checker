# dennis_link_checker
Monitors links to reduce redirects

## Configuration:
##### The site URL for scanning can be configured at:
`/admin/config/system/link-checker/config`

## Drush commands:

`drush link-checker:link <optional comma separated list of nids>`

`drush link-checker:asset <optional comma separated list of nids>`

##### These drush commands have corresponding cron jobs.

## PHPUnit:
`../vendor/bin/phpunit -c core modules/contrib/dennis_link_checker --group Link_checker`
