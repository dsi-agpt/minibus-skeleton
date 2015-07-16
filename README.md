Minibus skeleton
================

#Presentation

Minibus skeleton is a sample implementation for Minibus application available [here](https://github.com/dsi-agpt/minibus). Minibus is a minimalist data bus written on top of Zend Framework 2 / Doctrine 2.
It provides a framework to write data transfer scripts and a GUI to manage them.
Minibus is intended for people who prefer coding data transfers in obect oriented language rather than designing them via a graphic interface as that provided by ETL.

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

Minibus requires apache 2.x with mod rewrite, mysql, PHP>=5.4, mysql, php5-curl, php5-intl, php5-mysql.
For now, dependence to mysql is avoidable, but minibus uses "enum" type in its internal data model. Your RDBMS implementation must support this typing.

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
        'connection' => array(
            'orm_default' => array(
                'params' => $dbParams
            )
        ),
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
    'enable_rest_client_ssl_verification' => false,
   	'data-store-directory' => '%DATA_DIRECTORY%' ,
	'process-log-directory' => '%LOGS_DIRECTORY%' ,
    'number-of-executions-to-keep' => 5,
    'jquery-ui-theme' => '%JQUERY_UI_THEME%',
    'auth' => array(
        'filePath' => __DIR__ . "/users.txt"
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => function ($sm) use($dbParams)
            {
                return new Zend\Db\Adapter\Adapter(array(
                    'driver' => 'pdo',
                    'dsn' => 'mysql:host=' . $dbParams['host'] . ';port=' . $dbParams['port'] . ';dbname=' . $dbParams['dbname'] . ';user=' . $dbParams['user'] . ';password=' . $dbParams['password'],
                    'database' => $dbParams['dbname'],
                    'username' => $dbParams['user'],
                    'password' => $dbParams['password'],
                    'hostname' => $dbParams['host']
                ));
            }
        )
    )
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
                        //first source. Must be configured in config/data-endpoints.php
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
Sensible or device-specific configuration information should be placed in autoload/jobs.local.php

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

