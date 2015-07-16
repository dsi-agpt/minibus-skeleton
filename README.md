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
* Alerts handling
* Data display

##Operating principle

Define your (possibly hierarchical) types of data. For each type of data, implement one or mor **acquisition process**.
Acquisition process will connect to remote endpoint (possibly internal to you information system), fetch and convert data to your reference model. 




