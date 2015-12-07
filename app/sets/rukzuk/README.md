# rukzuk package set

The rukzuk package set. Contains all official rukzuk modules, snippets, pageTypes and websiteSettings.

## Install grunt (systemwide)

	npm install -g grunt-cli

## Install local dependencies

	npm install

## Grunt Tasks

	grunt # shows an overview of available tasks
	grunt test # test the code (mostly linters and hinters + some phpunit tests)
	grunt build # builds all modules

	Options (for build):
	    --channel=dev|beta|stable - filters the packages according to channelmap.json (otherwiese all rz_* are used)
