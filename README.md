# Jointly Moodle plugin

## About Jointly

Jointly is a Moodle plugin of the type 'local'

This plugin aims to provide specified data in XML with the LOM-syntax to match the 
Open Archives Initiative protocol for Metadata Harvesting.

Every module which allows the user to upload media files will be queried.

## Viewing the results

There are three ways to view the results:

1. Table
2. XML output 'ListIdentifiers'
3. XML output 'getRecord'

The results are available over the view.php.
With the optional URI parameter 'verb=ListIdentifiers' or
'verb=getRecord' the results will be viewed as XML.

On the page view.php are direct links to the XML files.

Optional you can add 'language=xx' after the 'verb' parameter for a specific language when available.

## Settings & Configuration 

In the global settings you have the following options:

* free for all - download available for authorized and unauthorized user
* Admins only - you need the capability 'moodle/site:config' to access the view.php
* allowed license types (e.g. Public, CC, All rights reserved)
* File types


Under 'Edit Metadata' in the view.php you can enter the following metadata 
which will be used in the generated XML files:

* Language ID (e.g. 'de', 'en')
* Persistent Identifier (e.g. DOI)
* Title
* Description
* Keywords
* MetadataPrefix
* Prefix for  Listidentifiers (will be used for every single record)

## Further information

Open Archives Initiative Protocol for Metadata Harvesting
https://www.openarchives.org/pmh/

LOM - Learning object metadata
https://en.wikipedia.org/wiki/Learning_object_metadata




