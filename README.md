# Enterprise Europe Network (Alpha Version)

What is EEN?
------------

EEN is a website available [here][1] and this is the source code of the projects

Installation
------------

In order to install the project locally and some awesome development you will need to clone all the necessary projects onto your machine

- Vagrant Box: ``git clone https://devops.innovateuk.org/code-repository/scm/een/een-vagrant.git``
- Service Layer: ``git clone https://devops.innovateuk.org/code-repository/scm/een/een-service.git``
- Drupal: ``git clone https://devops.innovateuk.org/code-repository/scm/een/een-webapp.git een``
- Integration: ``git clone https://devops.innovateuk.org/code-repository/scm/een/een-integration-tests.git``

Documentation
-------------

The documentation of each project is available in each project repository


The [Vagrant project][2] will build a virtual machine on the developer computer ready to use and host the website


The [Service Layer project][3] is a bridge between the drupal application and the external tools (Salesforce/Merlin/etc.)

The [Integration project][4] is a test suite to test that the website pages are displayed correctly

Drupal (See below)


Drupal Documentation
--------------------

The Drupal project use [Drupal][9] version 8.2

* Custom Theme
An EEN Theme is available [here][10].
All the javascript, sass and templates are in the code source
In order to compile the js and sass files we used a gulp manager.
To run all the required steps you can simply execute ``gulp`` in the root folder of this project.
This will compile the file into a dist folder to make it available to the website

* Custom Modules
The following module have been created to handle the project requirements:
    - events: This module is responsible for retrieving and displaying the een events
    - opportunities: This module is responsible for retrieving and displaying the een opportunities
    - service_communication: This module management insures communication with external api and service layer
    - een_common: This module handles the shared component used by the different modules

* Modules
This is the list of modules installed on the drupal project:
    - site_banner
    - twig_extensions

* Database
The full dump of the database is loaded on the deployment if any changes has been made to it.
It is planned to be used a migration tool in the future when the website will be live and modification to the schema or data wants to be made.

Deployment information
----------------------

In order to deploy the project to an environment, we are using a [jenkins][6] instance.
A [jenkins file][7] is use to define the steps of deployment below:
- Code: update the code with the latest changes
- Npm: sync all the npm modules
- Gulp: run all the gulp tasks (compile css/js/image/etc.)
- Composer: update the project dependencies
- Unit Test: run the unit test suite
- Package: compile the files
- Remote Deploy: Deploy the project to selected environment - here integration_v3
- Integration Test: Run the integration test suite

If one of the steps above fail for any reason, the deployment will stop.

All the deployment script are present inside this project under the [build][8] folder


Command Line
------------

To help the user to use the project locally a Makefile has been created to run command fast.
Here are most important:
- make install: install/re-install completely the project
- make cc: clear the cache
- make test: run the unit test
- make install-module: Install the custom modules
- make delete-module: Delete the custom modules
- make export-sql: Export all the database to a file
- make update-entity: Update the database in case of wrong schema

Git Information
---------------

At the moment we are using git flow to version the work we have done.
Nothing has been released to master as develop is our main branch and that we do not have a live environment.

Here is a quick help to use git flow:
```
git flow feature start FEATURE_NAME # This create a new feature branch
git flow feature finish             # This release the feature branch to develop
```

Links
-----

[Website][1] |
[Vagrant Project][2] | 
[Service Project][3] | 
[Integration Project][4] | 
[Jira][5] | 
[Jenkins][6]

[1]: https://een.int.aerian.com
[2]: https://devops.innovateuk.org/code-repository/projects/EEN/repos/een-vagrant/browse?at=refs%2Fheads%2Fdevelop
[3]: https://devops.innovateuk.org/code-repository/projects/EEN/repos/een-service/browse?at=refs%2Fheads%2Fdevelop
[4]: https://devops.innovateuk.org/code-repository/projects/EEN/repos/een-integration-tests/browse?at=refs%2Fheads%2Fdevelop
[5]: https://devops.innovateuk.org/issue-tracking/secure/Dashboard.jspa
[6]: https://jenkins.aerian.com/view/een/
[7]: https://devops.innovateuk.org/code-repository/projects/EEN/repos/een-webapp/browse/Jenkinsfile?at=refs%2Fheads%2Fdevelop
[8]: https://devops.innovateuk.org/code-repository/projects/EEN/repos/een-webapp/browse/build?at=refs%2Fheads%2Fdevelop
[9]: https://www.drupal.org/
[10]: https://devops.innovateuk.org/code-repository/projects/EEN/repos/een-webapp/browse/drupal/themes/custom/een?at=refs%2Fheads%2Fdevelop
