<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Assets paths
    |--------------------------------------------------------------------------
    |
    | Location of all application assets, relative to the public folder,
    | may be used together with absolute paths or with URLs.
    |
    */

    'images' => '/storage/images',
    'css' => '/assets/css',
    'img' => '/assets/img',
    'js' => '/assets/js'
);

class Asset
{
    private static function getUrl($type, $file)
    {
        return URL::to(Config::get('assets.' . $type) . '/' . $file);
    }

    public static function css($file)
    {
        return self::getUrl('css', $file);
    }

    public static function img($file)
    {
        return self::getUrl('img', $file);
    }

    public static function js($file)
    {
        return self::getUrl('js', $file);
    }

}

?>