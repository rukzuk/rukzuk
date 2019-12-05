<?php
// add login data of the owner from meta.json to DB


// read json
$rawdata = file_get_contents($argv[1] ? : 'meta.json');

/* Example meta.json
{
"owner": {
    "firstname": "First",
    "language": "de",
    "lastname": "Last",
    "upgradeUrl": "https://ca.rukzuk.com/upgrade",
    "passwordResetUrl": "https://ca.rukzuk.com/accounts/password_reset",
    "email": "user@example.com",
    "dashboardUrl": "https://ca.rukzuk.com/dashboard",
    "trackingId": "uuid",
    "password": "pbkdf2_sha256$20000$HASH",
    "id": "uuid"
  }
}
*/

//convert json object to php associative array
$data = json_decode($rawdata, true);
$user = $data['owner'];

if((getenv('CMS_DB_TYPE') ? : 'mysql') == 'mysql') {
    $pdo = new PDO('mysql:host=localhost;dbname='.(getenv('CMS_MYSQL_DB') ? : 'rukzuk'), 
                   getenv('CMS_MYSQL_USER') ? : 'rukzuk', 
                   getenv('CMS_MYSQL_PASSWORD') ? : 'rukzuk');
} else {
    $pdo = new PDO('sqlite:'.getenv('CMS_SQLITE_DB'));
}

$db_user = array(
    'id'         => $user['id'],
    'firstname'  => $user['firstname'],
    'lastname'   => $user['lastname'],
    'email'      => $user['email'],
    'password'   => $user['password'],
    'language'   => $user['language'],
    'lastupdate' => time(),
);

echo "import-metajson.php: found admin user: ".$db_user['email'];

$statement = $pdo->prepare("INSERT INTO user (id, firstname, lastname, email, password, language, issuperuser, isdeletable, lastupdate) "
                          ."VALUES (:id, :firstname, :lastname, :email, :password, :language, 1, 0, :lastupdate)");
$statement->execute($db_user);
