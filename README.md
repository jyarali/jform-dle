# jform-dle
Drag &amp; Drop Form Generator for Datalife Engine

# How to install
1. copy contents of uploads folder to your hosting root folder.
2. rename theme_name folder inside templates as your current dle theme.
3. go to DLE admin panel and navigate to plugins section.
4. import jform-form-generator.xml into your plugins.
5. open `.htaccess` in your website root

    find 
    ```
    RewriteEngine On
    ```
    
    and add following code after it
    ```
    RewriteRule ^jform/([0-9]+)-(.*).html$ index.php?do=jform&formid=$1 [L]
    ```
6. enjoy using the plugin!

