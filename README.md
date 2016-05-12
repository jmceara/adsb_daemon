# ADS-B PHP Daemon
PHP-Daemon to collect data from ADS-B sources and procces using plugins

![sample](http://i.imgur.com/Juqb3Do.png)


This daemon need PHP with pthreads library enabled. To run on CentOS, just type:
```zts-php daemon.php```

Copy ```dbSettings.php.sample``` to ```dbSettings.php``` and change database information.

There are 2 plugins for now:
1) Remove old data
2) Get DB info for flights/routes

More to come!
