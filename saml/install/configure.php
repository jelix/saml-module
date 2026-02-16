<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2022-2026 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once(__DIR__.'/SamlInstallerTrait.php');

/**
 * Configurator
 */
class samlModuleConfigurator extends \Jelix\Installer\Module\Configurator
{
    use SamlInstallerTrait;

    public function getDefaultParameters()
    {
        return array(
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
        $appConfig = $helpers->getConfigIni();

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

        if (!$appConfig->getValue('htmlauth', 'responses')) {
            $appConfig->setValue('htmlauth', 'jResponseHtml', 'responses');
        }

        $this->updateCacheProfile('profiles.ini.php.dist');
    }
}