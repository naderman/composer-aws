<?php
/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 * Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Naderman\Composer\AWS;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;

/**
 * Composer Plugin for AWS functionality
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class S3RemoteFilesystem extends RemoteFilesystem
{
    /**
     * @var AwsClient
     */
    protected $awsClient;

    /**
     * {@inheritDoc}
     */
    public function __construct(IOInterface $io, Config $config = null, array $options = array(), AwsClient $awsClient)
    {
        parent::__construct($io, $config, $options);
        $this->awsClient = $awsClient;
    }

    /**
     * {@inheritDoc}
     */
    public function getContents($originUrl, $fileUrl, $progress = true, $options = array())
    {
        return $this->awsClient->download($fileUrl, $progress);
    }

    /**
     * {@inheritDoc}
     */
    public function copy($originUrl, $fileUrl, $fileName, $progress = true, $options = array())
    {
        $this->awsClient->download($fileUrl, $progress, $fileName);
    }
}
