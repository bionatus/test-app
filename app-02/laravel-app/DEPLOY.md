# Deploy App

### Setup Ansible on your local machine

#### Install

Follow the official [documentation](https://docs.ansible.com/ansible/latest/installation_guide/intro_installation.html) instructions for installation for each OS

#### Setup

##### Hosts

Define the hosts ( remote servers ) which will be provisioned by either changing the `etc/ansible/hosts` or by creating custom hosts file and :
- exporting `ANSIBLE_INVENTORY` with colon separated list of paths - macOS `export ANSIBLE_INVENTORY=/root/ansible_hostsexport` (export `ANSIBLE_HOSTS` for older ansible versions)
- use the `-i` flag to provide custom inventory file without polluting the system environment variables

Example hosts file :

```
[servers]
bluon_production ansible_ssh_host=157.230.154.136
```

##### SSH

- Generate SSH key to use for connecting to the remote machine : `ssh-keygen -t rsa`
- Copy you new key via the `ssh-copy-id` command by `ssh-copy-id -i ~/.ssh/your_new_key remote_user@remote_host`
- Test your connection `ssh remote_user@remote_host` - if the connection fails manually add the the public key to the `authorized_keys` file on the remote host
- Add the remote server to your local `~/.ssh/config` :

```
Host REMOTE_HOST_DOMAIN/IP
    User root
    IdentityFile ~/.ssh/your_new_private_key
    HostName REMOTE_HOST_DOMAIN/IP
```

`User root` is used as the provisioning is best performed by the root user.

**NOTICE** Some linux servers have python3 installed so `python` command will not be available systemwide, in which case the ansible inventory file needs to be modified to include `ansible_python_interpreter={PATH TO PYTHON EXECUTABLE ON REMOTE}` in the same line as the host it needs to be applied to.

### Provision the Server

#### Preparation ( Custom deploy user )

Custom `deploy` user is being used to handle the project deployment ( All git operations should be handled using this user).

The user is automatically being created and configured with the ansible playbook. Fore the script to work a SSH pair needs to be prepared :

- Generate SSH key pair and name it `id_rsa/id_rsa.pub`
- Place the newly generated pair the `provision/ssh` directory inside the project
- Add the generated public key to your Git keychain

**Notice** At the time of writing gitlab is being used as remote repository, if that changes, update the `provision/ssh/config` file accordingly !

#### Provisioning

`cd` into the `provision folder` of the project and run:

```
ansible-playbook playbook.yml --extra-vars "target=TARGET_HOST"
```

Where `TARGET_HOST` is the assigned hostname - for the above example target would be `bluon_production`.

#### Caveats

**Important**
Depending on the remote server initial setup some changes to the `playbook.yml` might need ot be made.

- If Python2 has been installed and Python 3.x is not available - removing `python3-mysqldb` from the playbook task `Install MySQL` might be needed

----

### App Setup

#### SSH

- `ssh` into the remote server with the `root` user
- Run `su deploy` .

#### Clone

- `cd` into `/var/www/app`
- Run `git clone your-git-repo bluon-api` to checkout the project into the `bluon-api` directory - **On production use the `--depth 1` flag**

#### Project Setup

- `cd` into the project directory
- Update `storage` directory permission by using `chmod 0755 -R storage/`
- Run `cp .env.example .env` to create the local .env file for the project
- Edit the `.env` file via `nano` or `vim` and setup the project name, url and database credentials ( Database credentials can be found in the `provision.yml` file )
- Run `composer install` to install the project PHP dependencies
- Run `php artisan key:generate` to generate the unique APP_KEY ( automatically populated inside the `.env` file)
- Once configured run `php migrate --seed` to provision the database for usage
- Sync the project products and conversion jobs with the Airtable via :

```
php artisan sync:projects
```

and

```
php artisan sync:conversion-jobs
```

- Test that the project is working correctly - use `Authorize` header with `Bearer` token - found in the `config/app.php` file.

#### Cron Setup

- Run `which php` to determine the location of the php executable
- Run `crontab -e`
- Add both artisan sync commands with the PHP path scheduled for midnight

```

MAILTO=""

* * * * * cd /var/www/bluon-api/ && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

- Save and exit the cron edit tab
- Run `crontab -l` to confirm that the changes have been saved
