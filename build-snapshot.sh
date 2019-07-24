#!/usr/bin/env bash

# see: https://github.com/rukzuk/rukzuk/blob/master/.circleci/config.yml

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

VER=$(cd ${DIR} && git describe --tags --always --dirty)
BRANCH=$(cd ${DIR} && git rev-parse --abbrev-ref HEAD)
CHANNEL=${1:-dev}

# clean
echo "remove ${DIR}/build ${DIR}/packaging ${DIR}/artifacts"
rm -rf ${DIR}/build ${DIR}/packaging ${DIR}/artifacts

echo "build version ${VER} on branch ${BRANCH} for channel ${CHANNEL}"

# build sets (all modules) in subshell
echo $(cd ${DIR}/app/sets/rukzuk && grunt build --channel=${CHANNEL} --build="${VER}" --branch="${BRANCH}")

# build client
cd ${DIR}
grunt package --channel=${CHANNEL} --build="${VER}" --branch="${BRANCH}"

# show result
ls -lh ${DIR}/artifacts/*
