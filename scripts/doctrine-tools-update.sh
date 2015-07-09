#!/bin/bash
DIR="$( cd "$( dirname "$0" )" && pwd )"
VENDOR_BIN="$DIR/../vendor/bin"

php $VENDOR_BIN/doctrine orm:schema-tool:update --force
