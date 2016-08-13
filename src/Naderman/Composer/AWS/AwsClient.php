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

namespace Naderman\Composer\AWS;

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
     * @var S3Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $awsConfig;

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
     * @return array
     */
    public function getAwsConfig()
    {
        if (is_null($this->awsConfig)) {
            if (isset($_SERVER['HOME']) && file_exists($_SERVER['HOME'] . '/.aws/config')) {
                $this->awsConfig = parse_ini_file($_SERVER['HOME'] . '/.aws/config', true);
            } else {
                $this->awsConfig = [];
            }
        }
        
        return $this->awsConfig;
    }

    /**
     * @param array $config
     * @return AwsClient
     */
    public function setAwsConfig($config)
    {
        $this->awsConfig = $config;
        return $this;
    }

    /**
     * @param string $url      URL of the archive on Amazon S3.
     * @param bool   $progress Show progress
     * @param string $to       Target file name
     *
     * @throws \Composer\Downloader\TransportException
     */
    public function download($url, $progress, $to = null)
    {
        list($bucket, $key) = $this->determineBucketAndKey($url);

        if ($progress) {
            $this->io->write("    Downloading: <comment>connection...</comment>", false);
        }

        try {
            $params = array(
                'Bucket'                => $bucket,
                'Key'                   => $key
            );

            if ($to) {
                $params['command.response_body'] = \Guzzle\Http\EntityBody::factory(
                    fopen($to, 'w+')
                );
            }
    
            $s3     = $this->s3factory($this->config);
            $result = $s3->getObject($params);

            if ($progress) {
                $this->io->overwrite("    Downloading: <comment>100%</comment>");
            }

            if ($to) {
                if (false === file_exists($to) || !filesize($to)) {
                    $errorMessage = sprintf(
                        "Unknown error occurred: '%s' was not downloaded from '%s'.",
                        $key,
                        $url
                    );
                    throw new TransportException($errorMessage);
                }
            } else {
                return $result['Body'];
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

        return $this;
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
        if (!$key) {
            $key = '/';
        }
        return array($bucket, $key);
    }

    /**
     * This method reads AWS config and credentials and create s3 client
     * Behaviour aims to mimic region setup as it is for credentials:
     * http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/basic-usage.html#creating-a-client
     * Which is the following (stopping at the first successful case):
     * 1) read region from config parameter
     * 2) read region from environment variables
     * 3) read region from profile config file
     * 4) set region US Standard region
     * 
     * @param \Composer\Config $config
     *
     * @return \Aws\S3\S3Client
     */
    public function s3factory(Config $config)
    {
        if (is_null($this->client)) {
            $s3config = array(
                'version' => 'latest'
            );
    
            /**
             * If these are not set and we happen to have an IAM profile, it will still work.
             */
            if (($composerAws = $config->get('amazon-aws'))) {
                $s3config = array_merge($s3config, $composerAws);
            }
            
            if (!isset($s3config['profile']) && getenv('AWS_DEFAULT_PROFILE')) {
                $s3config['profile'] = getenv('AWS_DEFAULT_PROFILE');
            }
            
            if (!isset($s3config['region'])) {
                $this->detectRegion($s3config);
            }
            
            $this->client = new S3Client($s3config);
        }

        return $this->client;
    }
    
    public function detectRegion(array &$config)
    {
        if (getenv('AWS_DEFAULT_REGION')) {
            $config['region'] = getenv('AWS_DEFAULT_REGION');
        } elseif (isset($config['profile'])) {
            $awsConfig = $this->getAwsConfig();
            if (isset($awsConfig['profile ' . $config['profile']]) &&
                isset($awsConfig['profile ' . $config['profile']]['region'])) {
                $config['region'] = $awsConfig['profile ' . $config['profile']]['region'];
            // support aws CLI <1.1.0 config file format
            } elseif (isset($awsConfig[$config['profile']]) &&
                isset($awsConfig[$config['profile']]['region'])) {
                $config['region'] = $awsConfig[$config['profile']]['region'];
            }
        }
        
        if (!isset($config['region'])) {
            $config['region'] = 'us-east-1';
        }
    }
    
}
