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
from fabric.colors import green
import os.path
import re

lxc_server = "fabric.local"
staging_server = "pirineus.softcatala.org:4222"
production_server = "pirineus.softcatala.org:4422"

def lxc(username=''):
    """
    setup for local development environment
    """
    env.hosts = [lxc_server]
    env.user = username
    env.dir = "/var/www/fabric.local/htdocs/"
    env.wordpressdir = "/var/www/fabric.local/htdocs/wp"
    env.confdir = "/var/www/fabric.local/web-2015"
    env.confprivatedir = "/var/www/fabric.local/web-privat"
    env.tmp_path = "/tmp/"

def staging(username=''):
    """
    setup for staging
    """
    env.hosts = [staging_server]
    env.id = 'staging'
    env.user = username
    env.dir = "/var/www/web2015.softcatala.org/htdocs"
    env.wordpressdir = "/var/www/web2015.softcatala.org/htdocs/wp"
    env.confdir = "/var/www/web2015.softcatala.org/web-2015"
    env.confprivatedir = ""
    env.tmp_path = "/tmp/"

def prod(username=''):
    """
    setup for prod
    """
    env.hosts = [staging_server]
    env.id = 'prod'
    env.user = username
    env.dir = "/var/www/web2015.softcatala.org/htdocs"
    env.wordpressdir = "/var/www/web2016.softcatala.org/htdocs/wp"
    env.confdir = "/var/www/web2016.softcatala.org/web-2015"
    env.confprivatedir = ""
    env.tmp_path = "/tmp/"

def check_connection():
    """
    use this function to verify that the connection with the environment is successful
    example: fab lxc:ubuntu check_connection
    """
    run('echo "Congrats! You are now in the remote server!"')

def update_environment():
    """
    updates the application on server side using composer
    """
    ##Set the directory permissions to the active user
    with cd('%s' % env.dir):
        run('sudo chown $USER:$USER -R .')

    ##Update composer.json
    with cd('%s' % env.confdir):
        run('sudo git pull')

    ##In local environments, it might be necessary to update the web-privat repository as well (not mandatory if it doesn't exist)
    if env.confprivatedir != '':
        with cd('%s' % env.confprivatedir):
            run('git pull')

    ##Run the environment update and set the permissions back to apache
    with cd('%s' % env.dir):
        run(' ')
        run('sudo chown www-data:www-data -R .')

def deploy():
    """
    usage: fab [lxc|staging|production] deploy
    clones the repo in a separate folder, packs it in a tar.gz, makes a remote backup, and enables it
    """
    


def initialize_site(base_path='',base_url='',db_name='',db_user='',db_pass=''):
    if base_path and base_url:
        print('Starting SC website initizalization...')

        ##Change directory permissions to current user and create directory structure
        with cd('%s' % base_path):
            run('sudo chown $USER:$USER -R .')
            run('mkdir -p conf/wordpress')
            run('git clone https://github.com/Softcatala/web-2015.git')
            run('git clone ssh://git@softcatala.org:3332/web-privat web-privat')
            if not db_user:
                run('cp web-privat/conf/wordpress/db_%s.php conf/wordpress/db.php' % env.id)
            else:
                run('cp web-2015/conf/wordpress/db.php conf/wordpress/db.php')

        ##Download WordPress and plugins/theme
        with cd('%s/htdocs' % base_path):
            run('curl -sS https://getcomposer.org/installer | php')
            run('ln -s ../web-2015/composer.json')
            run('./composer.phar install')
            run('ln -s ../web-2015/ssi')
            run('cp ../web-2015/index.php .')
            run('mkdir uploads')

        with cd('%s/htdocs/wp' % base_path):
            run('ln -s ../../web-2015/conf/wordpress/wp-config.php')

        ##Set the user/pass/db for wordpress with the provided parameters
        if db_name and db_user and db_pass:
            with cd('%s/conf/wordpress' % base_path):
                run('sed -i -- \'s/db_name/%s/g\' db.php' % db_name)
                run('sed -i -- \'s/db_user/%s/g\' db.php' % db_user)
                run('sed -i -- \'s/db_pass/%s/g\' db.php' % db_pass)

        ##Import Database
        with cd('%s' % base_path):
            sc_database_path = 'web-privat/bd/softcatala_local.sql'
            run("mysql -u "+db_user+" -p"+db_pass+" --silent --skip-column-names -e \"SHOW TABLES\" "+db_name+" | xargs -L1 -I% echo 'SET FOREIGN_KEY_CHECKS = 0; DROP TABLE %;' | mysql -u "+db_user+" -p"+db_pass+" -v "+db_name)
            run("mysql -u "+db_user+" -p"+db_pass+" "+db_name+" < "+sc_database_path)
            run("mysql -u "+db_user+" -p"+db_pass+" "+db_name+" -e 'SET FOREIGN_KEY_CHECKS = 1; UPDATE wp_options SET option_value=\"%s/wp\" where option_name=\"siteurl\"'" % base_url)
            run("mysql -u "+db_user+" -p"+db_pass+" "+db_name+" -e 'UPDATE wp_options SET option_value=\"%s\" where option_name=\"home\"'" % base_url)

        ##Remove unused themes and update database if necessary. To avoid initial with Softcatala theme, we deactivate and reactivate it
        with cd('%s/htdocs/wp' % base_path):
            run('wp core update-db && wp theme delete twentyten && wp theme delete twentyeleven && wp theme delete twentytwelve && wp theme delete twentythirteen && wp theme delete twentyfourteen')
            run('wp theme activate twentyfifteen && wp theme activate wp-softcatala')

        ##Restore apache permissions
        with cd('%s' % base_path):
            run('sudo chown www-data:www-data -R .')

    else:
        print("Please provide an absolute path to the website root directory and url")