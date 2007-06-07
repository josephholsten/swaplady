Welcome to swaplady!
    Before you try to deploy, improve, or maintain this project, please note a few things. I use a very unusual coding standard, which I hope you will appreciate. I believe in literate programming, test driven design, relentless refactoring, and heavy handed logging. That means you have four different ways of finding answers to your problems.
    First, there is the API documentation. They are available in the doc/ directory. You can (re)build them using the standard phpDocumentor tool.
    TODO: flesh out API
    TODO: create a script to automate phpDocumentor
    Second, there are tests. These are available in the test/ directory. You can run them using the nearly standard phpUnit tool.
    TODO: get tests working
    TODO: create a script to automate phpUnit
    Third, there is the code itself. This is primarily in application/ along with config/ containing configuration files and library/ containing supplementary packages. I try as best as I can to be descriptive and clear with every line of code. As such, I rarely include comments beyond API descriptions.
    Forth, there are the logs. These are available in the log/ directory. By default, they are terse and infrequent for production use. To change this, set the desired log level in config/environment.php . This should allow to get most all the info you need about the status of the application. And while I rarely include comments about the workings of the code, my debug level logs may suit your needs for such.
    
        Installation and Deployment
    Swaplady is designed to deploy into an Apache + PHP5 + MySQL5 server stack. For Apache, mod_rewrite is required with mod_alias and either mod_php, mod_fcgi, or mod_fastcgi strongly recomended. You'll need to point apache to. the public/ directory and either modify your httpd.conf based on the public/.htaccess file, or use and AllowOverride All directive. If you use an alias to route to swaplady, you will need to modify the public/.htaccess file.
    Swaplady depends on PHP 5.1 to run. You will also need PDO_MYSQL built in or installed as an extention. Normally a recent install of php will provide that in a reasonable manner.
    You'll need to create a database in MySQL. The default is swaplady_development, but this may be changed in the config/db.php file. You'll need to create the tables and indexes located in the db/ directory.
    Sessions will are stored in the tmp/sessions/ directory. Because of this, the directory should be writable by PHP.