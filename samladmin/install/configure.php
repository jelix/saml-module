<?php

use Jelix\Installer\Module\API\ConfigurationHelpers;
use Jelix\Routing\UrlMapping\EntryPointUrlModifier;
use Jelix\Routing\UrlMapping\MapEntry\MapInclude;

class samladminModuleConfigurator extends \Jelix\Installer\Module\Configurator
{

    public function getDefaultParameters()
    {
        return array(
        );
    }

    public function declareUrls(EntryPointUrlModifier $registerOnEntryPoint)
    {
        $registerOnEntryPoint->havingName(
            'admin',
            array(
                new MapInclude('urls.xml')
            )
        );
    }

    function configure(ConfigurationHelpers $helpers)
    {
    }
}