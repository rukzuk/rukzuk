#!/bin/bash
$ curl -H "Content-Type: application/json" --data '{"source_type": "Tag", "source_name": "$CIRCLE_TAG"}' -X POST https://registry.hub.docker.com/u/rukzuk/rukzuk/trigger/$DOCKER_HUB_TOKEN/
