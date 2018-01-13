<?php

/*
 *
 *   _____                 ____ ____  _  ___
 *  | ____|_ __   ___  ___| ___| ___|/ |/ _ \
 *  |  _| | '_ \ / _ \/ __|___ \___ \| | (_) |
 *  | |___| | | |  __/\__ \___) |__) | |\__, |
 *  |_____|_| |_|\___||___/____/____/|_|  /_/
 *
 *  Lütfen yazılımı izinsiz dağıtmayınız.
 *
 */

declare(strict_types=1);

namespace Enes5519\APIConverter;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;

class APIConverter extends PluginBase{

    public function onEnable(){
        @mkdir($this->getDataFolder());
        $this->getServer()->getLogger()->directSend("\n§8» §bThank you for using Turanic API Converter ♥ by Enes5519.\n§8» §eThis plugin is only for plugins that are in source plugins there are not compiled.\n");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args){
        if($sender->isOp() && count($args) > 0){

            $path = $this->getServer()->getPluginPath();
            if($args[0] == "--all"){
                $this->getLogger()->info("Start converting all plugins...\n");
                $plugins = scandir($path);
                foreach($plugins as $pl){
                    if($pl == "." or $pl == "..") continue;
                    if(is_dir($path . $pl)){
                        $this->convertPlugin($path.$pl);
                    }
                }
            }else{
                $pluginPath = $path.DIRECTORY_SEPARATOR.$args[0];
                if(file_exists($pluginPath)){
                    $this->convertPlugin($pluginPath);
                }else{
                    $this->getLogger()->critical("This directory not found => $pluginPath");
                }
            }
        }
    }

    public function convertPlugin($file){
        if(is_dir($file) and file_exists($file . "/plugin.yml") and file_exists($file . "/src/")){
            $plugin = $this->getPluginDescription($file);
            if($plugin instanceof PluginDescription){
                $pluginName = $plugin->getName();
                if($pluginName == $this->getDescription()->getName()) return false;
                $this->getLogger()->notice("Converting API for $pluginName started.");
                $this->createBackup($file, $pluginName);

                $objects = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(realpath($file), \RecursiveIteratorIterator::SELF_FIRST));
                /**
                 * @var string $name
                 * @var \SplFileInfo $object
                 */
                foreach ($objects as $name => $object){
                    if($object->getExtension() == "php"){
                        $dosya = file_get_contents($object->getRealPath());
                        // PHP 7.2 to PHP 7.0
                        $dosya = str_replace([":void", ": void"], ["", ""], $dosya);
                        $dosya = preg_replace("/:.*\?\w+/i", "", $dosya); // dönüş tiplerinde ? işareti varsa siler dönüş tipini
                        $lines = explode("\n", $dosya);
                        foreach($lines as $li => $line){
                            if(stripos($line, "function onCommand(CommandSender") !== false or stripos($line, "function execute(CommandSender") !== false){
                                $pa = substr($line, strpos($line, "("), strpos($line, ")"));
                                $words = explode(",", $pa);
                                if(strpos($line, "function onCommand") !== false){
                                    $index = 2;
                                }else{
                                    $index = 1;
                                }
                                $label = $words[$index] ?? "string";
                                if(stripos($label, "string") === false){
                                    $words[$index] = " string " . $label;
                                }

                                $lines[$li] = str_ireplace($pa, implode(",", $words), $line);
                            }elseif(($i = stripos($line, "?")) !== false and strpos($line, "function") !== false){
                                $next = substr($line, $i, $i + 1);
                                if($next !== " "){
                                    $zd = substr($line, strpos($line, "("), strpos($line, ")"));
                                    $words = explode(",", $zd);
                                    foreach($words as $wi => $word){
                                        if(strpos($word, "?") !== false){
                                            $dsh = explode('$', $word);
                                            if(count($dsh) > 1){
                                                $words[$wi] = '$' . $dsh[1];
                                            }
                                        }
                                    }
                                    $lines[$li] = str_ireplace($zd, "(" . implode(",", $words), $line);
                                }
                            }
                        }
                        $dosya = implode("\n", $lines);
                        file_put_contents($object->getRealPath(), $dosya);
                    }
                }

                $this->getLogger()->notice("Converting API for $pluginName finished!\n");
                return true;
            }
        }
        $this->getLogger()->warning("This is not plugin => $file");
        return false;
    }

    public function getPluginDescription($file){
        if(is_dir($file) and file_exists($file . "/plugin.yml")){
            $yaml = @file_get_contents($file . "/plugin.yml");
            if($yaml != ""){
                return new PluginDescription($yaml);
            }
        }

        return null;
    }

    public function createBackup(string $file, string $pluginName){
        if(!is_dir($file)){
            $this->getLogger()->critical("This is not directory => $file");
        }
        $this->getLogger()->info("Started backup for $pluginName...");
        if(file_exists($this->getDataFolder().$pluginName)){
            $this->getLogger()->info("$pluginName plugin backup already exists, overwriting...");
        }

        $path = $this->getDataFolder().$pluginName;
        @mkdir($path);
        $this->copy($file, $path);
        $this->getLogger()->info("Finished creating backup for $pluginName successfully");
    }

    public function copy(string $source, string $def){
        @mkdir($def);
        $sdir = scandir($source);
        foreach ($sdir as $file) {
            if($file == "." or $file == "..") continue;
            $path = $source.DIRECTORY_SEPARATOR.$file;
            if(is_dir($path)){
                $this->copy($path, $def.DIRECTORY_SEPARATOR.$file);
            }else{
                copy($path, $def.DIRECTORY_SEPARATOR.$file);
            }
        }
    }
}