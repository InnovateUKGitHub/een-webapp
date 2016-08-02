#!/bin/bash
####################################
#
# Create base database if required and run any database deltas
#
####################################

cd $htdocs

deltadir=$htdocs/db/deltas/application

# run database deltas?
if [ -d $deltadir ]; then   
    for f in $deltadir/*; do  
        substr=$(echo $f | egrep -o '[0-9]{3}')
        if [ ! "$substr" = "" ]; then
            dupe=$(find $deltadir -name "$substr*" | grep "$f" -v)
            if [ ! "$dupe" = "" ]; then
                echo "Failing build due to duplicate delta: " $dupe
                exit 1;
            fi
        fi
    done

    # create db and run deltas
    cd $htdocs/db

    #create DB if not exists
    dbexists=$(mysqlshow -h$dbhost -u$dbuser -p$dbpass -P$dbport $dbname | grep -v Wildcard | grep -o $dbname)
    if [ ! "$dbexists" = $dbname ]; then
        echo "Creating database $dbname"
        mysql -h$dbhost -u$dbuser -p$dbpass -P$dbport < setup.sql
    fi

    # run dbdeploy deltas
    ant

    # check if ant succeeded
    antReturnCode=$?   
    if [ $antReturnCode -ne 0 ];then
        echo "ant build failed"
        exit 1;
    fi
fi