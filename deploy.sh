#!/bin/bash
killall php
git pull
rm -rf var/cache/*
bin/console oro:platform:update --force
rm -rf var/cache/*
bin/console cache:warmup --env=dev
bin/console cache:warmup --env=prod
