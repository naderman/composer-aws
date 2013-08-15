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
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PrepareRemoteFilesystemEvent;

/**
 * Composer Plugin for AWS functionality
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class AwsPlugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function activate()
    {
    }

    public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::PREPARE_REMOTE_FILESYSTEM => array(
                array('onPrepareRemoteFilesystem', 0)
            ),
        );
    }

    public function onPrepareRemoteFilesystem(PrepareRemoteFilesystemEvent $event)
    {
        $protocol = parse_url($event->getProcessedUrl(), PHP_URL_SCHEME);

        if ($protocol === 's3') {
            $awsClient = new AwsClient($this->io, $this->composer->getConfig());
            $s3RemoteFilesystem = new S3RemoteFilesystem($this->io, $event->getRemoteFilesystem()->getOptions(), $awsClient);
            $event->setRemoteFilesystem($s3RemoteFilesystem);
        }
    }
}
