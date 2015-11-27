# Fabfile to:
#    - initialize site based on a new WordPress installation (git, svn, curl need to be installed)
#    - IMPORTANT: before starting, make a copy of the wp directory and database, the script move files and directories and doesn't put them back if something fails
#    - this scripts requires:
#      - a working wordpress installation
#      - possibility to write on the website root path and the directory above
#      - need to provide: base_path where the wordpress installation is, base_url of the site and a path to the sql file
#    - You will need to add the parameter "ssi on;" to your nginx conf file for your site

# Import Fabric's API module
from __future__ import with_statement
from fabric.api import *
from fabric.contrib.console import confirm
import os.path
import re

lxc_server = "softcatala.local"
staging_server = "pirineus.softcatala.org:4222"
production_server = "pirineus.softcatala.org:4222"

def lxc():
    """
    setup for local development environment
    """
    env.hosts = [lxc_server]
    env.user = "ubuntu"
    env.dir = "/var/www/softcatala.local/htdocs/"
    env.wordpressdir = "/var/www/softcatala.local/htdocs/wp"
    env.confdir = "/var/www/softcatala.local/web-2015"
    env.branch = "master"
    env.tmp_path = "/tmp/"

def staging():
    """
    setup for staging
    """
    env.hosts = [staging_server]
    env.user = "piranzo"
    env.dir = "/var/www/web2015.softcatala.org/htdocs"
    env.wordpressdir = "/var/www/web2015.softcatala.org/htdocs/wp"
    env.confdir = "/var/www/web2015.softcatala.org/web-2015"
    env.branch = "master"
    env.tmp_path = "/tmp/"

def update_environment():
    """
    makes a backup server-side
    """
    with cd('%s' % env.confdir):
        run('git pull')
    with cd('%s' % env.dir):
        run('php composer.phar update')

def deploy():
    """
    usage: fab [lxc|staging|production] deploy
    clones the repo in a separate folder, packs it in a tar.gz, makes a remote backup, and enables it
    """
    


def initialize_site(base_path='',base_url='',db_name='',db_user='',db_pass=''):
    if base_path and base_url and db_name and db_user and db_pass:
        print('Starting SC website initizalization...')

        with cd('%s' % base_path):
            #Create directory structure
            run('mkdir -p conf/wordpress')
            run('git clone https://github.com/Softcatala/web-2015.git')
            run('git clone ssh://git@softcatala.org:3332/web-privat web-privat')
            run('cp web-privat/conf/wordpress/wp-config.php conf/wordpress/')
            
        with cd('%s/htdocs' % base_path):
            #Download WordPress and plugins/theme
            run('curl -sS https://getcomposer.org/installer | php')
            run('ln -s ../web-2015/composer.json')
            run('./composer.phar install')
            run('ln -s ../web-2015/ssi')
            run('ln -s ../web-2015/index.php')
            run('mkdir uploads')

        with cd('%s/htdocs/wp' % base_path):
            run('ln -s ../../web-2015/wp-config.php')

        with cd('%s/conf/wordpress' % base_path):
            run('sed -i -- \'s/db_name/%s/g\' wp-config.php' % db_name)
            run('sed -i -- \'s/db_user/%s/g\' wp-config.php' % db_user)
            run('sed -i -- \'s/db_pass/%s/g\' wp-config.php' % db_pass)

        ##Import Database
        with cd('%s' % base_path):
        sc_database_path = 'web-privat/bd/softcatala_local_sample.sql'
        run("mysql -u "+db_user+" -p"+db_pass+" --silent --skip-column-names -e \"SHOW TABLES\" "+db_name+" | xargs -L1 -I% echo 'SET FOREIGN_KEY_CHECKS = 0; DROP TABLE %;' | mysql -u "+db_user+" -p"+db_pass+" -v "+db_name)
        run("mysql -u "+db_user+" -p"+db_pass+" "+db_name+" < "+sc_database_path)
        run("mysql -u "+db_user+" -p"+db_pass+" "+db_name+" -e 'SET FOREIGN_KEY_CHECKS = 1; UPDATE wp_options SET option_value=\"%s/wp\" where option_name=\"siteurl\"'" % base_url)
        run("mysql -u "+db_user+" -p"+db_pass+" "+db_name+" -e 'UPDATE wp_options SET option_value=\"%s\" where option_name=\"home\"'" % base_url)

    else:
        print("Please provide an absolute path to the website root directory and url")