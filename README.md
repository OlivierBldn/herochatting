# apidesignpatern

# Step 1

Clone the project.

```bash
git clone https://github.com/OlivierBldn/apidesignpatern.git
```

If you are using xampp, clone it into the htdocs directory.

# Step 2

Create your database using mysql, postgresql or sqlite.

## For Mysql :

Create a new database, then import the apidesignpater.sql file available at the root of the project.

## For SQLite :

You can either use the existing database names apidesignpatern.db available in the database folder.

You can also create your own SQLite database into the database directory and create the tables using the following code.

```sql
CREATE TABLE 'user' (
  'id' INTEGER PRIMARY KEY,
  'firstName' TEXT NOT NULL,
  'lastName' TEXT NOT NULL,
  'username' TEXT NOT NULL,
  'password' TEXT NOT NULL,
  'email' TEXT NOT NULL
);

CREATE TABLE 'character' (
  'id' INTEGER PRIMARY KEY,
  'name' TEXT NOT NULL,
  'description' TEXT NOT NULL,
  'image' TEXT NOT NULL,
  'id_universe' INTEGER NOT NULL,
  FOREIGN KEY ('id_universe') REFERENCES 'universe' ('id') ON DELETE CASCADE
);

CREATE TABLE 'message' (
  'id' INTEGER PRIMARY KEY,
  'description' TEXT,
  'dateMessage' DATE,
  'is_human' INTEGER,
  'id_user' INTEGER,
  'id_character' INTEGER,
  'id_universe' INTEGER,
  FOREIGN KEY ('id_user') REFERENCES 'user' ('id') ON DELETE CASCADE,
  FOREIGN KEY ('id_character') REFERENCES 'character' ('id') ON DELETE CASCADE,
  FOREIGN KEY ('id_universe') REFERENCES 'universe' ('id') ON DELETE CASCADE
);

CREATE TABLE 'universe' (
  'id' INTEGER PRIMARY KEY,
  'name' TEXT NOT NULL,
  'description' TEXT NOT NULL,
  'image' TEXT NOT NULL,
  'id_user' INTEGER NOT NULL,
  FOREIGN KEY ('id_user') REFERENCES 'user' ('id') ON DELETE CASCADE
);
```

To create dummy data, you can use the api queries via POST method (suggested) or do it manually (not recommended).

Example - Inserting a new user in the database manually using sql:

```sql
INSERT INTO user (id, firstName, lastName, username, password, email) VALUES (1, 'John', 'Doe', 'JonyDoe', '123456', 'john@example.com');
```

# Step 3

In the /config directory, create api_config.php file.

Paste the following code snippet and replace with you own value.

```php
const __WEBSITE_URL__ = 'your_website_url';
```

If you are using xampp, the value is the name of the directory in htdocs.

For example, if you did not rename the project after cloning, the value will be :

```php
const __WEBSITE_URL__ = 'apidesignpatern';
```

# Step 3

Create db_config.php file in config directory.

Paste the following code snippet and replace the examples values with you own values.

database_type takes values 'mysql', 'sqlite' or 'postgresql'.

Insert the value of the type of database you want to use.

```php
$GLOBALS['dbinfos'] = [
    'database_type' => 'mysql',
    'mysql' => [
        'host' => 'localhost',
        'dbname' => 'your_mysql_db_name',
        'username' => 'your_mysql_username',
        'password' => 'your_mysql_password',
    ],
    'sqlite' => [
        'database_file' => 'database_file_name',
    ],
    'postgresql' => [
        'host' => 'localhost',
        'dbname' => 'your_postgresql_db_name',
        'username' => 'your_postgresql_username',
        'password' => 'your_postgresql_password',
    ],
];

return $GLOBALS['dbinfos'];
```

# Step 4

Test the requests (Don't Forget to create dummy data).

## GetAllusers

#### Method = GET

your_host/your_website_url/users

## GetUser

#### Method = GET

your_host/your_website_url/users/{id}

## UpdateUser

#### Method = PUT

your_host/your_website_url/users/{id}

###### Body = JSON raw -> You only need the rows you want to update

```json
{
    "firstName" : "your_user_firstname",
    "lastName" : "your_user_lastname",
    "username" : "your_user_username",
    "password" : "your_user_password",
    "email" : "your_user_email"
}
```

## CreateUser

#### Method = POST

your_host/your_website_url/users

###### Body = JSON raw -> All data are required

```json
{
    "firstName" : "your_user_firstname",
    "lastName" : "your_user_lastname",
    "username" : "your_user_username",
    "password" : "your_user_password",
    "email" : "your_user_email"
}
```

## DeleteUser

#### Method = DELETE

your_host/your_website_url/users/{id}