# composer-aws

A composer plugin to download packages from a private repository.

## Configuration

There are three options available in order to configure and use this plugin:

 1. For AWS EC2: Create an IAM profile for your instances to access the bucket - then no other configuration is necessary.
 2. Set the environment variables `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY`
 3. Add the following to your `config.json` (in `$COMPOSER_HOME`):

```json
{
    "aws-config": {
        "key": "your aws access key",
        "secret": "your aws secret"
    }
}
```

## Usage

Add the plugin to your project's `composer.json`:

```json
{
    "require": {
        "naderman/composer-aws": "*"
    }
}
```

## Further reading

 * Setting up satis as a mirror: http://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#downloads
 * AWS IAM Instance profiles: http://docs.aws.amazon.com/IAM/latest/UserGuide/instance-profiles.html
 * Amazon S3 Syncer: https://github.com/easybiblabs/s3-syncer
