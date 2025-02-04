# Application Configuration

The application is configurable by [Symfony configuration files](https://symfony.com/doc/4.4/configuration.html#configuration-parameters) or via [environment variables](https://symfony.com/doc/4.4/configuration.html#configuration-environments) which allows you to overwrite them.

## Configuration parameters

For operating Shopsys Platform it is needed to have correctly set connections to external services via ENV variables.

!!! note
    All default values use default ports for all external services like PostgreSQL database, elasticsearch, redis, ...

!!! tip
    Host values can be modified or can be aliased for your Operating System via `/etc/hosts` or `C:\Windows\System32\drivers\etc\hosts`


Environment variables are really handy to configure the right setting in the desired application environment.
You may want to set some settings in a different way (such as production, test, or CI servers).
[Setting environment variables](/introduction/setting-environment-variables) depends on environment of your application.

!!! tip
    To improve performance you can optionally run `composer dump-env`. [See Symfony documentation for further information.](https://symfony.com/doc/4.4/configuration.html#configuring-environment-variables-in-production)

### Application

| Name                                  | Default                            | Description                                                                                                                                    |
|---------------------------------------|------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------|
| `DATABASE_HOST`                       | `'postgres'`                       | access data of your PostgreSQL database                                                                                                        |
| `DATABASE_PORT`                       | `null`                             | ...                                                                                                                                            |
| `DATABASE_NAME`                       | `'shopsys'`                        | ...                                                                                                                                            |
| `DATABASE_USER`                       | `'root'`                           | ...                                                                                                                                            |
| `DATABASE_PASSWORD`                   | `'root'`                           | ...                                                                                                                                            |
| `ELASTICSEARCH_HOST`                  | `'elasticsearch:9200'`             | host of your Elasticsearch, you can use multiple hosts like `'["elasticsearch:9200", "elasticsearch2:9200"]'`                                  |
| `REDIS_HOST`                          | `'redis'`                          | host of your Redis storage (credentials are not supported right now)                                                                           |
| `REDIS_PREFIX`                        | `''`                               | separates more projects that use the same redis service                                                                                        |
| `MAILER_DSN`                          | `smtp://smtp-server:25`            | set to `null://null` if you don't want to send any emails, see https://symfony.com/doc/current/mailer.html#disabling-delivery                  |
| `APP_SECRET`                          | `'ThisTokenIsNotSoSecretChangeIt'` | randomly generated secret token                                                                                                                |
| `ELASTIC_SEARCH_INDEX_PREFIX`         | `''`                               | separates more projects that use the same elasticsearch service                                                                                |
| `IGNORE_DEFAULT_ADMIN_PASSWORD_CHECK` | `'0'`                              | set to `true` if you want to allow administrators to log in with default credentials                                                           |
| `OVERWRITE_DOMAIN_URL`                | `'http://webserver:8080'`          | overwrites URL of all domains for acceptance testing (set to `~` to disable)                                                                   |
| `SELENIUM_SERVER_HOST`                | `'selenium-server'`                | with native installation the selenium server is on `localhost`                                                                                 |
| `SHOPSYS_CONTENT_DIR_NAME`            | `'content-test'`                   | web/content-test/ directory is used instead of web/content/ during the tests                                                                   |
| `TRUSTED_PROXIES`                     | `'127.0.0.1'`                      | proxies that are trusted to pass traffic, used mainly for production (set as text separated by comma for multiple values)                      |
| `CDN_DOMAIN`                          | `'//'`                             | specifies URL of a Content Delivery Network (CDN) that is used to serve static assets such as images, CSS, and JavaScript files                |


### Google Cloud Bundle

These variables are specific for [shopsys/google-cloud-bundle](https://github.com/shopsys/google-cloud-bundle)

| Name                               | Default | Description                                 |
|------------------------------------|---------|---------------------------------------------|
| `GOOGLE_CLOUD_PROJECT_ID`          | `''`    | defines Google Cloud Project ID             |
| `GOOGLE_CLOUD_STORAGE_BUCKET_NAME` | `''`    | defines Bucket Name in Google CLoud Storage |


### S3 Bridge Bundle

These variables are exclusively usable with the [shopsys/s3-bridge](https://github.com/shopsys/s3-bridge) and are required only when using this package.

| Name             | Default        | Description                                                                      |
|------------------|----------------|----------------------------------------------------------------------------------|
| `S3_ENDPOINT`    | `''`           | URL of the S3 service endpoint                                                   |
| `S3_REGION`      | `''`           | AWS region where the S3 bucket is located (usually empty for custom S3 services) |
| `S3_ACCESS_KEY`  | `''`           | access key ID for the S3 service                                                 |
| `S3_SECRET`      | `''`           | secret access key for the S3 service                                             |
| `S3_BUCKET_NAME` | `''`           | name of the S3 bucket to access or manipulate                                    |
| `S3_VERSION`     | `'2006-03-01'` | version of the webservice to utilize                                             |
