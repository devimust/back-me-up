### Back Me Up

This is a file and folder backup / compression tool written in php.


#### Requirements

php 5.3+
Linux-based OS


#### Install

Copy bin/bmu.phar to a folder in your path e.g. /usr/local/bin
e.g.

1. `sudo cp bin/bmu.phar /usr/local/bin/bmu`
2. `sudo chmod +x /usr/local/bin/bmu`


#### Usage

1. Create a copy of `backup.sample.json` and update
2. `bmu -vs /path/to/backup.json`


#### Making changes

1. Make changes to files in the `src` folder.
2. Run `php generate-phar.php`
3. Run `chmod +x bin/bmu.phar && bin/bmu.phar -h`
