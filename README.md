# UNCCD Engagement Drupal Module
The UNCCD Engagement Drupal Module provides constest and campaign functionality.

## User Guide

### Installing the module
The module requires Drupal 8.x, and has no other module dependencies.
To install the module:
1. Create a new folder "unccd_engagement" inside "docroot/modules/unccd"
2. Place all of the files from this repository inside the above folder.
3. Go to "Extend" in the Drupal admin panel.
4. Find UNCCD Engagement in the module list, and check the box next to it
5. Click "Install" at the bottom of the page
6. Clear the cache by running "drush cr" or go to "Configuration -> Performance" and click "Clear all caches"

### Updating the module
To update the module:
1. Update the contents of "docroot/modules/unccd/unccd_engagement" with the contents of this repository
2. Clear the cache by running "drush cr" or go to "Configuration -> Performance" and click "Clear all caches"

### Usage

The module adds two new sections under "Structure" in the admin panel:
- UNCCD Campaigns: Create and manage campaigns
- UNCCD Contests: Create and manage contests

The permissions "Manage UNCCD Campaigns" and "Manage UNCCD Contests" can be given to users for them to be able to view the aforementioned sections.

## Development

### Folder structure

TODO

### Database Schema

TODO

### External libraries
This module does not use any external libraries.