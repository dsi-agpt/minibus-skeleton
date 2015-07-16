Minibus skeleton
================
![Minibus logo](https://minibus.agroparistech.fr/img/minibus-logo.png)
#Presentation

Minibus skeleton is a sample implementation for Minibus application available [here](https://github.com/dsi-agpt/minibus). Minibus is a minimalist data bus written on top of Zend Framework 2 / Doctrine 2.
It provides a framework to write data transfer scripts and a GUI to manage them.
Minibus is intended for people who prefer coding data transfers in object oriented language rather than designing them via a graphic interface as that provided by ETL tools.

##Demo

A fake deployment is available [here](https://minibus.agroparistech.fr). You can connect as **admin** (password **admintest**) to access all the functionalities or as **guest** (password **guesttest**) for restricted access.
A single type of data ("Foo/Bar") is exposed with two data transfer

##Features

Currently, Minibus provides the following functionnalities :
* Process execution and monitoring from graphical interface
* Process sheduling
* Alerts handling
* Data display with full text search
* Connexion to databases, Rest client, Scp Client

##Operating principle

Define your (possibly hierarchical) types of data. For each type of data, implement one or more **acquisition process**.
Acquisition process will connect to a remote endpoint (possibly internal to you information system), fetch and convert data to your reference model.  Then, implement one or more **export process** that will convert data and write it into your target applications.

#Requirements

Minibus requires apache 2.x with mod rewrite, mysql, PHP>=5.4, mysql, php5-curl (if you wish to enable rest client), php5-intl, php5-ssh2 (for Scp endpoints), php5-mysql.
For now, dependence to mysql is avoidable, but minibus uses "enum" type in its internal data model. Your RDBMS implementation must support this feature.

#Technical instructions

##Local configuration
After retrieving all dependencies via composer, copy minibus local configuration file vendor/dsi-agpt/minibus/config/minibus.local.php.dist to your autoload directory and remove .dist extension.

This file must be carefully fulfilled. Edit parts marked by %% symbols. It allows Doctrine Tools to generate database schema for 2 entity directories : that of Minibus itself and that of you Data transfer process.
Pay attention to *data-store-directory* and *process-log-directory* : you will have to allow write access for apache user or group (www-data).

```php
$dbParams = array(
    'host' => '%MINIBUS_DB_HOST%',
    'port' => '%MINIBUS_DB_PORT%',
    'user' => '%MINIBUS_DB_USER%',
    'password' => '%MINIBUS_DB_PASSWORD%',
    'dbname' => '%MINIBUS_DB_DBNAME%',
    'driver' => 'pdo_mysql',
    'mapping_types' => "enum: string",
    'charset' => 'utf8',
    'driverOptions' => array(
        1002 => 'SET NAMES utf8'
    )
);
return array(
    'doctrine' => array(
       /...
        'driver' => array(
            // defines an annotation driver with two paths
            'minibus_annotation_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../vendor/dsi-agpt/minibus/src/Minibus/Model/Entity',
                    __DIR__ . '/../../../module/Jobs/src/Jobs/Model/Entity'
                )
            ),
            
            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    'Minibus' => 'minibus_annotation_driver',
                    'Jobs' => 'minibus_annotation_driver'
                )
            )
        )
    ),
    //just in case you should use auto-generated ssl certficates with REST client
    'enable_rest_client_ssl_verification' => false,
    'data-store-directory' => '%DATA_DIRECTORY%' ,
    'process-log-directory' => '%LOGS_DIRECTORY%' ,
    'number-of-executions-to-keep' => 5,
    //choose among google cdn host themes : for example, 'smoothness' (http://blog.jqueryui.com for complete list)
    'jquery-ui-theme' => '%JQUERY_UI_THEME%',
    //acl configuration file
    'auth' => array(
        'filePath' => __DIR__ . "/users.txt"
    ),
    //...
);
```

##Data types
Data types hierachy (module/Jobs/config/data-types.php) is the core of your Minibus deployment. 
```php
return array(
    //first level of data types, called, "Foo"
    'foo' => array(
        'label' => 'Foo',
        'children' => array(
            //first level of data types, called, "Bar"
            'bar' => array(
                'label' => 'Bar',
                //wether or not to sort data by year
                'annualize' => false,
                //browse configuration
                'configuration' => array(
                    'browse' => array(
                        'general' => array(
                            'control' => 'defaultBrowseControl',
                            //implementation of data conversion to feed jquery datatable
                            'datatable-formatter' => 'Jobs\Model\Browse\Formatters\Foo\Bar\DataTableFormatter',
                            //url of jquery datatable ajax calls
                            'url' => 'rest/data/bar',
                            //columns to display
                            'columns' => array(
                                'title' => 'Title',
                                'record_label' => 'Record label',
                                'release_date' => 'Release date',
                                'primary_artist' => 'Primary artist'
                            )
                        )
                    ),
                    //one or more sources of data
                    'sources' => array(
                        //Your first data source for Foo/Bar. Must be configured in config/data-endpoints.php
                        'dummy' => array(
                            'label' => 'Dummy Endpoint',
                            'process-description' => 'This process retrieves products containing word "piano" on ebay API and rejects those non belonging to Music/CD category',
                            //javascript control used to monitor the process
                            'control' => 'defaultProcessControl',
                            //implementation of data transfer. Real class to specify in config/data-transfer-agent.php
                            'dataTransferAgent' => 'acquisition-bar-dummy',
                            //name of the mutex lock and mode of acquisition
                            'locks' => array(
                                Jobs\Controller\Exclusion\Locks::PRODUCTS => LOCK_EX
                            ),
                            //options javascript control used to monitor the process
                            'options' => array(
                                'display_button' => array(
                                    'synchronize' => true,
                                    'control' => false,
                                    'stop' => true,
                                    'clear' => true
                                )
                            )
                        )
                    ),
                    //one or more targeted application
                    'targets' => array(
                        //the same for the targeted applications
                    )
                )
            )
        )
    )
);
```

##Endpoints configuration

For each endpoint listed in *data-types.php*, provide a configuration in *data-endpoints.php*.


```php
return array(
    'dummy' => array(
        'type' => \Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder::WEB_SERVICE_TYPE,
        'params' => array()
    ),
    'fake' => array(
        'type' => \Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder::DATABASE_TYPE,
        'params' => array(
            'driver' => 'mysql'
        )
    )
);
```
Sensible or device-specific configuration information should be moved in autoload/jobs.local.php under the *endpoints* key.

```php
return array(
    'data_endpoints' => array(
        'dummy' => array(
            'params' => array(
                'url' => 'https://sensible information'
            )
            
        ),
        'fake' => array(
            'params' => array(
                'host' => 'myhost',
                'port' => '1234',
                'user' => 'myuser',
                'password' => 'mypasswd',
                'dbname' => 'mydb'
            )
        ),
    )
);

##Access control lists

Considering the small number of users typically authorized in such applications, Minibus offers a rudimentary ACL system, based on a file and two roles. Provide a file designed as follows and specify its path in minibus local configuration file:

```
alice=admin
bob=guest
```

In case you would add new routes to the application via your Jobs module, allow  admin and/or guest role to use them in /minibus-skeleton/module/Jobs/config/acl-roles-config.php

```php
return array (
		'guest' => array ("my-new-route"),
		'admin' => array ("my-new-route","another-new-route") 
);
```
##Zfc-User configuration
Minibus depends on zfc-user module for authentication features.
In the minibus-skeleton configuration sample, Zend User is based on an internal user table. Though, no user management is provider. *scripts/sql/minibus.user.sql* provides tw basic users for application startup.
By default, nitecon/zfcuser-ldap is installed with Minibus. To enable ldap connexion :

* Enable 'ZfcUserLdap' in application.config.php
* In config/autoload/zfcuser.global.pgp, disable zend_db_adapter

```php
        // 'zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
```
*  and change user_entity_class
```php
		'user_entity_class' => 'ZfcUserLdap\Entity\User',
```

* Provide autoload/ldap.local.php
```php
return array (
		'ldap' => array (
				'server' => array (
						'host' => 'ldap://my.ldap.server',
						'port' => 0,
						'useSsl' => false,
						'username' => null,
						'password' => null,
						'bindRequiresDn' => true,
						'baseDn' => 'ou=People,dc=xxx, dc=xx',
						'accountCanonicalForm' => 2,
						'accountDomainName' => 'xxxxxxxxxxxx.fr',
						'accountDomainNameShort' => 'xxxxxxxxxx-fr',
						'accountFilterFormat' => null,
						'allowEmptyPassword' => false,
						'useStartTls' => false,
						'optReferrals' => false,
						'tryUsernameSplit' => true 
				) 
		) 
);
```
