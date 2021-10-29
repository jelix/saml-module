<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class configCtrl extends jController
{
    // Configure access via jacl2 rights management
    public $pluginParams = array(
        '*' => array('jacl2.right' => 'saml.config.access'),
    );


    /**
     * Display a summary of the information taken from the ~ configuration file.
     */
    public function index()
    {
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $tpl->assign('sp_metadata_url', jUrl::getFull('saml~endpoint:metadata'));


        $rep->body->assign('MAIN', $tpl->fetch('config'));
        $rep->body->assign('selectedMenuItem', 'samlconfig');

        return $rep;
    }


    public function asset() {

        $rep = $this->getResponse('binary');
        $rep->doDownload = false;
        $dir = __DIR__.'/../www/';
        $rep->fileName = realpath($dir.str_replace('..', '', $this->param('file')));

        if (!is_file($rep->fileName)) {
            $rep = $this->getResponse('html', true);
            $rep->bodyTpl = 'jelix~404.html';
            $rep->setHttpStatus('404', 'Not Found');
            return $rep;
        }
        $rep->mimeType = jFile::getMimeTypeFromFilename($rep->fileName);
        return $rep;
    }
}
