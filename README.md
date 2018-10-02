# Jointly Moodle plugin

## About Jointly

Jointly is a plugin of the type 'local'

This plugin aims to provide specified data in XML to match the 
Open Archives Initiative protocol for Metadata Harvesting.

Every module which allows the user to upload media files will be queried.

## Viewing the results

There are three ways to view the results:

1. Table with all results
2. XML-output "ListIdentifiers"
3. XML-output "getRecord"

The table is available over the view.php.
If you add the optional parameter "verb=ListIdentifiers" or
"verb=getRecord" to the URI the results will be viewed as XML.

On the page view.php are direct links to the XML files.

Optional you can add "language=xx" after the "verb" parameter for a specific language when available.

## Configuration

Under 'Edit Metadata' you can enter the following metadata which will be used in the generated XML files

* Language ID (eg. 'de', 'en')
* Persistent Identifier (eg. DOI)
* Title
* Description
* Keywords
* MetadataPrefix
* Prefix for  Listidentifiers (will be used for every single record)






