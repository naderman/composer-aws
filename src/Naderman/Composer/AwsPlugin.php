<?php

namespace Naderman\Composer;

class AwsPlugin implements \Composer\Plugin\PluginInterface
{
    public function activate(\Composer\Composer $composer)
    {
        echo "\n\nfoo\n\n";
    }
}
