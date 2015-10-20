# Fabfile to:
#    - initialize site based on a new WordPress installation (git, svn, curl need to be installed)

# Import Fabric's API module
from fabric.api import local
import os.path

def initialize_site(base_path='',base_url=''):
    if base_path and base_url:
        #wp_config_url = 'https://raw.githubusercontent.com/Softcatala/web-2015/master/wp-config.php';
        config_path = base_path + '/wp-config.php'
        web2015_path = base_path + '/../../web-2015'
        if os.path.isfile(config_path):
            print('Found wp-config.php file. Proceeding...')

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

            #Define website url
            local("cd %s/wp && echo \"update_option('siteurl','%s/wp');\" >> wp-config.php" % (base_path, base_url))
            local("cd %s/wp && echo \"update_option('home','%s');\" >> wp-config.php" % (base_path, base_url))
        else:
            print("The path you have provided doesn't contain a wp-config.php file. Is it correct?")
    else:
        print("Please provide an absolute path to the website root directory and url")
