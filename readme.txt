-----------------------------------------

BMIT2013 RSD2(S1)G4
Online Camping Equipment Store (finecamp)
Group 2:
1. Mong Kel Heng (Leader)
2. Lum Win Kong
3. Victor Pow Yie Yuaw
4. Kong Weng Hang

-----------------------------------------



INSTALLATION GUIDE
----------------------------------------------------
1. Follow "BMIT2013 Installation Guide (202405).pdf"
----------------------------------------------------



HOW TO RUN PHPMYADMIN
----------------------------------------------------------------------
1. Launch XAMPP Control Panel (Run as administrator)
2. Start Apache
3. Start MySQL

If you need image processing:
- For Apache, click [Config] > [PHP (php.ini)]
- Press [CTRL + F] and enter gd to find the text
- Remove the semicolon (;) in front of gd
- Press [Ctrl + S] to save changes, close the file

4. For MySQL, click [Admin] to open PhpMyAdmin

If you are unable to open PhpMyAdmin due to port 3306 has been used:
- Open Command Prompt
- Type in: netstat -aon | find ":3306" | find "LISTENING"
- Remember the PID shown
- Open Task Manager (Ctrl + Shift + Esc) > Details
- Search for the PID > Right click the record > End task > End process
- Repeat step 4
----------------------------------------------------------------------



HOW TO IMPORT DATABASE
--------------------------------------------------
1. Select [Import] tab
2. Click [Choose File] and select a .sql file
3. Click [Import] at the bottom to import database
--------------------------------------------------



HOW TO EXPORT DATABASE
----------------------------------------------------------------
1. Click Database at the breadcrumb to go back to Database level
2. Select [Export] tab
3. Select [Custom]
4. Scroll down to the "Object Creation Options" section
5. Enable [Add CREATE DATABASE / USE Statement]
6. Click [Export] at the bottom to export database
----------------------------------------------------------------



HOW TO DROP DATABASE
----------------------------------------------------------------
1. Click Database at the breadcrumb to go back to Database level
2. Select [Operations] tab
3. Scroll down to the "Remove database" section
4. Click [Drop the database (DROP)] > OK
----------------------------------------------------------------



HOW TO OPEN PROJECT
---------------------------------------------------------------------------------------------------------------------
1. Go to [project] folder, right click [app] folder > Show more options > Open with Code/Open with Visual Studio Code
2. In Visual Studio Code, select [View] > [Terminal], or press shortcut keys [Ctrl + `]
3. Enter: php -S localhost:8000
4. [Ctrl + Click] the URL to access it in web browser
---------------------------------------------------------------------------------------------------------------------



MEMBER LOGIN
----------------------------------------------
Credentials
-----------
Email: rsander1fine@gmail.com
Password: Rsander1fine!

URL
---
http://localhost:8000/authentication/login.php
----------------------------------------------



ADMIN LOGIN
---------------------------------------------------
Credentials
-----------
Email: achance0@gmail.com
Password: Achance0!

URL
---
http://localhost:8000/authentication/loginAdmin.php
---------------------------------------------------



ROOT LOGIN
---------------------------------------------------
Credentials
-----------
Email: gmerlin6@gmail.com
Password: Gmerlin6!

URL
---
http://localhost:8000/authentication/loginAdmin.php
---------------------------------------------------