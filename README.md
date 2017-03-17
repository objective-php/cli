# Objective PHP / Command Line Interface [![Build Status](https://secure.travis-ci.org/objective-php/cli.png?branch=master)](http://travis-ci.org/objective-php/services-factory)

## Description

CLI components allow to create command that are routable on CLI only.  

The main main focus of this component is put on:

 - implements maintenance scripts within the application
 - reuse most of what is done for the main application in a CLI context

The CLI package provides Objective PHP applications with several components: 

 - CliRequest
    - this one will be used by the RequestWrapper from `objective-php/application` to expose CLI arguments to the routed action
 - CliRouter
    - this component works together with the `MetaRouter` class from the `objective-php/router` package
    - it is needed to register then route the command line actions
    - the `CliRouter` is also responsible for triggering actions's parameter hydration
 - CliParameterContainer
    - this is where the CLI parameters are stored
 - AbstractCliAction
    - base class to extend for writing CLI actions


## What's next

Next release with provide the developer with some more base action classes, especially targeted at creating workers. 
 

## Installation

### Manual

You can clone our Github repository by running:

```
git clone http://github.com/objective-php/
```

This is the way you should get the code only if you intend to work on it.

### Composer

Most typical use case is to require `objective-php/cli` from an existing Objective PHP application:

```
composer require objective-php/cli
```


## How to test the work in progress?

### Run unit tests

First of all, before playing around with our services factory, please always run the unit tests suite. Our tests are written using PHPUnit, and can be run as follow:

```
cd [clone directory]
vendor/bin/phpunit tests
```

### Write your first CLI command

To be continued...


