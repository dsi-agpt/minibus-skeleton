Minibus skeleton
================

#Presentation

Minibus skeleton is a sample implementation for Minibus application available [here](https://github.com/dsi-agpt/minibus). Minibus is a minimalist data bus written on top of Zend Framework 2 / Doctrine 2.
It provides a framework to write data transfer scripts and a GUI to manage them.

##Demo

A fake deployment is available [here](https://minibus.agroparistech.fr). You can connect as **admin** (password **admintest**) to access all the functionalities or as **guest** (password **guesttest**) for restricted access.
A single type of data ("Foo/Bar") is exposed with two data transfer

##Features

Currently, Minibus provides the following functionnalities :
* Process execution and monitoring from graphical interface
* Process sheduling
* Alerts handling
* Data display with full text seach

##Operating principle

Define your (possibly hierarchical) types of data. For each type of data, implement one or more **acquisition process**.
Acquisition process will connect to a remote endpoint (possibly internal to you information system), fetch and convert data to your reference model.  Then, implement one or more **export process** that will convert data and write it into your target applications.

#Requirements

Minibus requires apache 2.x with mod rewrite, mysql, PHP>=5.4, mysql, php5-curl, php5-intl, php5-mysql.
For now, dependence to mysql is avoidable, but minibus uses "enum" type in its internal data model. Your RDBMS implementation must support this fonctionnality.

#Technical instructions





