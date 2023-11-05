# apidesignpatern

# Step 1

Clone the project

```bash
git clone https://github.com/OlivierBldn/apidesignpatern.git
```

# Step 2

Create api_config.php file in config directory

Paste Following and replace with you own values

```php
const __WEBSITE_URL__ = 'your_website_url';
```


# Step 3

Create db_config.php file in config directory

Paste Following and replace with you own values

```php
const __DB_HOST__ = 'your_host';
const __DB_NAME__ = 'your_database_name';
const __DB_USER__ = 'your_database_user';
const __DB_PASS__ = 'your_database_password';
```

# Step 4

Test the requests

## GetAllusers

#### Method = GET

your_host/your_website_url/users

## GetUser

#### Method = GET

your_host/your_website_url/users/{id}

## UpdateUser

#### Method = PUT

your_host/your_website_url/users/{id}

###### Body = JSON raw -> You only need the raws you want to update

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