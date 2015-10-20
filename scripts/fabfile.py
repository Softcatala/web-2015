# Fabfile to:
#    - initialize site based on a new WordPress installation (git, svn, curl need to be installed)
#    - IMPORTANT: before starting, make a copy of the wp directory and database, the script move files and directories and doesn't put them back if something fails
#    - this scripts requires:
#      - a working wordpress installation
#      - possibility to write on the website root path and the directory above
#      - need to provide: base_path where the wordpress installation is, base_url of the site and a path to the sql file
#    - You will need to add the parameter «ssi on;» to your nginx conf file for your site

# Import Fabric's API module
from fabric.api import local
import os.path
import re

def initialize_site(base_path='',base_url='',sc_database_path=''):
    if base_path and base_url and sc_database_path:
        #wp_config_url = 'https://raw.githubusercontent.com/Softcatala/web-2015/master/wp-config.php';
        config_path = base_path + '/wp-config.php'
        web2015_path = base_path + '/../../web-2015'
        if os.path.isfile(config_path):
            print('Found wp-config.php file. Proceeding...')

            #Store database parameters
            f = open(config_path, 'r')
            db_name_ar = re.findall(r'DB_NAME\', \'(\w+)', f.read())
            db_name = db_name_ar[0]
            f.close()

            f = open(config_path, 'r')
            db_user_ar = re.findall(r'DB_USER\', \'(\w+)', f.read())
            db_user = db_user_ar[0]
            f.close()

            f = open(config_path, 'r')
            db_pass_ar = re.findall(r'DB_PASSWORD\', \'(\w+)', f.read())
            if db_pass_ar:
                db_pass = "-p"+db_pass_ar[0]
            else:
                db_pass = ''
            f.close()

            #Create directory structure
            local('mkdir %s/.wp' % base_path)
            local('cd %s && cd .. && mkdir -p conf/wordpress' % base_path)
            local('cd %s && mv * .wp/' % base_path)
            local('cd %s && mv .wp wp' % base_path)
            local('mkdir %s/plugins' % base_path)
            local('mkdir %s/themes' % base_path)

            #Donwload basic conf files from web-2015 repo, they will be placed in a directory under ../../base_path
            if not os.path.isfile(web2015_path):
                local('cd %s && cd ../.. && git clone https://github.com/Softcatala/web-2015.git' % base_path)

            #Set up the config file location and index.php
            local('cd %s/wp && head -n -3 wp-config.php > wp-config-temp.php && mv wp-config-temp.php wp-config.php' % base_path)
            local("cd %s/wp && echo \"\n/** Plugin, Uploads and Theme directories **/\ndefine( 'WP_PLUGIN_DIR', ABSPATH . '../../htdocs/plugins' );\ndefine( 'WP_PLUGIN_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/plugins' );\ndefine( 'UPLOADS', '../uploads' );\ndefine( 'PLUGINDIR', ABSPATH . '../../htdocs/plugins' );\n\n/** Sets up WordPress vars and included files. */\nrequire_once(ABSPATH . 'wp-settings.php');  \" >> wp-config.php" % base_path)
            local('cd %s/wp && mv wp-config.php ../../conf/wordpress' % base_path)
            local('cd %s/wp && ln -s ../../../web-2015/wp-config.php wp-config.php' % base_path)
            local('cd %s && cp ../../web-2015/index.php index.php' % base_path)

            #Download theme
            local('cd %s/themes && git clone https://github.com/Softcatala/wp-softcatala.git' % base_path)

            #Donwload composer and install plugins
            local('cd %s && curl -sS https://getcomposer.org/installer | php' % base_path)
            local('cd %s && ln -s ../../web-2015/composer.json' % base_path)
            local('cd %s && ./composer.phar install' % base_path)

            #Create ssi symbolic link
            local('cd %s && ln -s ../../web-2015/ssi ssi' % base_path)

            #Import Database
            local("mysql -u "+db_user+" "+db_pass+" --silent --skip-column-names -e \"SHOW TABLES\" "+db_name+" | xargs -L1 -I% echo 'SET FOREIGN_KEY_CHECKS = 0; DROP TABLE %;' | mysql -u "+db_user+" "+db_pass+" -v "+db_name)
            local("mysql -u "+db_user+" "+db_pass+" "+db_name+" < "+sc_database_path)
            local("mysql -u "+db_user+" "+db_pass+" "+db_name+" -e 'SET FOREIGN_KEY_CHECKS = 1; UPDATE wp_options SET option_value=\"%s/wp\" where option_name=\"siteurl\"'" % base_url)
            local("mysql -u "+db_user+" "+db_pass+" "+db_name+" -e 'UPDATE wp_options SET option_value=\"%s\" where option_name=\"home\"'" % base_url)

        else:
            print("The path you have provided doesn't contain a wp-config.php file. Is it correct?")
    else:
        print("Please provide an absolute path to the website root directory and url")