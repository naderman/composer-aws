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

namespace Composer\AWS;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;

/**
 * Composer Plugin for AWS functionality
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class S3RemoteFilesystem extends RemoteFilesystem
{
    protected $awsClient;

    public function __construct(IOInterface $io, $options, AwsClient $awsClient)
    {
        parent::__construct($io, $options);
        $this->awsClient = $awsClient;
    }

    public function copy($originUrl, $fileUrl, $fileName, $progress = true, $options = array())
    {
        $this->awsClient->download($fileUrl, $fileName, $progress);
    }
}
