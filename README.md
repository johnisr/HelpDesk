# HelpDesk

A customer support system that supports anonymous users and help desk users. Connects the an authenticated help desk user to an anonymous user to a chat session. Also supports messaging from a help desk user to another help desk user.

Client Side

anon.php - The view for anonymous users.
anon.css/anon.js are the resources for the anonymous view.

main.php - The view for help desk users.
main.css/main.js are the resources for the help desk user view.

login.php - The view for logging in

Server Side

HDcontroller.php - The server controller, passes commands and data to and from the views and HDmodel.

HDauth.php - Handles authentication requests

HDoauth.php - Handles open authentication requests using EECS server

HDupload.php - Handles upload requests

HDmodel.php - The server model, does input validation and applies business logic from passed in commands.

HDdao.php - HDmodel's database access object, encapsulates all SQL code used by the model to interact with the database.

How to use:

1) Download a web server stack such as XAMPP.
2) Create the "HelpDesk" folder in the htdocs folder (where the web server stores web files)
3) Paste the contents of all files and folders except "setup", "tests", and "attack"
4) In the setup folder, import the sql settings found in help_desk.sql

Changes:

Protection against SQL inference attacks by using try catch to catch all errors and log them (prevent users from seeing errors)
Input validation for all incoming inputs, done at HDmodel (isset used for every variable for every method, prevent inference attacks)
Output sanitization to prevent CSS done at HDcontroller, using htmlescapechars() on the 3 possible output types (string, array, associative array)
CSRF Guard implemented for the views (login, main, anon)
Implementation of a IP blacklist that prevents IPs from logging in after some failed login attempts (default 5 min timeout after 5 failed tries)
Improved HDoauth security on the EECS server, making the log file inaccessible to all as well as implementing an index.html to prevent others from seeing directory.
Implemented HDconstants.php, to be able to see and change server constants in one file.
Added a drop down list to input boxes where they request to talk to another HDuser, to leak info about who they should try to attack.
CSRF implementation broke FRRegressionTest.php
New help_desk.sql to accomodate the IP blacklist defence against brute force.
New passwords for .htpasswd and HDUsers, in .htplain (could be changed)