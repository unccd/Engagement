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

The module adds five additional database tables.

unccd_campaigns:

Column name         | Type     | Description
------------------- | -------- | -------------------
id                  | int      | Primary Key: Unique campaign ID.
title               | varchar  | Title of the campaign.
status        		| tinyint  | Status of the campaign (draft, live...)
button_text		    | varchar  | Text of the support button
description         | text     | Description of the campaign.

unccd_campaign_signatures:

Column name         | Type     | Description
------------------- | -------- | -------------------
id                  | int      | Primary Key: Unique signature ID.
campaign_id         | int      | ID of the campaign the user signed.
ip        			| varchar  | IP of the user who signed
date		    	| datetime | Date the campaign was supported

unccd_contests:

Column name          | Type     | Description
-------------------- | -------- | -------------------
id                   | int      | Primary Key: Unique contest ID.
type                 | varchar  | Type of contest (photo, video, text, etc...)
title        		 | varchar  | Title of the contest
status		    	 | tinyint  | Status of the contest (draft, live...)
allow_online_entries | tinyint  | Should the users be allowed to submit their entries online.
show_number_of_votes | tinyint  | Should users be able to see the number of votes each entry has.
deadline_for_entries | datetime | Date/time after which new entries are no longer allowed.
voting_starts		 | datetime | Date/time at which voting starts.
deadline_for_voting  | datetime | Date on which voting ends.
description  		 | text 	| Description of the contest.

unccd_contest_entries:

Column name          | Type     | Description
-------------------- | -------- | -------------------
id                   | int      | Primary Key: Unique contest entry ID.
contest_id           | int      | ID of the contest the entry is for
title        		 | varchar  | Title of the entry
name		    	 | varchar  | Name of the author
email 				 | varchar  | Email of the author
description          | text  	| Description of the entry
attachment_id 	     | int 		| The drupal file id for the uploaded attachment.
attachment		 	 | varchar  | The contest entry attachment URL.

unccd_contest_votes:

Column name         | Type     | Description
------------------- | -------- | -------------------
id                  | int      | Primary Key: Unique vote ID.
contest_id          | int      | ID of the contest the user voted in.
entry_id		    | int      | ID of the entry the user voted for.
ip        			| varchar  | IP of the user who voted
date		    	| datetime | Date on which the vote was cast.

### External libraries
This module does not use any external libraries.