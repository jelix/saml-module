<?php

require_once(__DIR__.'/SamlInstallerTrait.php');

/**
 * Configurator for Jelix 1.7+
 */
class samlModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    use SamlInstallerTrait;

    public function getDefaultParameters()
    {
        return array(
            'useradmin' => '',
            'emailadmin' => '',
            // name of the entrypoint on which the saml authentication will
            // be routed
            'authep' => ''
        );
    }

    public function declareUrls(\Jelix\Routing\UrlMapping\EntryPointUrlModifier $registerOnEntryPoint)
    {
        if ($this->parameters['authep']) {
            $registerOnEntryPoint->havingName(
                $this->parameters['authep'],
                array(
                    new \Jelix\Routing\UrlMapping\MapEntry\MapInclude('urls.xml')
                )
            );

        }
        else {
            parent::declareUrls($registerOnEntryPoint);
        }

    }

    function configure(\Jelix\Installer\Module\API\ConfigurationHelpers $helpers)
    {
        $this->configHelpers = $helpers;

        $appConfig = $helpers->getConfigIni();
        $appConfig->setValue('auth.class', 'samlCoordPlugin', 'coordplugins');
        $appConfig->setValue('driver', 'saml', 'coordplugin_auth');

        // import default SAML configuration into localconfig or mainconfig
        $samlIniConfig = new \Jelix\IniFile\IniModifier(__DIR__.'/config.ini');
        if ($helpers->forLocalConfiguration()) {
            $localConfig = $appConfig['local'];
            $mainConfig = $appConfig['main'];
            // import configuration from mainconfig into localconfig
            // it can have parameters that are empty by default, and then
            // values from mainconfig should not be set with empty values into localconfig.
            // We should not overwrite parameters that are already set into both config files.
            foreach (['saml', 'saml:sp', 'saml:idp', 'saml:security', 'saml:attributes-mapping', 'saml:sp:requestedAttributes'] as $section) {
                if ($mainConfig->isSection($section)) {
                    $samlIniConfig->setValues($mainConfig->getValues($section), $section);
                }
                if ($localConfig->isSection($section)) {
                    $samlIniConfig->setValues($localConfig->getValues($section), $section);
                }
            }
            $localConfig->import($samlIniConfig);
            $appConfig->save();
        }
        else {
            $mainConfig = $appConfig['main'];
            foreach (['saml', 'saml:sp', 'saml:idp', 'saml:security', 'saml:attributes-mapping', 'saml:sp:requestedAttributes'] as $section) {
                if ($mainConfig->isSection($section)) {
                    $samlIniConfig->setValues($mainConfig->getValues($section), $section);
                }
            }
            $appConfig['main']->import($samlIniConfig);
            $appConfig->save();
        }

        // setup configuration for the saml driver into jauth
        $foundAuthConfig = false;
        foreach ($helpers->getEntryPointsByType() as $ep) {
            $pluginInfo = $ep->getCoordPluginConfig('auth');
            if (!$pluginInfo) {
                continue;
            }
            list($authIni, $section) = $pluginInfo;
            if ($section ==0) {
                // this is an auth.coord.ini.php
                if (!$authIni->isSection('saml')) {
                    $authIni->setValue('dao', "jauthdb~jelixuser", 'saml');
                    $authIni->setValue('profile', "", 'saml');
                    $authIni->setValue('form', "jauthdb_admin~jelixuser", 'saml');
                    $authIni->setValue('uploadsDirectory', '', 'saml');
                    $authIni->setValue('compatiblewithdb', true, 'saml');
                    $authIni->setValue('automaticAccountCreation', true, 'saml');
                    $authIni->setValue('driver', 'saml');
                    $authIni->save();
                }
                $foundAuthConfig = true;
            }
        }

        // there is no configuration yet, let's create one for us
        if (!$foundAuthConfig) {
            $authConfigFilePath = $helpers->configFilePath('saml/saml.coord.ini.php');
            if (!file_exists($authConfigFilePath)) {
                $helpers->copyFile('saml.coord.ini.php', $authConfigFilePath);
            }
            $appConfig->setValue('auth', 'saml/saml.coord.ini.php', 'coordplugins');
        }

        if (!$appConfig->getValue('htmlauth', 'responses')) {
            $appConfig->setValue('htmlauth', 'jResponseHtml', 'responses');
        }

        $this->updateCacheProfile('profiles.ini.php.dist');
    }
}