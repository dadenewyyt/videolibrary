<?php
class autoloader {

    public static $loader;

    public static function init()
    {
        if (self::$loader == NULL)
            self::$loader = new self();

        return self::$loader;
    }

    public function __construct()
    {
        spl_autoload_register(array($this,'model'));
        spl_autoload_register(array($this,'helper'));
        spl_autoload_register(array($this,'controller'));
        spl_autoload_register(array($this,'service'));
    }

    public function library($class)
    {
        set_include_path(get_include_path().PATH_SEPARATOR.'/service/');
        spl_autoload_extensions('.service.php');
        spl_autoload($class);
    }

    public function service($class)
    {
        $class = preg_replace('/service/','',$class);

        set_include_path(get_include_path().PATH_SEPARATOR.'/service/');

        spl_autoload($class);
    }

    public function model($class)
    {
        $class = preg_replace('/_model$/','',$class);

        set_include_path(get_include_path().PATH_SEPARATOR.'/model/');
        spl_autoload_extensions('.model.php');
        spl_autoload($class);
    }

    public function helper($class)
    {
        $class = preg_replace('/_helper$/','',$class);

        set_include_path(get_include_path().PATH_SEPARATOR.'/helper/');
        spl_autoload_extensions('.helper.php');
        spl_autoload($class);
    }

}