<?php
/**
 * Created by PhpStorm.
 * User: gaoyi
 * Date: 12/26/16
 * Time: 3:58 PM
 */

namespace TcpWorker;


class AutoLoader
{
    protected static $_autoLoadRootPath = '';

    public static function setRootPath($root_Path)
    {
        self::$_autoLoadRootPath = $root_Path;
    }

    public static function loadByNamespace($name)
    {
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        if (strpos($name, 'TcpWorker\\') === 0) {
            $class_file = __DIR__ . substr($class_path, strlen('TcpWorker')).'.php';
        } else {
            if (self::$_autoLoadRootPath) {
                $class_file = self::$_autoLoadRootPath . DIRECTORY_SEPARATOR . $class_path.'.php';
            }
            if (empty($class_file) || !is_file($class_file)) {
                $class_file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $class_path.'.php';
            }
        }

        if (is_file($class_file)) {
            require_once($class_file);
            if (class_exists($name, false)) {
                return true;
            }
        }
        return false;
    }
}

spl_autoload_register('\TcpWorker\AutoLoader::loadByNamespace');