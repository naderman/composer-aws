<?php
/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\AWS;

use Composer\IO\IOInterface;
use Composer\Config;
use Composer\Downloader\TransportException;
use Composer\Json\JsonFile;

use Aws\S3\S3Client;

/**
 * @author Till Klampaeckel <till@php.net>
 * @author Nils Adermann <naderman@naderman.de>
 */
class AwsClient
{
    /**
     * @var \Composer\Config
     */
    protected $config;

    /**
     * @var \Composer\IO\IOInterface
     */
    protected $io;

    /**
     * @param \Composer\IO\IOInterface $io
     * @param \Composer\Config         $config
     */
    public function __construct(IOInterface $io, Config $config)
    {
        $this->io       = $io;
        $this->config   = $config;
    }

    /**
     * @param string $url URL of the archive on Amazon S3.
     * @param string $to  Location on disk.
     * @param bool                     $progress
     *
     * @throws \Composer\Downloader\TransportException
     */
    public function download($url, $to, $progress)
    {
        list($bucket, $key) = $this->determineBucketAndKey($url);

        if ($progress) {
            $this->io->write("    Downloading: <comment>connection...</comment>", false);
        }

        try {
            $s3 = self::s3factory($this->config);
            $s3->getObject(
                array(
                    'Bucket'                => $bucket,
                    'Key'                   => $key,
                    'command.response_body' => \Guzzle\Http\EntityBody::factory(
                        fopen($to, 'w+')
                    )
                )
            );

            if ($progress) {
                $this->io->overwrite("    Downloading: <comment>100%</comment>");
            }

            if (false === file_exists($to) || !filesize($to)) {
                $errorMessage = sprintf(
                    "Unknown error occurred: '%s' was not downloaded from '%s'.",
                    $key, $url
                );
                throw new TransportException($errorMessage);
            }

        } catch (\Aws\Common\Exception\InstanceProfileCredentialsException $e) {
            $msg = "Please add key/secret into config.json or set up an IAM profile for your EC2 instance.";
            throw new TransportException($msg, 403, $e);
        } catch(Aws\S3\Exception\S3Exception $e) {
            throw new TransportException("Connection to Amazon S3 failed.", null, $e);
        } catch (TransportException $e) {
            throw $e; // just re-throw
        } catch (\Exception $e) {
            throw new TransportException("Problem?", null, $e);
        }
    }

    /**
     * @param string $url
     *
     * @return array
     */
    public function determineBucketAndKey($url)
    {
        $hostName = parse_url($url, PHP_URL_HOST);
        $path     = substr(parse_url($url, PHP_URL_PATH), 1);

        $parts = array();
        if (!empty($path)) {
            $parts = explode('/', $path);
        }

        if ('s3.amazonaws.com' !== $hostName) {
            // replace potential aws hostname
            array_unshift($parts, str_replace('.s3.amazonaws.com', '', $hostName));
        }
        $bucket = array_shift($parts);
        $key = implode('/', $parts);
        return array($bucket, $key);
    }

    /**
     * @param \Composer\Config $config
     *
     * @return \Aws\S3\S3Client
     */
    public static function s3factory(Config $config)
    {
        $s3config = array(
            'default_cache_config'  => '',
            'certificate_authority' => '',
        );

        /**
         * If these are not set and we happen to have an IAM profile, it will still work.
         */
        if (($composerAws = $config->get('amazon-aws'))) {
            $s3config = array_merge($s3config, $composerAws);
        }

        return S3Client::factory($s3config);
    }
}
