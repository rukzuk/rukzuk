# rukzuk-docker
Docker image for rukzuk.

The default `Dockerimage` file contains mysql daemon (parts based on official `mysql` docker image) and config as well 
as a standalone version of the python/django based ftp/sftp publisher for exernal hosting.

The files `Dockerfile-DEV` and `Dockerfile-sqlite` do not include mysql and publisher.

## Requirements

You need docker to run this docker image. For more information see:

https://docs.docker.com/installation/


## Build
To build the rukzuk docker image execute:

```sh
docker build --tag="rukzuk_image" --force-rm=true --no-cache=false .
```

## Create Volumes

```sh
docker volume create rz-data 
docker volume create rz-db
```

## Run

To start the build docker image execute:

```sh
docker run -v rz-db:/var/lib/mysql -v rz-data:/srv/rukzuk/htdocs/cms -e "CMS_URL=http://$(hostname)" -e "SMTP_HOST=smtp.google.com" -e "SMTP_USER=you@gmail.com" -e "SMTP_PASSWORD=password" -d -p 80:80 rukzuk_image
```

Note: Replace the email configuration with your own settings.

## Import Data

To import data please put a file `import.tar` in the folder `/srv/rukzuk/htdocs/cms` which is the `rz-data` volume. You might use `sudo` as the volume paths are owned by root.

Example:

```
sudo cp import.tar $(docker volume inspect -f '{{ .Mountpoint }}' rz-data)
```

then create a new instance (via run). After the import the tar file will be deleted! 

NOTE: The import can take a long time. Look what happens via `docker logs -f <Container-ID>` (`docker ps` shows the id).


### Configuration environment variables

* **CMS_URL**
  * Defines the domain name used to access the rukzuk cms inside the container.
* **SMTP_HOST**
  * Hostname of the used mail server
* **SMTP_USER**
  * Username to access the mail server
* **SMTP_PASSWORD**
  * Password to access the mail server


### Login credentials

Open your browser and login using `rukzuk@example.com` and `admin123` as password. After choosing a website template adjust the credentials in the user management. You should change the password after the first login.

