#!/usr/bin/env bash

BUILD_VERSION=$(node -pe "require('./composer.json').version")
export BUILD_VERSION
BUILD_NAME="devly-themekit"
export BUILD_NAME

if [ ! -d "dist" ]; then
  mkdir "dist"
fi

rsync -rc --exclude-from ".distignore" "./" "dist/$BUILD_NAME"

cd "dist/$BUILD_NAME"

composer install --no-dev

rm composer.json
rm composer.lock

cd -

zip -r "dist/$BUILD_NAME" "dist/$BUILD_NAME/"

mv "dist/$BUILD_NAME.zip" "dist/$BUILD_NAME-v$BUILD_VERSION.zip"

clear

echo "Build process completed."

