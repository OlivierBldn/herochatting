# HeroChatting

# Requirements :

  - Apache 2.4.56
  - MariaDB 10.4.28
  - PHP 8.2.4
  - Composer 2.6.5

# Step 1

Clone the project:

```bash
git clone https://github.com/OlivierBldn/herochatting.git
```

If you are using xampp, clone it into the htdocs directory.

# Optionnel

Make a copy of the project on your GitHub to be able to modify it as you wish :

## 1 - Create a new Guthub Repository

## 2 – Open a terminal in the project folder you just cloned (named herochatting unless you changed it).

## 3 – Change the repository URL to that of the new repository you created:

```bash
git remote set-url origin https://github.com/username/example.git
```

## 4 – Push the project files to your Git repo:

```bash
git push -u origin main
```

# Step 2

Uncomment all the commented paths in the .gitignore file and save the changes (this will prevent you from accidentally pushing your API keys).

# Step 3

Install the dependencies:

Open a teminal in the project's folder and run the command :

```bash
composer install
```

# Step 4

Create your database using mysql, postgresql or sqlite.

## If you just want to make a quick try, you can directly process requests using the sqlite3 database which comes with the project. Otherwise:

## Option 1 :

Create a new database, then create the tables by copying and pasting the queries written in the corresponding file of your Database type from the TablesCreationRequests directory.
If you do so, please paste all at once or proceed in order from the top of the file to the bottom.
And if you choose a slite3 database, the file has to be in the /database folder.

## Option 2 :

Create a new database, then import the file corresponding to your
Database type from the EmptyDatabases directory.

## If you choose to create a sqlite database, put the file in the database folder.

To create dummy data, you can use the api queries via POST method (suggested) or do it manually (not recommended) By inserting data directly into the database.

Example - Inserting a new user in the database manually using sql:

```sql
INSERT INTO user (id, firstName, lastName, username, password, email) VALUES (1, 'John', 'Doe', 'JonyDoe', '123456', 'john@example.com');
```

# Step 5

Get yourself an OpenAi API Key , you can find more information at https://platform.openai.com/docs/tutorials

Once you registered, log in.
Once you are logged in, on the left of your screen there will be a menu.
Click on "API Key", and then create a new key.
Then go to Settings->Billing, and add credits to your account.


You will also need a Stable Diffusion API Key, you can find more informations at https://stablediffusionapi.com/docs/
Once you registered, log in, click on your avatar.
On the left of your screen there will be a menu.
Click on "API Settings", and then create a new key.
You will have free credits for about 10 requests.
Then you will have to subscribe.

# Step 6

Go to the /config directory.

In the cfg_apiConfig.php file, replace Your_Secret_Key by a random chain of characters.

In the cfg_dbConfig.php file, replace the dummy values by those of your database.

In the cfg_globalConfig.php file, replace Your_Website_URL by the url of your project. If you are using xampp it is the name of your folder, which means herochatting if you did not change it.

In the cfg_openAIConfig.php file, replace Your_OPEN_AI_API_Key by your actual OpenAi key.

In the cfg_stableDiffusionConfig.php file, replace Your_STABLE_DIFFUSION_API_Key by your actual Stable Diffusion key.

# Step 7

You are now all setted up to try the requests !

Start by creating a user :

## CreateUser

#### Method = POST

your_host/your_website_url/register

###### Auth = No auth

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

Then generate a Token for your User :

## CreateToken

#### Method = POST

your_host/your_website_url/auth/login

###### Auth = No auth

###### Body = JSON raw -> All data are required

```json
{
    "email" : "your_user_email",
    "password" : "your_user_password",
}
```

Then copy your token and create a Universe !

## CreateUniverse

#### Method = POST

your_host/your_website_url/users/{your_user_id}/universes

###### Auth = Bearer Token -> paste your token if you did not change the variable !

###### Body = JSON raw -> The name is required

```json
{
    "name" : "Harry Potter"
}
```

You can now create a Character !

## CreateCharacter

#### Method = POST

your_host/your_website_url/universes/{your_universe_id}/characters

###### Auth = Bearer Token -> paste your token if you did not change the variable !

###### Body = JSON raw -> The name is required

```json
{
    "name" : "Harry"
}
```
Your are ready to create a Chat !

# CreateChat

#### Method = POST

your_host/your_website_url/chats

###### Auth = Bearer Token -> paste your token if you did not change the variable !

###### Body = JSON raw -> The fields are all required

```json
{
    "userId" : 28,
    "characterId" : 50
}
```

And now have fun chatting with you Character !

# CreateMessage

#### Method = POST

your_host/your_website_url/users/{your_user_id}/chats/{your_user_chat_id}/messages

###### Auth = Bearer Token -> paste your token if you did not change the variable !

###### Body = JSON raw -> The fields are all required

```json
{
    "content" : "Do you know the secret of my scar ?",
    "isHuman" : 1
}
```

You can upload my Postman collection if you want.

If you do so, feel free to update the project's variables with your own values !


## Other routes examples

## GetAllusers

#### Method = GET

your_host/your_website_url/users

## GetUserById

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

## DeleteUser

#### Method = DELETE

your_host/your_website_url/users/{id}


## If you have any problem with the api, feel free to have a look at the logs files or contact me !