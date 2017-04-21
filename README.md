[![CircleCI](https://circleci.com/gh/rukzuk/rukzuk.svg?style=svg)](https://circleci.com/gh/rukzuk/rukzuk)

# What is rukzuk?

rukzuk combines a web design Software and a Content Management System. Until now the old web design process required a layout to be created by a designer using a graphic program like Adobe Photoshop or InDesign. The design was then translated into HTML/CSS before being integrated into a content management system — all of which required a developer. rukzuk sums up the entire process in one tool, which saves time, money, and avoids duplicate work.

- Coding knowledge and a web developer are no longer necessary to create an individual website.
- rukzuk has adopted the slogan "What You See Is What It Really Is", describing how users have the ability to design websites directly in their browser. Throughout the web design process the website is viewed in real time giving users constant control over the end result.
- Responsive layouts for smart phones, tablets and desktops are designed visually in rukzuk using the program's Visual Responsive Web Design.
- In contrast to code generators like Adobe Edge Reflow, rukzuk’s content management system (CMS) is already built-in.
- Multiple websites can be managed simultaneously in one installation.

## Who needs rukzuk?

- Everybody who wants to design websites independently and professionally, but without wasting time and money on coding the website.
- Designers who want to design websites freely and professionally, but without wasting time and money on coding. Freelancing designers who create websites that must strictly adhere to customer specification or corporate guidelines. Content editing can be done by the clients independently.
- Agencies who need to create last-minute websites for pitches or presentations. The big difference between rukzuk and mockup or prototyping tools is that you can publish the internet-ready websites instantly.
- Companies and organizations who manage a huge amount of websites simultaneously. rukzuk is the ideal solution to create microsites, landing pages, single product sites, event websites and all kinds of websites.
- Developers who code new modules to expand the functionality of rukzuk. rukzuk itself does not generate code, it's generated from within the modules, which can be modified and extended at any time.

## How does rukzuk work?

Users use rukzuk to build websites by combining a flexible element called a Module. Modules are combined to create the structure and design of a website. Each module has a specific function, for example: a gallery, a navigation element, a form or simply a box. To create a website: select an existing design — or start with a new one — and fill it with content using the modules. Styles and effects can be applied to each of the modules; from font formatting to backgrounds and CSS3. Once the site is complete, designers specify which modules can be edited by a client. The completed design then serves as the template for the pages of a website. The final result can consist of unlimited pages and never-ending creative possibilities.

## Who made rukzuk?

The idea was born at [SEITENBAU GmbH](https://seitenbau.github.io/), an IT-service company and web agency with over 100 employees, based in Constance, Germany. The development of rukzuk is the product of many years of experience working with various content management systems for private companies of all sizes and public administrations.

Further information about rukzuk under [https://rukzuk.com](https://rukzuk.com)

# Demo

For a quick demo simply use [`docker pull rukzuk/rukzuk`](https://hub.docker.com/r/rukzuk/rukzuk/), check out the free trial at [https://rukzuk.com](https://rukzuk.com) or watch a video: [Getting Started](https://www.youtube.com/watch?v=CeBHMoWo_TE&list=PLybfRIhLjxOn7jP2C8VxPN1cdcu_7Prck), [Timelapse of creating a website visually in rukzuk](https://www.youtube.com/watch?v=2i38NKPDsM0). 


# Components

* Client application (stored in [/app](app))
* Webservices (stored in [/app/server](app/server))
* Modules (stored in [/app/sets/rukzuk](app/sets/rukzuk)), read more in our [module development tutorials](http://developers.rukzuk.com/)


# Requirements

rukzuk is tested and developed under Ubuntu 14.04.1.

The following Ubuntu packages are required:

* apache2 - Version 2.4
  * mod-ssl
  * mod-rewrite

* php5 - Version 5.5
  * php5-mcrypt
  * php5-curl
  * php5-gd
  * php5-intl

You can use mod-php5, fcgi or php-fpm to connect Apache with PHP.

Needed third party packages:

* phantomjs - Version 1.9
* php5-v8js - Version 0.1.3 (deb file is in the repository)

## For SQLite

* sqlite3 - Version 3.8
* php5-sqlite - Version 5.5

## For MySQL

* mysql5 - Version 5.5
* php5-mysql - Version 5.5

# Installations notes

## Install grunt (systemwide)

	npm install -g grunt-cli

## Install local dependencies

	npm install

## Grunt Tasks

	grunt # shows an overview of available tasks
	grunt dev # install dev dependencies
	grunt test # test the client code (mostly linters and hinters + some phpunit tests)
	grunt phpunit:all # test the php code
	grunt build # builds all modules
	grunt package --channel=stable --build=`git describe --tags` # build a release package


