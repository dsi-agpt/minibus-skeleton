Minibus skeleton &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;![Minibus logo](https://raw.githubusercontent.com/dsi-agpt/minibus/master/assets/img/minibus-logo.png)
================

  * [Presentation](#presentation)
    * [Demo](#demo)
    * [Features](#features)
    * [Operating principle](#operating-principle)
  * [Requirements](#requirements)
  * [Technical instructions](#technical-instructions)
    * [Application structure](#application-structure)
    * [Local configuration](#local-configuration)
    * [Data types](#data-types)
    * [Endpoints configuration](#endpoints-configuration)
    * [Zfc-User configuration](#zfc-user-configuration)
    * [Minibus data transfer framework](#minibus-data-transfer-framework)
      * [Data transfer execution engine](#data-transfer-execution-engine)
      * [Creating a new data transfer](#creating-a-new-data-transfer)
      * [Data transfer Api](#data-transfer-api)
    * [Sheduling data transfer](#sheduling-data-transfer)



#Presentation

Minibus skeleton is a sample implementation for Minibus application available on [Github](https://github.com/dsi-agpt/minibus) and [Packagist](https://packagist.org/packages/dsi-agpt/minibus). Minibus is a minimalist data bus written on top of Zend Framework 2 / Doctrine 2.
It provides a framework to write data transfer scripts and a GUI to manage them.
Minibus is intended for people who prefer coding data transfers in object oriented language rather than designing them via a graphic interface as that provided by ETL tools. The main interest of Minibus, for an organization that already uses Zend and Doctrine in its projects, is to train its teams on these technologies instead of dispersing the skills to other tools such as ETL or Bus. Coding data transfers can be a good way to acclimate newcomers to this framework.
Don't use Minibus if your transfers involve very large amounts of data or if you have important performance requirements. Indeed, Zend and the ORM layer will be detrimental with respect to the consumption of RAM and the execution time.

##Demo

A fake deployment is available [here](https://minibus.agroparistech.fr). You can connect as **admin** (password **admintest**) to access all the functionalities or as **guest** (password **guesttest**) for restricted access.
A single type of data ("Foo/Bar") is exposed with two data transfer

##Screenshots

![Screen1](https://raw.githubusercontent.com/dsi-agpt/minibus/master/etc/screens/capt1.png)

![Screen2](https://raw.githubusercontent.com/dsi-agpt/minibus/master/etc/screens/capt2.png)

![Screen3](https://raw.githubusercontent.com/dsi-agpt/minibus/master/etc/screens/capt3.png)

![Screen4](https://raw.githubusercontent.com/dsi-agpt/minibus/master/etc/screens/capt4.png)

##Features

Currently, Minibus provides the following functionnalities :
* Process execution and monitoring from graphical interface
* Process sheduling
* Alerts handling
* Data display with full text search
* Connexion to databases, Rest client, Scp Client

##The big picture

Define your (possibly classified by domains) types of data. Create doctrine entities representative of the common data model for applications of the information system. For each type of data, implement one or more **acquisition process**.
Acquisition process will connect to a remote endpoint (possibly internal to you information system), fetch and convert data to your reference model.  Then, implement one or more **export process** that will convert data and write it into your target applications.

#Requirements

Minibus requires apache 2.x with mod rewrite, mysql, PHP>=5.4, mysql, php5-curl (if you wish to enable rest client), php5-intl, php5-ssh2 (for Scp endpoints), php5-mysql.
For now, dependence to mysql is avoidable, but minibus uses "enum" type in its internal data model. Your RDBMS implementation must support this feature.

#Technical instructions

##Application structure

In the sample deployment proposed here, the main module called Jobs is where you create and configure your data transfer. The data transfer runtime engine is provided by the Minibus module [available on Packagist] (https://packagist.org/packages/dsi-agpt/minibus)

For complex or unusual data manipulations, you can implement your own controllers, services or helpers like in any ZF2 application.
The directory Jobs\Model\ Entity is expected to host the entities forming your specific data model. Two scripts are provided to launch the Doctrine tools, taking into account the local application configuration:

* scripts/doctrine-tools-update.sh for database schema forward generation
* scripts/doctrine-tools-geters-setters.sh for automatic getters/setters generation in entity classes

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
       //...
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
```

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
In the minibus-skeleton configuration sample, Zend User is based on an internal user table. Though, no user management is provider. *scripts/sql/minibus.user.sql* provides 2 basic users for application startup.
By default, nitecon/zfcuser-ldap is installed with Minibus. To enable ldap connexion :

* Enable module 'ZfcUserLdap' in application.config.php
* In config/autoload/zfcuser.global.pgp, disable zend_db_adapter

```php
        // 'zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
```
*  Switch to zfc-user embedded User entity Class
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

##Minibus data transfer framework

Minibus provides a lightweight data transfer framework that aims to simplify the writing of data transfers by supporting the plumbing and optimizing the error return.

###The data transfer execution engine 

Minibus is based on the regular launch of **executors**. An executor is set in motion by a POST request sent to the url */execution*. When launched, the executor:

* Updates active data transfer process
* Kill *zombi* data transfer
* Determines which data transfer are eligible for launch
* Elects one of them and executes it

Each time a data transfer process is launched an **execution** is instantiated and registered in database along with the unix pid of the current PHP thread.
Any execution belongs to a process must be in one of the following states :
* running
* stopped

Each process can be active or not. Only active process may be executed. An active process is marked as running. During the execution of the process, it is the responsibility of the code to set the boolean 'alive' to *true* on a regular basis (at least once per minute). This can be done through a call to the *setAlive* method provided by the data transfer API.
Each time an executor process is launched, it uses the following algorithm :
* if an active process is running, and the boolean *alive* is set to *true*, set it to *false*
* if an active process is running, and the boolean *alive* is set to *false*, treat it as *zombi*, which means killing the corresponding unix thread (known by its pid), and set the boolean *running* to *false*.

Note that a process can be marked as *interrupted* from the outside (typically by pressing the power button * stop * on GUI Minibus). In this case, during the next call to setAlive, an exception is thrown inside the data transfer that will end the process.

If *setAlive* is never called, this will not happen, but the process will be cleaned "naturally" after two minutes at most, for the action of the executors. This algorithm is designed to prevent the accumulation of *zombies* process on the server when something goes wrong.

###Creating a new data transfer

1. Create a new class extending one of the *Minibus\Model\Process\DataTransfer\Acquisition\AbstractDataAcquisitionAgent*, *Minibus\Model\Process\DataTransfer\Acquisition\AbstractDataExportAgent* : for exemple, for the acquisition of students coming from ApplySystem application :

```php
<?php
namespace Jobs\Model\Process\DataTransfer\Acquisition\Students\RegistrationSystem;

use Minibus\Model\Process\DataTransfer\Acquisition\AbstractDataAcquisitionAgent;

class TransferAgent extends AbstractDataAcquisitionAgent
{
```
Then, register your TransferAgent in *module/Jobs/config/data-transfer-agents.php* :
```php
'acquisition-students-registrationsytem' => array(
        'class' => 'Jobs\Model\Process\DataTransfer\Acquisition\Students\RegistrationSystem\TransferAgent'
    ),
```
Please pay attention to naming conventions used by minibus.
If your data transfer is an acquisition process, if the transferred data are students and that the source is registrationsytem, the key must be *acquisition-students-registrationsytem*.
Inside the application a process is always identified by the key registrationsytem mode-datatype-endpoint-year. For non-annualized process, year is set to 0.
You still have to mention that data transfer in one of the *source* (for acquisition) or *target* (for export) entries of your data type configuration file.

```php
'persons' => array(
        'label' => 'Persons',
        'children' => array(
            'students' => array(
                'label' => 'Students',
                'annualize' => false,
                'registrationsytem' => array(
                   //...
                    'sources' => array(
                        'arvus' => array(
                            'label' => 'registrationsytem',
                            'control' => 'defaultProcessControl',
                            'dataTransferAgent' => 'acquisition-students-registrationsytem',
                            'locks' => array(
                                Jobs\Controller\Exclusion\Locks::STUDENTS => LOCK_EX
                            ),
                            'options' => array(
                                'display_button' => array(
                                    'synchronize' => true,
                                    'control' => false,
                                    'stop' => true,
                                    'clear' => false,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
```
It's Minibus that will take care of initializing the connexion to the endpoint and inject it into the data transfer. The source (here *registrationsytems*) must be configured in *module/Jobs/config/data-endpoints.php*.

```php
			'registrationsytems' => array (
					'type' => \Minibus\Controller\Process\Service\Connection\EndpointConnectionBuilder::DATABASE_TYPE,
					'params' => array (
							'driver' => 'pgsql' 
					) 
			),
```

The remaining parameters, whether sensitive or specific to a device, must be moved into autoload/jobs.local.php under the *endpoints* key as already mentioned above.

###Data transfer Api

By extending the abstract TransferAgent class, you have access to several convenience methods constituting the data transfer API.

####Logging 

The dataTransfer is equipped with a logger (Zend\Log\Logger) related to a particular file attached to an execution. The logs are delivered in real time into Minibus web interface.

```php
$this->getLogger()->info("Sample information");
```
Note that as long as a data transfer is running, its logger can be called in a static way : it exempts you from passing a reference to the logger to all secondary classes that you will develop.

```php
Jobs\Model\Process\DataTransfer\Acquisition\Students\RegistrationSystem\TransferAgent::logError(...);
```

####Alerts

The facts that deserve to be brought to the knowledge of the dministrators can be subject to alerts with 3 severity levels.
Then, they can be displayed and filtered under the "Alerts" tab of the web interface.

```php
$this->alertWarn($message);
$this->alertError($message);
$this->alertAlert($message);
```
As with logs, alerts can be called in static mode by other classes.

If an object identifier is passed along with the alert message, object concerned by an alert can be listed and skipped during the next execution of the data transfer. It's accessible as the protected field *$idObjectsAlertList*.

####Keep me alive

Calling the setAlive method on a regular basis is mandatory for the DataTransfer. Typically, this has to be done inside the main loop.
```php
 $this->setAlive(true);
```
Conversely, when its execution is complete, the dataTransfer must report it.
```php
 $this->setAlive(false);
```
To stop execution prematurely, simply place a return statement.
```php
 $this->setAlive(false);
 return;
```
##Sheduling data transfer

It's not enough to click on the checkboxes in the acquisition and export interfaces. An executor is to be launched every minute to update the status of data transfers, determine which ones are mature candidates for execution and launch one of them. 
To enable scheduling, insert this line into your crontab :

```sh
*/1 * * * * root  curl 'https://minibus-deployment-url/execution' -X POST -k >/dev/null 2>&1
```
