<?php

# ***** BEGIN LICENSE BLOCK *****
# Version: MPL 1.1/GPL 2.0/LGPL 2.1
# 
# The contents of this file are subject to the Mozilla Public License Version 
# 1.1 (the "License"); you may not use this file except in compliance with 
# the License. You may obtain a copy of the License at 
# http://www.mozilla.org/MPL/
# 
# Software distributed under the License is distributed on an "AS IS" basis,
# WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
# for the specific language governing rights and limitations under the
# License.
# 
# The Original Code is Weave Minimal Server
# 
# The Initial Developer of the Original Code is
#   Stefan Fischer
# Portions created by the Initial Developer are Copyright (C) 2012
# the Initial Developer. All Rights Reserved.
# 
# Contributor(s):
#   Daniel Triendl <daniel@pew.cc>
#   balu
#   Christian Wittmer <chris@computersalat.de>
# 
# Alternatively, the contents of this file may be used under the terms of
# either the GNU General Public License Version 2 or later (the "GPL"), or
# the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
# in which case the provisions of the GPL or the LGPL are applicable instead
# of those above. If you wish to allow use of your version of this file only
# under the terms of either the GPL or the LGPL, and not to allow others to
# use your version of this file under the terms of the MPL, indicate your
# decision by deleting the provisions above and replace them with the notice
# and other provisions required by the GPL or the LGPL. If you do not delete
# the provisions above, a recipient may use your version of this file under
# the terms of any one of the MPL, the GPL or the LGPL.
# 
# ***** END LICENSE BLOCK *****

// --------------------------------------------
// variables start
// --------------------------------------------

$action = null;
$dbType = null;

$dbUser = null;
$dbName = null;
$dbPass = null;
$dbHost = null;

$sqlitefile = null;


// --------------------------------------------
// variables end
// --------------------------------------------


// --------------------------------------------
// post handling start
// --------------------------------------------
if ( isset( $_POST['action'] ) ) {
    $action = check_input($_POST['action']);
}

if ( isset( $_POST['dbType'] ) ) {
    $dbType = check_input($_POST['dbType']);
}

if ( isset( $_POST['dbhost'] ) ) {
    $dbHost = check_input($_POST['dbhost']);
}

if ( isset( $_POST['dbname'] ) ) {
    $dbName = check_input($_POST['dbname']);
}

if ( isset( $_POST['dbuser'] ) ) {
    $dbUser = check_input($_POST['dbuser']);
}

if ( isset( $_POST['dbpass'] ) ) {
    $dbPass = check_input($_POST['dbpass']);
}

if ( isset( $_POST['sqlite_file'] ) ) {
    $sqlitefile = check_input($_POST['sqlite_file']);

    if (strcmp(pathinfo($sqlitefile, PATHINFO_EXTENSION), "sqlite") !== 0) {
       echo "Please use extension \"sqlite\" for SQLite 3.x database <a href=\"#\" onclick=\"history.go(-1)\">Go Back</a>";
       exit;
    }
}

// --------------------------------------------
// post handling end
// --------------------------------------------


// --------------------------------------------
// functions start
// --------------------------------------------

/*
    ensure that the input is not total waste
*/
function check_input( $data ) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


/*
    create the config file with the database type
    and the given connection credentials
*/
function write_config_file($dbt, $dbh, $dbn, $dbu, $dbp, $fsRoot, $sqlitefile) {

    // construct the name of config file
    //
    $cfg_file_name = getcwd() . "/settings.php";
    echo "Creating cfg file: " . $cfg_file_name;

    // now build the content of the config file
    //
    $cfg_content  = "<?php\n\n";
    $cfg_content .= "    // you can disable registration to the firefox sync server here,\n";
    $cfg_content .= "    // by setting ENABLE_REGISTER to false\n";
    $cfg_content .= "    // \n";
    $cfg_content .= "    define(\"ENABLE_REGISTER\", true);\n\n";

    $cfg_content .= "    // firefox sync server url, this should end with a /\n";
    $cfg_content .= "    // e.g. https://YourDomain.de/Folder_und_ggf_/index.php/\n";
    $cfg_content .= "    // \n";
    $cfg_content .= "    define(\"FSYNCMS_ROOT\", \"$fsRoot\");\n\n";

    $cfg_content .= "    // Database connection credentials\n";
    $cfg_content .= "    // \n";
    $cfg_content .= "    define(\"SQLITE_FILE\", \"$sqlitefile\");\n";
    if ( $dbt != "mysql" ) {
        $cfg_content .= "    define(\"MYSQL_ENABLE\", false);\n";
        $cfg_content .= "    define(\"MYSQL_HOST\", \"localhost\");\n";
        $cfg_content .= "    define(\"MYSQL_DB\", \"fsync\");\n";
        $cfg_content .= "    define(\"MYSQL_USER\", \"fsyncUserName\");\n";
        $cfg_content .= "    define(\"MYSQL_PASSWORD\", \"fsyncUserPassword\");\n";
    } else {
        $cfg_content .= "    define(\"MYSQL_ENABLE\", true);\n";
        $cfg_content .= "    define(\"MYSQL_HOST\", \"$dbh\");\n";
        $cfg_content .= "    define(\"MYSQL_DB\", \"$dbn\");\n";
        $cfg_content .= "    define(\"MYSQL_USER\", \"$dbu\");\n";
        $cfg_content .= "    define(\"MYSQL_PASSWORD\", \"$dbp\");\n";
    }
    $cfg_content .= "\n";
    $cfg_content .= "    // Use bcrypt instead of MD5 for password hashing\n";
    $cfg_content .= "    define(\"BCRYPT\", true);\n";
    $cfg_content .= "    define(\"BCRYPT_ROUNDS\", 12);\n";

    $cfg_content .= "\n";
    $cfg_content .= "    // you can enable logging to syslog for MINQUOTA_ERROR_OVER_QUOTA\n";
    $cfg_content .= "    // if (quota_used > MINQUOTA && quota_used < MAXQUOTA)\n";
    $cfg_content .= "    define(\"MINQUOTA_LOG_ERROR_OVER_QUOTA_ENABLE\", false);\n";
    $cfg_content .= "\n";
    $cfg_content .= "    // set MinQuota and MaxQuota\n";
    $cfg_content .= "    define(\"MINQUOTA\", 30000);\n";
    $cfg_content .= "    define(\"MAXQUOTA\", 35000);\n";

    $cfg_content .= "\n?>\n";

    // now write everything
    //
    $cfg_file = fopen($cfg_file_name, "w");
    fputs($cfg_file, "$cfg_content");
    fclose($cfg_file);
}


/*
    print the html header for the form
*/
function print_header( $title ) {
    if ( ! isset( $title ) ) {
        $title = "";
    }
    print '<html><header><title>' . $title . '</title><body>
    <h1>Setup FSyncMS</h1>
    <form action="setup.php" method="post">';
}


/*
    print the html footer
*/
function print_footer() {
    print '</form></body></html>';
}


/*
    print the html for for the mysql connection credentials
*/
function print_mysql_connection_form() {
    print_header("MySQL database connection setup");
    print 'MySQL database connection setup
    <table>
        <tr>
            <td>Host</td>
            <td><input type="text" name="dbhost" /></td>
        </tr>
        <tr>
            <td>Instance name</td>
            <td><input type="text" name="dbname" /></td>
        </tr>
        <tr>
            <td>Username</td>
            <td><input type="text" name="dbuser" /></td>
        </tr>
        <tr>
            <td>Password</td>
            <td><input type="password" name="dbpass" /></td>
        </tr>
    </table>

    <input type="hidden" name="action" value="step2">
    <input type="hidden" name="dbType" value="mysql">
    <p><input type="submit" value="OK"></p>';
    print_footer();
}
// --------------------------------------------
// functions end
// --------------------------------------------

// check if we have no configuration at the moment
//
if ( file_exists("settings.php") && filesize( "settings.php" ) > 0 ) {
    echo "<hr><h2>The setup looks like it's completed, please delete settings.php if wont re-setup start.</h2><hr>";
    exit;
}


// inital page - select the database type
//
if ( ! $action ) {

    // first check if we have pdo installed (untested)
    //
    if ( ! extension_loaded('PDO') ) {
        print "ERROR - PDO is missing in the php installation!";
        exit();
    }

    $validPdoDriver = 0;

    print_header("Setup FSyncMS - DB Selection");

    print 'Which database type should be used?<br>';
    if ( extension_loaded('pdo_mysql') ) {
        print '<input type="radio" name="dbType" value="mysql" /> MySQL <br>';
        $validPdoDriver++;
    } else {
        print 'MySQL not possible (Driver missing) <br>';
    }

    if ( extension_loaded('pdo_sqlite') ) {
        if ( ! $sqlitefile ) $sqlitefile = getcwd() . "/weave_db.sqlite";
        print '<input type="radio" name="dbType" value="sqlite" checked="checked" /> SQLite ';
	print 'Sqlite database file: <input type="text" name="sqlite_file" size="40" autofocus value="' . $sqlitefile . '"><br><br>';
        $validPdoDriver++;
    } else {
        print 'SQLite not possible (Driver missing) <br>';
    }

    if ( $validPdoDriver < 1 ) {
        print '<hr> No valid pdo driver found! Please install a valid pdo driver first <hr>';
    } else {
        print '<input type="hidden" name="action" value="step1">
        <p><input type="submit" value="OK" /></p>';
    }

    // ensure we bail out at this point ;)
    exit();
};


// step 2 (connection data) below
//
if ( $action == "step1" ) {

    // now check if the database is in place
    //
    print_header("Setup FSyncMS - DB Setup: $dbType!");
    switch ( $dbType ) {
        case "sqlite":
            $action = "step2";
            break;

        case "mysql":
            print_mysql_connection_form();
            break;

        default:
            print "ERROR - This type of database ($dbType) is not valid at the moment!";
            exit();
            break;
    }

}

// now generate the database
//
if ( $action == "step2" ) {

    $dbInstalled = false;
    $dbHandle = null;
    try {

        if ( $dbType == "sqlite" ) {

            if ( file_exists($sqlitefile) && filesize( $sqlitefile ) > 0 ) {
                $dbInstalled = true;
            } else {
                echo("Creating sqlite weave storage: ". $sqlitefile ."<br>");
                $dbHandle = new PDO('sqlite:' . $sqlitefile);
                $dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

        } else if ( $dbType == "mysql" ) {

            $dbHandle = new PDO("mysql:host=". $dbHost .";dbname=". $dbName, $dbUser, $dbPass);
            $select_stmt = "show tables like 'wbo'";
            $sth = $dbHandle->prepare($select_stmt);
            $sth->execute();
            $count = $sth->rowCount();
            if ( $count > 0 ) {
                $dbInstalled = true;
            }

        };

    } catch ( PDOException $exception ) {
        echo("database unavailable " . $exception->getMessage());
        throw new Exception("Database unavailable " . $exception->getMessage() , 503);
    }

    if ( $dbInstalled ) {
        echo "DB is already installed!<br>";

    } else {
        echo "Now going to install the new database! Type is: $dbType<br>";

        try {
            $create_statement = " create table wbo ( username varchar(100), id varchar(65), collection varchar(100),
                 parentid  varchar(65), predecessorid int, modified real, sortindex int,
                 payload text, payload_size int, ttl int, primary key (username,collection,id))";
            $create_statement2 = " create table users ( username varchar(255), md5 varchar(124), login int, 
                 primary key (username)) ";
            $index1 = 'create index parentindex on wbo (username, parentid)';
            $index2 = 'create index predecessorindex on wbo (username, predecessorid)';
            $index3 = 'create index modifiedindex on wbo (username, collection, modified)';

            $sth = $dbHandle->prepare($create_statement);
            $sth->execute();
            $sth = $dbHandle->prepare($create_statement2);
            $sth->execute();
            $sth = $dbHandle->prepare($index1);
            $sth->execute();
            $sth = $dbHandle->prepare($index2);
            $sth->execute();
            $sth = $dbHandle->prepare($index3);
            $sth->execute();
            echo "Database created <br>";

        } catch( PDOException $exception ) { 
            throw new Exception("Database unavailable", 503);
        }  

    }

    //guessing fsroot
      // get the FSYNC_ROOT url
    //  
    $fsRoot ="https://";
    if ( ! isset($_SERVER['HTTPS']) ) { 
        $fsRoot = "http://";
    }   
    $fsRoot .= $_SERVER['SERVER_NAME'] . dirname($_SERVER['SCRIPT_NAME']) . "/";
    if( strpos( $_SERVER['REQUEST_URI'], 'index.php') !== 0 ) { 
        $fsRoot .= "index.php/";
    }   

    // write settings.php, if not possible, display the needed contant
    //
    write_config_file($dbType, $dbHost, $dbName, $dbUser, $dbPass, $fsRoot, $sqlitefile);

    echo "<hr><hr> Finished the setup, please delete setup.php and go on with the FFSync<hr><hr>";
    echo <<<EOT
        <hr><hr>                                                                                                            
         <h4>This script has guessed the Address of your installation, this might not be accurate,<br/>
         Please check if this script can be reached by <a href="$fsRoot">$fsRoot</a> .<br/>
         If thats not the case you have to ajust the settings.php<br />
         </h4>
EOT;
}

?>
