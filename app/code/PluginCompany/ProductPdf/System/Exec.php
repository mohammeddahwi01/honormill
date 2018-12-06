<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 */
namespace PluginCompany\ProductPdf\System;

class Exec
{
    private $phpExecutableList =
        array(
            'php-cli',
            'php5-cli',
            'php5.6-cli',
            'php7-cli',
            'php7.0-cli',
            'php7.1-cli',
            'php'
        );

    private $execMethodName;

    public function isExecEnabled()
    {
        return (bool)$this->getExecMethodName();
    }

    private function getExecMethodName()
    {
        if (!$this->hasExecMethodName()) {
            $this->initExecMethodName();
        }
        return $this->execMethodName;
    }

    private function hasExecMethodName()
    {
        return isset($this->execMethodName);
    }

    private function initExecMethodName()
    {
        if(@exec('echo EXEC') == 'EXEC'){
            return $this->execMethodName = 'exec';
        }
        if(@shell_exec('echo EXEC') == "EXEC\n"){
            return $this->execMethodName = 'shell_exec';
        }
        $this->execMethodName = false;
    }

    public function execute($command, $background = false)
    {
        if($background){
            $background = ' > /dev/null 2>&1 &';
        }
        if(!$this->getExecMethodName()){
            throw new \Exception("Exec is disabled. Please enable exec or shell_exec in php.ini.");
        }
        if($this->getExecMethodName() == 'exec'){
            return @exec($command . $background);
        }
        if($this->getExecMethodName() == 'shell_exec'){
            return @shell_exec($command . $background);
        }
        return $this;
    }

    public function executePHP($command, $background = '')
    {
        return $this->execute($this->getPhpExecutable() . ' ' . $command, $background);
    }

    public function getPhpExecutable()
    {
        foreach($this->phpExecutableList as $executable) {
            if($this->getExecutable($executable)){
                return $this->getExecutable($executable);
            }
        }
        return 'php';
    }

    private function getExecutable($executable)
    {
        $path = $this->execute('which ' . $executable);
        if(is_array($path)){
            $path = $path[0];
        }
        if(strpos($path, '/') === 0){
            return $path;
        }
        return false;
    }

}