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
use Composer\EventDispatcher\EventSubscriberInterface;
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
    public function activate(Composer $composer)
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
        echo "\n\nfoo\n\n";
    }
}
