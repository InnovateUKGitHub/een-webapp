#!/bin/bash
# Clone credentials and inject into build/properties/
# 

if [ -e "/home/web/een-config/" ]; then
    cp -r /home/web/een-config/een-webapp/build/properties build/
else
    git clone ssh://git@git.devops.innovateuk.org/een/een-config.git /tmp/een-config
    cp -r /tmp/een-config/een-webapp/build/properties build/
    rm -rf /tmp/een-config
fi