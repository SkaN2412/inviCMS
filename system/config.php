<?php
class Config {
    public static $database = array(
        "server" => "localhost",
        "login" => "root",
        "password" => "q24u12e19rt94i",
        "db" => "invicms"
    );
    public static $site_data = array(
        "name" => "inviCMS",
        "description" => "Light CMS with strong functional!"
    );
    public static $plugins = array(
        "blog" => array(
            "integrated" => false,
            "database" => array(
                "table_prefix" => "blog_"
            )
        )
    );
}
?>