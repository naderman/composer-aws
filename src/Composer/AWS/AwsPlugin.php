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
use Composer\Plugin\PluginInterface

/**
 * Composer Plugin for AWS functionality
 *
 * @author Nils Adermann <naderman@naderman.de>
 */
class AwsPlugin implements PluginInterface
{
    public function activate(Composer $composer)
    {
        echo "\n\nfoo\n\n";
    }
}
