           _     _                _           _     
          | |   (_)              | |         | |    
 _ __ ___ | |__  _ _ __ ______ __| | __ _ ___| |__  
| '__/ _ \| '_ \| | '_ \______/ _` |/ _` / __| '_ \ 
| | | (_) | |_) | | | | |    | (_| | (_| \__ \ | | |
|_|  \___/|_.__/|_|_| |_|     \__,_|\__,_|___/_| |_|

robin-dash: Centralized Controller for Robin-Mesh networking devices
Copyright (C) 2010-2011 Cody Cooper.

Version 0.7 BETA 7

robin-dash is an open-source alternative to the default closed-source
open-mesh dashboard. It is currently in the BETA phase and is being
actively developed by Cody Cooper. It focuses on being small, and
keeping up with the latest and greatest features of robin-mesh. 

Benefits of using robin-dash, compared with others:
*	Fast: The pages are clean, and easy to read.
*	Small: The configuration is stored in XML files so that you do not
	have to have an MySQL server
*	Reliable: Because you can host your own version, on your own server,
	internally or on the Internet, you know that your server will be up,
	and working when you want it to.
*	Up-to-date feature set: You will always have the latest features
	available because it is developed by a member of the robin-mesh
	development team.
*	Features unique to robin-dash include:
		* Integration with your own POMADE server.

robin-dash is almost feature complete compared with the open-mesh.com
dashboard.


How to begin using robin-dash
---------------------------------------------------------------------------
You can download robin-dash from:
http://code.google.com/p/robin-dash/downloads/list

1.	Unzip the file
2.	Ensure that the data folder is writable by the server
3.	Visit your install url in a web browser, and create an account
4.	Login to your account, and change the approiate settings.
	Remember to press the Save button on each page where changes are made.
5.	You will need to change the alternate server in the open-mesh dashboard
	to be:  www.yourserver.com/robin-dash-directory/

	Please note, the following url is for example purposes and is not exactly
	how it will look for your network. Make sure you press the update button
	on the open-mesh server, and that the url is absolutely correct. It should
	not have an http or https prefix at the start, but should end with a slash.
	You can check it is correct by visiting it in your browser. 

Voilà ! You're network should be working in about 30 minutes once all the nodes
checkin. You can also view the checkin details by logging into robin-dash and
clicking on the "Network Overview" button.

We also offer a hosted version of robin-dash. It is one of the first places
to get the latest updates, and is SSL enabled. It is available at:
https://www.robin-dash.net/

Compatibility
---------------------------------------------------------------------------
robin-dash should be compatible with all official Robin-Mesh firmware
releases from r2690, onwards. It has been tested up to r3713. We do not
support the Open-Mesh NG firmware at this point due to the closed nature
of the firmware and the encryption of dashboard replies.

Folder Structure
---------------------------------------------------------------------------

├───data					Contains all data related to users networks
│   ├───cid					Stores checkin hashes to determine if the settings have to be sent again
│       └───codyc1515.txt	Hashes of checkin data, per line, per node
│   └───stats				Stores checkin data for all nodes to use on the overview page
│       └───codyc1515		Individual network data folder, contains stats from nodes
└───resources				Resources needed to display the pages correctly
    └───ckeditor			CKEditor is a WYSIWYG editor for the web, its files are located here
	└───lightbox			Makes client graphs popup all fancy looking

License
---------------------------------------------------------------------------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.