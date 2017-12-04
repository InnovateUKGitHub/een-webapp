# Enterprise Europe Network (Alpha Version)

What is EEN?
------------

EEN is a website available [here][1] and this is the source code of the projects

Installation
------------

In order to install the project locally and some awesome development you will need to clone all the necessary projects onto your machine. 

The recommended method is to create a project directory 'een' and then clone the five repositories listed into directories within that 'een' directory.

- Vagrant Box: ``git clone https://devops.innovateuk.org/code-repository/scm/een/een-vagrant.git``
- Service Layer: ``git clone https://devops.innovateuk.org/code-repository/scm/een/een-service.git``
    (checkout the 'develop' branch for this repo)
- Drupal: ``git clone https://devops.innovateuk.org/code-repository/scm/een/een-webapp.git een``
    (checkout the 'develop' branch for this repo)
- Integration: ``git clone https://devops.innovateuk.org/code-repository/scm/een/een-integration-tests.git``
- Config: ``git clone https://DuttonMa@devops.innovateuk.org/code-repository/scm/een/een-config.git``

N.B. The Drupal repository is cloned into directory 'een' within the 'een' project directory, so you will have an /een/een directory.

You also need to set the environment variable EEN_SHARED_FOLDER_HOST to the project directory as follows:
(add this to your .bash_profile)
export EEN_SHARED_FOLDER_HOST="/<your een project directory>"

The next step is to start the vagrant VM:
 
    cd /een/een-vagrant

    vagrant up

Follow the isntructions displayed to make changes to your /etc/hosts file

Now ssh to the vagrant box and start apache:

    cd /een/een-vagrant
    vagrant ssh
    sudo service apache2 start

Back on your Mac, copy all items from /een/een-service/build/templates/zend to /een/een-service/config/autoload

On your Mac, go to /een/een-config/een-service/build/properties/base.properties and copy relevant values into the following files (replace all values surrounded by '%%'):

    /een/een-service/config/autoload/merlin.global.php
    /een/een-service/config/autoload/salesforce.global.php
    /een/een-service/config/autoload/gov-delivery.global.php
    /een/een-service/config/autoload/event-brite.global.php

N.B. event-brite.global.php needs 'path-event' => '/organizers/7829726093/events' (no trailing slash)


SSH to the Vagrant VM (as 'vagrant' user) and run the following commands:

    cd /home/web/een/build
    ln -s steps local
    cd /home/web/een
    make install
    cd /home/web/een-service
    make install

You should now have a working site when you open a browser on your Mac and go to http://vagrant.een.co.uk/

@TODO Need to update vagrant config to have an https version of the site as the HTML5 geolocation API does not work over http

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
    - een_custom_fields: This module contains custom fields and formatters
    - een_event_import: This module contains migration routines for importing events into Drupal
    - een_notifications: Contains glue code to make the Message Stack work
    - een_opportunity_import: This module contains migration routines for importing opportunities into Drupal

* Modules
This is the list of modules installed on the drupal project:
    - admin_toolbar
    - admin_toolbar_tools
    - ctools
    - eu_cookie_compliance
    - field_permissions
    - field_formatter_class
    - video
    - field_group
    - geolocation
    - shortcode
    - shortcode_example
    - shortcode_social
    - smtp
    - colorbox
    - crop
    - image_widget_crop
    - media_entity
    - crop_media_entity
    - message
    - message_notify
    - migrate_plus
    - migrate_tools
    - entity
    - pathauto
    - s3fs
    - simple_block
    - token
    - workbench_moderation
    - metatag
    - metatag_app_links
    - metatag_dc
    - metatag_dc_advanced
    - metatag_facebook
    - metatag_favicons
    - metatag_google_cse
    - metatag_google_plus
    - metatag_hreflang
    - metatag_mobile
    - metatag_open_graph
    - metatag_open_graph_products
    - metatag_twitter_cards
    - metatag_verification
    - yoast_seo
    - sharethis
    - twig_extensions
    
* Changes to contrib modules
    - ElasticsearchConnector 8.x-5.x-dev - fix applied as detailed at https://www.drupal.org/node/2858873
    - ElasticsearchConnector 8.x-5.x-dev - custom fix applied to avoid abort by using correct class from search_api
    
    - video_embed_field
    - better_exposed_filters
    - views_slideshow
    - views_slideshow_cycle
    - workbench

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

CRON Jobs and Imports
---------------------

The following drush commands need to be set up as cron jobs to run once a day:
drush mi --group=een_events
drush mi --group=een_opportunities

Salesforce integration
----------------------

The salesforce module has been added to the project and requires configuration at /admin/config/salesforce/authorize
You will need credentials for salesforce to do this authorisation and a connected app set up.

The connected app must have the following OAuth scopes:
Full Access(full)
Perform requests on your behalf at any time(refresh token, offline access)

You will need to input the Consumer key and secret from the connected app page on Salesforce.

All stage, dev and production instances should be connecting to test.salesforce.com whereas production should be pointing to login.salesforce.com


Google analytics integration
----------------------------

#### Overview
The basic Google Tag Manager (GTM) Javascript snippet is installed into every page of the EEN site. This snippet works in a similar way to the Google Analytics tracking code in that one of its functions is to send data to Google Analytics when a user starts a session on the EEN website in order to track things like what pages have been viewed, how long someone stayed on the site for etc. Where GTM + Google Analytics implementation differs from Google Analytics implementation alone however is that GTM is a container where other tags aside from the page view can be implemented without uploading anything to the site itself. For example, creating a virtual page view or installing 3rd party tags can now be done through the Google Tag Manager interface rather than hardcoding into the site itself and those changes are uploaded outside of the development cycle. Google Tag Manager has been set up to remove Personally Identifiable Information (PII) by updating the document location of every tag. It’s important that any new tags do this too or the analytics could be wiped by Google if found to contain any PII. All of the Tags, Triggers and Variables have been clearly marked with their functions and then sorted into folders which further describe the functions of that group of Tags, Triggers and Variables. In order to function correctly, the Google Tag Manager snippet must be installed onto every page that needs to be tracked. The correct snippet can be found within the Google Tag Manager console itself.

#### Logging in
The EEN service manager has the main login details for both Google Tag Manager and Google Analytics and can provide access to other users.

#### Google Analytics
Google Analytics receives data from Google Tag Manager. The main account is then split into four separate views which are all configured slightly differently. The Raw data view collects all data and applies no filters to it (aside from a filter that looks for any personally identifiable information and excludes it - every view needs this filter) the other views are Beta which captures just traffic to the Beta site, All Web Site data which is the main view and Test which is the test view for trying things out on.

#### Collecting internal search terms
A custom HTML tag (not a ready made one already in Google Tag Manager) has been created in order to track internal searches. This was necessary due to the autoloading of search results which would have meant that a regular tag would send every individual keystroke to Google Analytics i.e. A user searching for ‘medical’ would have sent page views and individual search queries for m, me, med, medi, medic, medica and medical. In order to get around this the custom search tag doesn’t send a page view unless more than three characters have been entered and it waits for two seconds before sending the page view. This will still result in slower typists creating multiple results however there will be far less bad data than with the first option.

#### Sending a (non-article/blog post)
Pageview This is like the standard Google Analytics tag in that it fires a page view over to Google Analytics every time the page loads. It anonymises the users IP and also makes sure that any personally identifiable information in a url string such as email or password isn’t captured through the updating path location. A different tag is used to send page views for blog and article posts.

#### Sending an article/blog post Pageview
This is like the standard page view tag but has had to be updated as we wanted to be able to send data about the author to Google Analytics. The tag looks for the author in the blog post using the CSS hierarchy (so if it breaks, this is a good first place to look) and creates a variable which stores the user number it has found. This variable is then inserted into the URL before it’s sent to Google analytics i.e. /blog/blog-about-een becomes /user/8/blog/blog-about-een

#### Creating virtual Pageviews
Virtual page views are created when: 1. A user clicks on a link that doesn’t go to EEN i.e. an external link 2. A user clicks on or in certain elements within the Partner application process i.e. when they verify their email or enter details into a form. The purpose of the virtual pageviews is to allow us to track progress where we wouldn’t usually be able to i.e. we can see how far down a form a user got rather than just that they didn’t get to the next page. The reason for using page views over events is that page views can be tracked using a funnel whereas events can’t i.e. a funnel needs to be made up of a sequence of pages visited one after the other.

#### Creating virtual Events
Virtual events are created when certain actions happen during the partner sign-up process. Events are chosen so as to reduce the number of pageViews where possible and so are implemented on actions taken by a user outside of the funnel process i.e. when clicking on a button that doesn’t necessarily have to be used to sign-up ‘register interest’ for example.

#### Implementing Hotjar (tracking individual sessions and creating heatmaps)
A tag with the Hotjar code is fired on every page of the website. Hotjar creates heat maps and records users sessions on the site.



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
