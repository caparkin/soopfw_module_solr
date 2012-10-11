# Soopfw module: Solr

Solr is a [SoopFw](http://soopfw.org) extension .

The module will provide solr search functionality for the [SoopFw framework](http://soopfw.org).

# How to use
First of all download this module and install the source files under **{docroot}/modules/solr**.
After that you need to enable the module.

Login with the admin account or use the **clifs** command to enable/install the module.

Now you need to be really logged in with the admin account.
Go to the solr menu entry and create a server.

If the server is successfully created you can use the SolrFactor to retrieve the Apache_Solr_Service object
or use the provided helper classes to send search requests to the solr server.