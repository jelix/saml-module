<?php
require_once(__DIR__.'/../app/application.init.php');
jApp::setEnv('jelixtests');
if (file_exists(jApp::tempPath())) {
    jAppManager::clearTemp(jApp::tempPath());
} else {
    jFile::createDir(jApp::tempPath(), intval("775",8));
}
