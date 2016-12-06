# Config

1. Intall XAMP it should be onfigurated to load Apache and MySQL, once is installed please make sure Apache and MySQL are started

2. Install MySQL Workbench

3. Create Database schema

3.1 Create a new Localhost conection on MySQL Connections:
    hostName: 127.0.0.1
    User: root
    Port: 3306
3.3. Import DB
     From Github Project please Download Dump20161206.sql
    3.3.1 Navigator->Data Import/Restore
    3.3.2 Select Import from Self-Contained File and select the file path where you download Dump20161206.sql
    Start Import
3.4 Download GIT Project .zip and extract Files
3.5 Locate the Git Project into Xampp-htdocs, your project should be look something similar to this path C:\xampp\htdocs\challenge-master

4. Unzipped library extract Files and should be in same Root as Application, Cronjob and public

5. Load Historical Data
    Go to: http://localhost/challenge-master/Cronjob/Manager.php
  5.1 On Manager please Run "Team Job"
  5.2 To Load Historical Data please Run "Schedule Job"
    5.2.1 A new Tab will be open, please add to the URL Query: &historicalMode=true
    Should looks like this: http://localhost/challenge-master/Cronjob/Manager.php?cronjob=Schedule&historicalMode=true
    
Now you are all set to visualize the Data:
http://localhost/challenge-master/public/index.html
      
    
    
