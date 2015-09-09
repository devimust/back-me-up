### BMU

#### Install

Copy bin/bmu.phar to a folder in your path e.g. /usr/local/bin
e.g.
1. `sudo cp bin/bmu.phar /usr/local/bin/bmu`
2. `sudo chmod +x /usr/local/bin/bmu`


#### Usage

1. Create a copy of `backup.sample.json`
2. `bmu -vs /path/to/backup.json`


#### Making code changes

1. Make changes to files in the `src` folder.
2. Run `php generate-phar.php`
3. Run `bin/bmu.phar`
