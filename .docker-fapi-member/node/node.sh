#!/bin/sh

yarn install --prefer-offline --frozen-lockfile

yarn webpack
