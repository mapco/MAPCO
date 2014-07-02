MAPCO SYSTEM
===================
This is the MAPCO SYSTEM Version for any shops
-----------------------------------------------------------------

* author: Mapco Developer <webmaster@mapco.de>
* version: 0.1

### Installation and Setup

Get the current ssh key from you system and add in your profile

```php
create a ssh key for you maschine
ssh-keygen -t rsa
```

```php
sudo apt-get install xclip
xclip -sel clip < ~/.ssh/id_rsa.pub

//copy your key in the github account ssh key
```

Get the current repository.

```php
git clone

```

```php
create or update the gitignor file

// remove a old version an create a new index file
git rm -r --cached .
git add .
git commit -m ".gitignore is now working"
```
