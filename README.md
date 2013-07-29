CrowdFunding for Joomla! 
==========================
( Version 1.2.1 )
- - -

CrowdFunding is a platform that provides functionality for creating collective funding websites, powered by Joomla!

Changelog
-----------

###v1.2.1

* Added to the package the plugins "Content - CrowdFunding - User Mail" and "Content - CrowdFunding - Admin Mail".
* Fixed some issues.

###v1.2

* Added some new options. Now you are able to manage those features better.
    * maximum amount
    * maximum days
    * duration type
    * funding type
* The box with project state information moved to module "CrowdFunding Info"
* The box with rewards moved to module "CrowdFunding Rewards"
* The box with project details moved to module "CrowdFunding Details"
* Added option to set number of project in row.
* Improved responsive design
* [[#12]](https://github.com/ITPrism/CrowdFunding/issues/12 "Date display issue") Fixed the date issue
* Added plugins used for sending mails to administrator and user
    * Content - CrowdFunding - User Mail ( It sends notification mail to the administrator when a user creates or publishes a project. )
    * Content - CrowdFunding - Admin Mail ( It sends notification mail to a user when the administrator approves a project. )
    * Content - CrowdFunding - Manager ( It adds functionality for managing project on details page. It also display statistical information about it. )
* It was added some plugin events
    * onContentAfterSave
    * onContentChangeState
* Added option for Terms Of Use on the page, where user create a project.
* Added rewards manager
* Added filters on backend
* Added filters on discover page
* Now, the owner of the projects can review them even they are not approved.
* Improved the plugin "Content - CrowdFunding - Share"
* Added view "Featured"
* Moved "Discover" options from component config to menu item options.
* Fixed some issues

###v1.1.3

* Fixed date
* Fixed issue with white spaces in payment plugins.
* Added default picture, which will be displayed if missing one.

###v1.1.2

* Added a SEO option for project title.
* Fixed an issue with routing inner categories.
* Fixed some issues

###v1.1.1

* Fixed an issue [[#11]](https://github.com/ITPrism/CrowdFunding/pull/11 "small change to use title for project alias")
* Added option to search plugin that enables or disables searching in area.

###v1.1

* Fixed issue with routing of multilevel categories
* Added option for selection category when adding a menu item.
* Now it works with ITPrism Library 1.2.
* Improved integration. Now it works with Gravatar too.
* Improved routers.
* Improved plugins.
* Improved backend - transactions, projects,...
* Included search plugin ( plg_search_crowdfunding ).
* Included plugin that gives additiona information ( plg_content_crowdfundinginfo ).
* Added avatars to comments, updates and funders list.
* Now, you are able to use Vimeo video.
* Added "Send to Friend" form.
* Fixed some issues.
