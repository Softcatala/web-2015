# Fabfile to:
#    - initialize site based on a new WordPress installation (git, svn, curl need to be installed)
#    - IMPORTANT: before starting, make a copy of the wp directory and database, the script move files and directories and doesn't put them back if something fails
#    - this scripts requires:
#      - set up website with nginx (use easyengine for the creation)
#      - root access to the server (so the permissions can be changed according to the operation)
#      - a database which has been already set up
#      - more information on the internal wiki

# Import Fabric's API module
from __future__ import with_statement
from fabric.api import *
from fabric.contrib.console import confirm
from fabric.colors import green
try:
        from StringIO import StringIO
except ImportError:
        from io import StringIO
import os.path
import re
import logging
logging.basicConfig()

lxc_server = "softcatala.local"
staging_server = "pirineus.softcatala.org:4222"
production_server = "pirineus.softcatala.org:4422"
private_repo_git = "ssh://git@softcatala.org:3332/web-privat"

def lxc(username=''):
    """
    setup for local development environment
    """
    env.hosts = [lxc_server]
    env.id = 'lxc'
    env.user = username
    env.dir = "/var/www/softcatala.local/htdocs/"
    env.wordpressdir = "/var/www/softcatala.local/htdocs/wp"
    env.confdir = "/var/www/softcatala.local/web-2015"
    env.confprivatedir = "/var/www/softcatala.local/web-privat"
    env.backupdir = "/var/www/softcatala.local/backup"
    env.tmp_path = "/tmp/"
    env.require_dev = ""

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
    env.backupdir = "/var/www/web2015.softcatala.org/backup"
    env.confprivatedir = "/var/www/web2015.softcatala.org/web-privat"
    env.tmp_path = "/tmp/"
    env.db_name = "web2015_softcatala_org"
    env.require_dev = "--no-dev"

def prod(username=''):
    """
    setup for prod
    """
    env.hosts = [production_server]
    env.id = 'prod'
    env.user = username
    env.dir = "/var/www/web2016.softcatala.org/htdocs"
    env.wordpressdir = "/var/www/web2016.softcatala.org/htdocs/wp"
    env.confdir = "/var/www/web2016.softcatala.org/web-2015"
    env.backupdir = "/var/www/web2016.softcatala.org/backup"
    env.confprivatedir = "/var/www/web2016.softcatala.org/web-privat"
    env.tmp_path = "/tmp/"
    env.db_name = "web2016_softcatala_org"
    env.require_dev = "--no-dev"

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
    ##Backup DB and files (not for lxc)
    if env.id != 'lxc':
        with cd('%s' % env.backupdir):
            sudo('mkdir -p $(date \'+%Y%b%d\') && cd $_ && mysqldump '+env.db_name+' > '+env.db_name+'-$(date \'+%H%M%S\').sql && mkdir -p files && rsync -av ' + env.dir + '/ files/')

    with cd('%s' % env.dir):
        sudo('mkdir -p ../../.composer && chown www-data:www-data -R ../../.composer')

    ##In local environments, it might be necessary to update the web-privat repository as well (not mandatory if it doesn't exist)
    if env.confprivatedir != '':
        with cd('%s' % env.confprivatedir):
            sudo('chown '+env.user +':' + env.user + ' -R ' + env.confprivatedir)
            run('git pull')
            sudo('chown www-data:www-data -R ' + env.confprivatedir)

    ##Update composer.json
    with cd('%s' % env.confdir):
        sudo('chown www-data:www-data -R ' + env.confdir)
        sudo('git pull', user='www-data')
        if env.confprivatedir != '':
            sudo('chown -h www-data:www-data ../ -R')
            sudo('rm -f ../htdocs/composer.json')
            sudo('source ' + env.confprivatedir + '/licenses/licenses && cat composer.json | sed -e "s/%%license%%/$ACF_LICENSE/g" > ../htdocs/composer.json')
            sudo('chown -h www-data:www-data ../htdocs/composer.json')

    ##Run the environment update and set the permissions back to apache
    with cd('%s' % env.dir):
        sudo('php composer.phar self-update && php composer.phar update ' + env.require_dev, user='www-data')
        sudo('service nginx restart && service redis-server restart && sleep 1 && redis-cli flushall')

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
            run('git clone ' + private_repo_git + ' web-privat')
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
        else:
            #Store database parameters
            config_path = '%s/conf/wordpress/db.php' % base_path
            fd = StringIO()
            get(config_path, fd)
            content = fd.getvalue()

            db_name_ar = re.findall(r'DB_NAME\', \'(\w+)', content)
            db_name = db_name_ar[0]

            db_user_ar = re.findall(r'DB_USER\', \'(\w+)', content)
            db_user = db_user_ar[0]

            db_pass_ar = re.findall(r'DB_PASSWORD\', \'(\w+)', content)
            db_pass = db_pass_ar[0]


        ##Import Database
        with cd('%s' % base_path):
            sc_database_path = 'web-privat/bd/softcatala_base.sql'
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
