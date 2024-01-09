Upload the files to your server

Google ReCaptcha

We now use Recaptcha from Google so you will need a v2 checkbox setup - see the instructions here https://developers.google.com/recaptcha/intro and setup a project here https://www.google.com/recaptcha/admin/enterprise
Settign a v2 Checkbox will generate a Site and a Secret key

OSM Keys

YOu will also need to create an external application in OSM - goto Settings->My Account->Developer Tools and create an app - take note of the two keys generated (they are only shown on create)
You will also need to add the URL of the osmrelay_patrolbuilder.php into the redirect URL setting

Edit the files

osmrelay_patrolbuilder.php 
Add the reCaptcha secret at line 30 and both the OSM Keys (lines 68 and 69) into this file
Chnage the URL at line 8

patrolbuilder.html
Add the reCaptcha Site Key at line 730
Change the URL at line 148 to the location of osmrelayoa2.php
Change the URL at line 442 to the lcoation of osmrelay_patrolbuilder.php 
