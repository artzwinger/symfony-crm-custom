#!/bin/bash

# shellcheck disable=SC2046
eval `ssh-agent -s`
chmod 0600 ~/shop/key
ssh-add ~/shop/key

composer "$@"
