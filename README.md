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

##Data types
Data types hierachy is the core of your Minibus deployment.
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



