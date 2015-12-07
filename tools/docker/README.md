# rukzuk Docker mini-how-to

## Requirements

https://docs.docker.com/installation/

NOTE: Remove Vagrant user stuff for a production image

## Build
(use next steps or build and download docker image from http://ci.rukzuk.net/view/cms/job/cms_build_docker_image/)

### Download latest version from ci

```sh
curl https://github.com/rukzuk/rukzuk/releases/download/0.20151207.10.stable/0.20151207.10.stable.tgz  > tools/docker/release/cmsrelease.tar.gz
docker build --tag="rukzuk_image" --force-rm=true --no-cache=false .
```

### Export Image

```sh
docker save -o rukzuk-image.tar.gz rukzuk_image
```

## Run

```sh
docker load -i rukzuk-image.tar.gz
docker run -e "CMS_URL=http://$(hostname)" -e "SMTP_HOST=smtp.google.com" -e "SMTP_USER=you@gmail.com" -e "SMTP_PASSWORD=password" -d -p 80:80 -v $HOME/rukzuk/:/srv/rukzuk/htdocs/cms:rw rukzuk_image
```

