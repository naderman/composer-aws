# composer-aws

A composer plugin to download packages from Amazon S3 which may be used for private repositories.

## Installation

### Global scope (per user) installation

```shell
$ composer global require "naderman/composer-aws:~0.2"
```

### Project scope installation

`/!\` This way needs you perform pluggin installation BEFORE to add packages links using S3

```shell
$ composer require "naderman/composer-aws:~0.2"
```

## Configuration

There are three options available in order to configure and use this plugin:

 1. For AWS EC2: Create an IAM profile for your instances to access the bucket - then no other configuration is necessary.
 2. Set the environment variables `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`
 3. Add the following to your `config.json` (in `$COMPOSER_HOME`):

```json
{
    "config": {
        "amazon-aws": {
            "key": "your aws access key",
            "secret": "your aws secret"
        }
    }
}
```

## Usage

Once the pluggin is well installed and configured, you can transparently use `packages.json` files using `s3://` schemes for dist urls.

## Further reading

 * Setting up satis as a mirror: http://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#downloads
 * AWS IAM Instance profiles: http://docs.aws.amazon.com/IAM/latest/UserGuide/instance-profiles.html
 * Amazon S3 Syncer: https://github.com/easybiblabs/s3-syncer
