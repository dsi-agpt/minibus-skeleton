#!/bin/bash
DIR="$( cd "$( dirname "$0" )" && pwd )"
VENDOR_BIN="$DIR/../vendor/bin"
#MINIBUS_ENTITIES_PATH="$DIR/../module/Minibus/src"
JOBS_ENTITIES_PATH="$DIR/../module/Jobs/src"

#php $VENDOR_BIN/doctrine orm:generate-entities --update-entities="true" --generate-methods="true" $MINIBUS_ENTITIES_PATH --filter Minibus
php $VENDOR_BIN/doctrine orm:generate-entities --update-entities="true" --generate-methods="true" $JOBS_ENTITIES_PATH --filter Jobs
