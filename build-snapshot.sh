#!/usr/bin/env bash

# see: https://github.com/rukzuk/rukzuk/blob/master/.circleci/config.yml

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

VER=$(cd ${DIR} && git describe --tags --always --dirty)
BRANCH=$(cd ${DIR} && git rev-parse --abbrev-ref HEAD)

# clean
echo "remove ${DIR}/build ${DIR}/packaging ${DIR}/artifacts"
rm -rf ${DIR}/build ${DIR}/packaging ${DIR}/artifacts

echo "build ${VER} on ${BRANCH}"

# build sets (all modules) in subshell
echo $(cd ${DIR}/app/sets/rukzuk && grunt build --channel=dev --build="${VER}" --branch="${BRANCH}")

# build client
cd ${DIR}
grunt package --channel=dev --build="${VER}" --branch="${BRANCH}"

# show result
ls -lh ${DIR}/artifacts/*
